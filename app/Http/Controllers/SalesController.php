<?php

namespace App\Http\Controllers;

use App\Models\ActivityRecord;
use App\Models\Customer;
use App\Models\DailyActivityLog;
use App\Models\Quotation;
use App\Models\Rfq;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SalesController extends Controller
{
    public function storeCustomer(Request $request)
    {
        $data = $request->validate([
            'customer_code' => ['required', 'string', 'max:50', 'unique:customers,customer_code'],
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'customer_type' => ['required', Rule::in(['new', 'existing', 'qualified'])],
            'department' => ['nullable', 'string', 'max:120'],
        ]);
        unset($data['department']);
        $data['assigned_sales_engineer_id'] = auth()->id();
        $data['sales_engineer_id'] = auth()->id();
        $customer = Customer::create($data);
        $this->audit('created_customer', Customer::class, $customer->id);
        ActivityRecord::create(['user_id' => auth()->id(), 'action' => 'created_customer', 'subject_type' => Customer::class, 'subject_id' => $customer->id]);
        return back()->with('status', 'Customer saved.');
    }

    public function rfqs()
    {
        $user = auth()->user();
        $query = Rfq::with(['customer', 'salesEngineer'])->latest('rfq_received_date');
        if (! $user->isAdmin() && ! $user->isOwner()) {
            $query->where('sales_engineer_id', $user->id);
        }
        $customers = Customer::when($user->isSalesEngineer(), fn ($query) => $query->where('assigned_sales_engineer_id', $user->id)->orWhere('sales_engineer_id', $user->id))->orderBy('company_name')->get();
        return view('content.sales.rfqs', ['rfqs' => $query->paginate(15)->withQueryString(), 'customers' => $customers]);
    }

    public function storeRfq(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'rfq_received_date' => ['required', 'date'],
            'rfq_number' => ['required', 'string', 'max:100', 'unique:rfqs,rfq_number'],
            'rfq_description' => ['required', 'string'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'quotation_submission_target_date' => ['nullable', 'date', 'after_or_equal:rfq_received_date'],
            'current_status' => ['required', Rule::in(['follow_up', 'follow_through', 'won', 'lost', 'cancelled'])],
            'total_quoted_price' => ['nullable', 'numeric', 'min:0'],
            'total_awarded_price' => ['nullable', 'numeric', 'min:0'],
            'total_invoiced_price' => ['nullable', 'numeric', 'min:0'],
            'advance_received' => ['nullable', 'numeric', 'min:0'],
            'pending_amount_due_date' => ['nullable', 'date'],
            'payment_pending_reason' => ['nullable', 'string'],
            'order_cancelled' => ['boolean'],
            'order_cancelled_amount' => ['nullable', 'numeric', 'min:0'],
            'order_cancel_reason' => ['nullable', 'string'],
        ]);
        $data['sales_engineer_id'] = $request->user()->isSalesEngineer() ? $request->user()->id : $request->integer('sales_engineer_id', $request->user()->id);
        if ($request->user()->isSalesEngineer() && !$request->user()->isAdmin()) {
            abort_unless(Customer::whereKey($data['customer_id'])->where(fn ($q) => $q->where('assigned_sales_engineer_id', $request->user()->id)->orWhere('sales_engineer_id', $request->user()->id))->exists(), 403);
        }
        $rfq = Rfq::create($data);
        $this->audit('created_rfq', Rfq::class, $rfq->id);
        ActivityRecord::create(['user_id' => auth()->id(), 'action' => 'created_rfq', 'subject_type' => Rfq::class, 'subject_id' => $rfq->id]);
        return back()->with('status', 'RFQ saved.');
    }

    public function showRfq(Rfq $rfq)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort_if($user->isSalesEngineer() && $rfq->sales_engineer_id !== $user->id, 403);
        }
        // View activity logging removed per requirement
        return view('content.sales.rfq', ['rfq' => $rfq->load(['customer', 'salesEngineer', 'quotations', 'payments']), 'activities' => ActivityRecord::with('user')->where('subject_type', Rfq::class)->where('subject_id', $rfq->id)->latest()->get()]);
    }

    public function updateRfq(Request $request, Rfq $rfq)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort_if($user->isSalesEngineer() && $rfq->sales_engineer_id !== $user->id, 403);
        }

        $data = $request->validate([
            'rfq_description' => ['required', 'string'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'quotation_submission_target_date' => ['nullable', 'date'],
            'current_status' => ['required', Rule::in(['follow_up', 'follow_through', 'won', 'lost', 'cancelled'])],
            'total_quoted_price' => ['nullable', 'numeric', 'min:0'],
            'total_awarded_price' => ['nullable', 'numeric', 'min:0'],
            'total_invoiced_price' => ['nullable', 'numeric', 'min:0'],
            'advance_received' => ['nullable', 'numeric', 'min:0'],
            'order_cancel_reason' => ['nullable', 'string'],
        ]);

        $rfq->update($data);
        $this->audit('updated_rfq', Rfq::class, $rfq->id);
        return back()->with('status', 'RFQ updated successfully.');
    }

    public function destroyRfq(Rfq $rfq)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort_if($user->isSalesEngineer() && $rfq->sales_engineer_id !== $user->id, 403);
        }

        $this->audit('deleted_rfq', Rfq::class, $rfq->id);
        $rfq->delete();
        return redirect()->route('sales.rfqs')->with('status', 'RFQ deleted successfully.');
    }

    public function quotations()
    {
        $user = auth()->user();
        $quotations = Quotation::with('rfq.customer')
            ->when($user->isSalesEngineer() && !$user->isAdmin(), fn ($query) => $query->whereHas('rfq', fn ($rfq) => $rfq->where('sales_engineer_id', $user->id)))
            ->latest()
            ->paginate(15)->withQueryString();
        $rfqs = Rfq::when($user->isSalesEngineer() && !$user->isAdmin(), fn ($query) => $query->where('sales_engineer_id', $user->id))
            ->orderByDesc('rfq_received_date')
            ->get();

        return view('content.sales.quotations', compact('quotations', 'rfqs'));
    }

    public function storeQuotation(Request $request)
    {
        $data = $request->validate([
            'rfq_id' => ['required', 'exists:rfqs,id'],
            'quotation_number' => ['required', 'string', 'max:100', 'unique:quotations,quotation_number'],
            'quotation_date' => ['required', 'date'],
            'quoted_date' => ['required', 'date'],
            'submission_target_date' => ['nullable', 'date'],
            'actual_submitted_date' => ['nullable', 'date', 'after_or_equal:quoted_date'],
            'quoted_price' => ['required', 'numeric', 'gt:0'],
            'awarded_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'submitted', 'under_review', 'won', 'lost', 'cancelled'])],
            'commercial_accuracy' => ['boolean'],
            'loss_reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->user()->isSalesEngineer() && !$request->user()->isAdmin()) {
            abort_unless(Rfq::whereKey($data['rfq_id'])->where('sales_engineer_id', $request->user()->id)->exists(), 403);
        }

        $quotation = Quotation::create($data);
        $this->audit('created_quotation', Quotation::class, $quotation->id);
        $quotation->rfq->update(['total_quoted_price' => $quotation->rfq->quotations()->sum('quoted_price')]);
        ActivityRecord::create(['user_id' => auth()->id(), 'action' => 'created_quotation', 'subject_type' => Quotation::class, 'subject_id' => $quotation->id]);
        return back()->with('status', 'Quotation saved.');
    }

    public function updateQuotation(Request $request, Quotation $quotation)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort_if($user->isSalesEngineer() && $quotation->rfq->sales_engineer_id !== $user->id, 403);
        }

        $data = $request->validate([
            'quoted_price' => ['required', 'numeric', 'gt:0'],
            'awarded_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'submitted', 'under_review', 'won', 'lost', 'cancelled'])],
            'actual_submitted_date' => ['nullable', 'date'],
            'loss_reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $quotation->update($data);
        $quotation->rfq->update(['total_quoted_price' => $quotation->rfq->quotations()->sum('quoted_price')]);
        $this->audit('updated_quotation', Quotation::class, $quotation->id);
        return back()->with('status', 'Quotation updated.');
    }

    public function destroyQuotation(Quotation $quotation)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort_if($user->isSalesEngineer() && $quotation->rfq->sales_engineer_id !== $user->id, 403);
        }

        $this->audit('deleted_quotation', Quotation::class, $quotation->id);
        $rfq = $quotation->rfq;
        $quotation->delete();
        if ($rfq) {
            $rfq->update(['total_quoted_price' => $rfq->quotations()->sum('quoted_price')]);
        }

        return back()->with('status', 'Quotation deleted.');
    }

    public function dailyLog()
    {
        return view('content.sales.daily-log', ['logs' => DailyActivityLog::where('sales_engineer_id', auth()->id())->latest('activity_date')->paginate(15)->withQueryString()]);
    }

    public function storeDailyLog(Request $request)
    {
        $data = $request->validate([
            'activity_date' => ['required', 'date', 'before_or_equal:today'],
            'customer_calls' => ['required', 'integer', 'min:0'],
            'follow_up_calls' => ['required', 'integer', 'min:0'],
            'customer_visits' => ['required', 'integer', 'min:0'],
            'online_meetings' => ['required', 'integer', 'min:0'],
            'rfqs_received' => ['required', 'integer', 'min:0'],
            'quotations_submitted' => ['required', 'integer', 'min:0'],
            'crm_updated' => ['accepted'],
            'notes' => ['nullable', 'string'],
        ]);
        $log = DailyActivityLog::updateOrCreate(['sales_engineer_id' => auth()->id(), 'activity_date' => $data['activity_date']], $data);
        $this->audit('saved_daily_activity', DailyActivityLog::class, $log->id);
        ActivityRecord::create(['user_id' => auth()->id(), 'action' => 'saved_daily_kpi_log', 'subject_type' => DailyActivityLog::class, 'subject_id' => $log->id]);
        return back()->with('status', 'Daily KPI log saved.');
    }
}
