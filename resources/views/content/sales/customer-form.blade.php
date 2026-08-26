@extends('layouts/layoutMaster')
@section('title', isset($customer) ? 'Edit Customer' : 'Add Customer')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1">{{ isset($customer) ? 'Edit Customer' : 'Create New Customer' }}</h4>
    <p class="text-muted mb-0">Manage customer credentials, engineer assignments, and contact details.</p>
  </div>
  <a class="btn btn-outline-primary" href="{{ route('customers.index') }}">
    <i class="mdi mdi-arrow-left me-1"></i> Back to Customers
  </a>
</div>

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ isset($customer) ? route('customers.update', $customer) : route('customers.store') }}" enctype="multipart/form-data" class="row g-3">
      @csrf
      @if(isset($customer))
        @method('PATCH')
      @endif

      <div class="col-md-4">
        <label class="form-label">Customer Code</label>
        <input class="form-control" name="customer_code" value="{{ old('customer_code', $customer->customer_code ?? '') }}" placeholder="e.g. CUST-001" required>
      </div>

      <div class="col-md-8">
        <label class="form-label">Company Name</label>
        <input class="form-control" name="company_name" value="{{ old('company_name', $customer->company_name ?? '') }}" placeholder="Company Name Ltd." required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Contact Person</label>
        <input class="form-control" name="contact_person" value="{{ old('contact_person', $customer->contact_person ?? '') }}" placeholder="Primary contact name" required>
      </div>

      <div class="col-md-3">
        <label class="form-label">Email</label>
        <input class="form-control" name="email" type="email" value="{{ old('email', $customer->email ?? '') }}" placeholder="contact@example.com">
      </div>

      <div class="col-md-3">
        <label class="form-label">Phone</label>
        <input class="form-control" name="phone" value="{{ old('phone', $customer->phone ?? '') }}" placeholder="+91 98765 43210">
      </div>

      <div class="col-md-4">
        <label class="form-label">Customer Type</label>
        <select class="form-select" name="customer_type">
          @foreach(['new', 'existing', 'qualified'] as $type)
            <option value="{{ $type }}" @selected(old('customer_type', $customer->customer_type ?? 'new') === $type)>{{ ucfirst($type) }}</option>
          @endforeach
        </select>
      </div>

      {{-- Customer-to-Engineer Mapping Dropdown --}}
      <div class="col-md-4">
        <label class="form-label">Assigned Sales Engineer</label>
        <select class="form-select" name="sales_engineer_id">
          <option value="">Unassigned</option>
          @foreach($engineers as $engineer)
            <option value="{{ $engineer->id }}" @selected(old('sales_engineer_id', $customer->sales_engineer_id ?? $customer->assigned_sales_engineer_id ?? '') == $engineer->id)>
              {{ $engineer->name }} ({{ $engineer->email }})
            </option>
          @endforeach
        </select>
      </div>

      {{-- Admin-only Status Dropdown --}}
      @if(auth()->user()->isAdmin())
        <div class="col-md-4">
          <label class="form-label">Customer Status (Admin)</label>
          <select class="form-select" name="status">
            <option value="active" @selected(old('status', $customer->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $customer->status ?? 'active') === 'inactive')>Inactive</option>
            <option value="lost" @selected(old('status', $customer->status ?? 'active') === 'lost')>Lost</option>
          </select>
        </div>
      @endif

      <div class="col-md-6">
        <label class="form-label">City</label>
        <input class="form-control" name="city" value="{{ old('city', $customer->city ?? '') }}" placeholder="e.g. Pune, Mumbai">
      </div>

      <div class="col-md-6">
        <label class="form-label">Industry</label>
        <input class="form-control" name="industry" value="{{ old('industry', $customer->industry ?? '') }}" placeholder="e.g. Automation, Manufacturing">
      </div>

      <div class="col-12">
        <label class="form-label">Address</label>
        <textarea class="form-control" name="address" rows="2" placeholder="Full office/factory address">{{ old('address', $customer->address ?? '') }}</textarea>
      </div>

      <div class="col-12">
        <label class="form-label">Customer Logo / Image</label>
        <input type="file" class="form-control" name="photo" accept="image/*">
        @if(isset($customer) && $customer->photo)
          <small class="text-muted d-block mt-1">Current photo: <img src="{{ $customer->photo_url }}" width="30" height="30" class="rounded-circle ms-1" alt="Logo"></small>
        @endif
      </div>

      <div class="col-12"><hr class="my-3"><h5>Customer Portal Login</h5><p class="text-muted">The customer uses this email and password to log into their dedicated portal.</p></div>
      <div class="col-md-6">
        <label class="form-label">Portal Email</label>
        <input class="form-control" type="email" name="portal_email" value="{{ old('portal_email', $customer->user?->email ?? '') }}" {{ isset($customer) ? '' : 'required' }}>
      </div>
      <div class="col-md-6">
        <label class="form-label">{{ isset($customer) ? 'New Portal Password (leave blank to keep current)' : 'Portal Password' }}</label>
        <input class="form-control" type="password" name="portal_password" {{ isset($customer) ? '' : 'required' }}>
      </div>

      <div class="col-12 mt-4">
        <button type="submit" class="btn btn-primary">{{ isset($customer) ? 'Update Customer' : 'Create Customer' }}</button>
      </div>
    </form>
  </div>
</div>
@endsection
