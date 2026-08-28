<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function create(): View
    {
        abort_unless(auth()->user()->isOwner() || auth()->user()->isAdmin(), 403);
        $engineers = User::where('role', 'sales_engineer')->orderBy('name')->get();
        return view('content.sales.customer-form', compact('engineers'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isOwner() || $request->user()->isAdmin(), 403);
        $data = $request->validate([
            'customer_code' => ['required', 'max:50', 'unique:customers,customer_code'],
            'company_name' => ['required', 'max:255'],
            'contact_person' => ['required', 'max:255'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'max:50'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'industry' => ['nullable', 'string', 'max:100'],
            'customer_type' => ['required', 'in:new,existing,qualified'],
            'sales_engineer_id' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', 'in:active,inactive,lost'],
            'portal_email' => ['required', 'email', 'unique:users,email'],
            'portal_password' => ['required', 'min:8'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
        ]);

        $portalEmail = $data['portal_email'];
        $portalPassword = $data['portal_password'];
        unset($data['portal_email'], $data['portal_password']);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('customers', 'public');
            $data['photo'] = $path;
        }

        // Sync engineer IDs
        if (isset($data['sales_engineer_id'])) {
            $data['assigned_sales_engineer_id'] = $data['sales_engineer_id'];
        }

        $customer = Customer::create($data);
        $customer->user()->create([
            'name' => $customer->company_name,
            'email' => $portalEmail,
            'password' => Hash::make($portalPassword),
            'role' => 'customer',
            'status' => $data['status'] ?? 'active',
        ]);

        $this->audit('created_customer', Customer::class, $customer->id);
        return redirect()->route('customers.show', $customer)->with('status', 'Customer and portal login created.');
    }

    public function edit(Customer $customer): View
    {
        abort_unless(auth()->user()->isOwner() || auth()->user()->isAdmin(), 403);
        $engineers = User::where('role', 'sales_engineer')->orderBy('name')->get();
        return view('content.sales.customer-form', compact('customer', 'engineers'));
    }

    public function update(Request $request, Customer $customer)
    {
        abort_unless($request->user()->isOwner() || $request->user()->isAdmin(), 403);
        $data = $request->validate([
            'customer_code' => ['required', 'max:50', 'unique:customers,customer_code,' . $customer->id],
            'company_name' => ['required', 'max:255'],
            'contact_person' => ['required', 'max:255'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'max:50'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'industry' => ['nullable', 'string', 'max:100'],
            'customer_type' => ['required', 'in:new,existing,qualified'],
            'sales_engineer_id' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', 'in:active,inactive,lost'],
            'portal_email' => ['nullable', 'email', 'unique:users,email,' . $customer->user?->id],
            'portal_password' => ['nullable', 'min:8'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
        ]);

        $portalEmail = $data['portal_email'] ?? null;
        $portalPassword = $data['portal_password'] ?? null;
        unset($data['portal_email'], $data['portal_password']);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            if ($customer->photo && Storage::disk('public')->exists($customer->photo)) {
                Storage::disk('public')->delete($customer->photo);
            }
            $path = $request->file('photo')->store('customers', 'public');
            $data['photo'] = $path;
        }

        if (isset($data['sales_engineer_id'])) {
            $data['assigned_sales_engineer_id'] = $data['sales_engineer_id'];
        }

        $customer->update($data);
        $portal = $customer->user;
        if ($portal) {
            $portalUpdate = array_filter([
                'email' => $portalEmail,
                'password' => $portalPassword ? Hash::make($portalPassword) : null,
                'status' => $data['status'] ?? null,
            ]);
            if (!empty($portalUpdate)) {
                $portal->update($portalUpdate);
            }
        }

        $this->audit('updated_customer', Customer::class, $customer->id);
        return redirect()->route('customers.show', $customer)->with('status', 'Customer updated.');
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $customers = Customer::with('salesEngineer')
            ->when($user->isSalesEngineer(), fn ($query) => $query->where(function ($q) use ($user) {
                $q->where('sales_engineer_id', $user->id)->orWhere('assigned_sales_engineer_id', $user->id);
            }))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search');
                $query->where(function ($nested) use ($search): void {
                    $nested->where('company_name', 'like', "%{$search}%")
                        ->orWhere('customer_code', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($request->integer('per_page', 10))
            ->withQueryString();

        return view('content.sales.customers', compact('customers'));
    }

    public function show(Customer $customer): View
    {
        $user = request()->user();
        if (!$user->isAdmin()) {
            abort_if($user->isSalesEngineer() && $customer->sales_engineer_id !== $user->id && $customer->assigned_sales_engineer_id !== $user->id, 403);
        }

        $customer->load(['salesEngineer', 'rfqs' => fn ($query) => $query->latest()->take(15), 'complaints' => fn ($query) => $query->latest()->take(15)]);
        // View activity logging removed per requirement
        return view('content.sales.customer', [
            'customer' => $customer,
            'activities' => \App\Models\ActivityRecord::with('user')->where('subject_type', Customer::class)->where('subject_id', $customer->id)->latest()->get()
        ]);
    }

    public function destroy(Customer $customer)
    {
        abort_unless(auth()->user()->isOwner() || auth()->user()->isAdmin(), 403);
        $this->audit('deleted_customer', Customer::class, $customer->id);
        if ($customer->photo && Storage::disk('public')->exists($customer->photo)) {
            Storage::disk('public')->delete($customer->photo);
        }
        $customer->delete();
        return redirect()->route('customers.index')->with('status', 'Customer deleted successfully.');
    }
}
