<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerComplaint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class CustomerComplaintController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $complaints = CustomerComplaint::with(['customer', 'salesEngineer'])
            ->when($user->isSalesEngineer(), fn ($query) => $query->where('sales_engineer_id', $user->id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('reported_date')
            ->paginate(15)
            ->withQueryString();

        return view('content.sales.complaints', [
            'complaints' => $complaints,
            'customers' => Customer::when($user->isSalesEngineer(), fn ($query) => $query->where('assigned_sales_engineer_id', $user->id)->orWhere('sales_engineer_id', $user->id))->orderBy('company_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'reported_date' => ['required', 'date'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);
        $customer = Customer::findOrFail($data['customer_id']);
        abort_if($request->user()->isSalesEngineer() && $customer->assigned_sales_engineer_id !== $request->user()->id && $customer->sales_engineer_id !== $request->user()->id, 403);
        $data['sales_engineer_id'] = $request->user()->isSalesEngineer() ? $request->user()->id : ($customer->sales_engineer_id ?: $customer->assigned_sales_engineer_id ?: $request->user()->id);
        $complaint = CustomerComplaint::create($data);
        $this->audit('raised_complaint', CustomerComplaint::class, $complaint->id, ['status' => 'open']);

        return back()->with('status', 'Complaint logged.');
    }

    public function show(CustomerComplaint $complaint): View
    {
        $this->authorizeComplaint($complaint);
        // GET/view logs removed per requirement
        return view('content.sales.complaint', ['complaint' => $complaint->load(['customer', 'salesEngineer']), 'activities' => \App\Models\ActivityRecord::with('user')->where('subject_type', CustomerComplaint::class)->where('subject_id', $complaint->id)->latest()->get()]);
    }

    public function update(Request $request, CustomerComplaint $complaint): RedirectResponse
    {
        $this->authorizeComplaint($complaint);
        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'resolved'])],
            'resolution' => ['nullable', 'string'],
        ]);
        $oldStatus = $complaint->status;
        $complaint->update($data);
        $this->audit('updated_complaint', CustomerComplaint::class, $complaint->id, ['from_status' => $oldStatus, 'to_status' => $complaint->status]);

        return back()->with('status', 'Complaint updated.');
    }

    public function destroy(CustomerComplaint $complaint): RedirectResponse
    {
        $this->authorizeComplaint($complaint);
        $this->audit('deleted_complaint', CustomerComplaint::class, $complaint->id);
        $complaint->delete();

        return back()->with('status', 'Complaint removed.');
    }

    private function authorizeComplaint(CustomerComplaint $complaint): void
    {
        $user = auth()->user();
        if ($user->isAdmin()) return;
        abort_if($user->isSalesEngineer() && $complaint->sales_engineer_id !== $user->id, 403);
    }
}
