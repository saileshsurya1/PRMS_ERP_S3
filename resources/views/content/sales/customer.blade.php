@extends('layouts/layoutMaster')
@section('title', $customer->company_name)
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <div class="d-flex align-items-center gap-3">
    <img src="{{ $customer->photo_url }}" alt="{{ $customer->company_name }}" class="rounded-circle shadow-sm" width="56" height="56" style="object-fit: cover; border: 2px solid #696cff;">
    <div>
      <h4 class="mb-1">{{ $customer->company_name }}</h4>
      <p class="text-muted mb-0">
        <code>{{ $customer->customer_code }}</code> · {{ $customer->contact_person }} · 
        <span class="badge bg-label-{{ $customer->status === 'active' ? 'success' : ($customer->status === 'lost' ? 'danger' : 'secondary') }}">{{ ucfirst($customer->status) }}</span>
      </p>
    </div>
  </div>
  <div class="d-flex gap-2">
    @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
      <a class="btn btn-outline-primary" href="{{ route('customers.edit', $customer) }}">
        <i class="mdi mdi-pencil me-1"></i> Edit
      </a>
    @endif
    <a class="btn btn-outline-secondary" href="{{ route('customers.index') }}">
      <i class="mdi mdi-arrow-left me-1"></i> Back to Customers
    </a>
  </div>
</div>

<div class="row g-4">
  <div class="col-xl-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="mb-0">Customer Details</h5>
      </div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-5">Email</dt>
          <dd class="col-7">{{ $customer->email ?: '-' }}</dd>
          <dt class="col-5">Phone</dt>
          <dd class="col-7">{{ $customer->phone ?: '-' }}</dd>
          <dt class="col-5">City</dt>
          <dd class="col-7">{{ $customer->city ?: '-' }}</dd>
          <dt class="col-5">Industry</dt>
          <dd class="col-7">{{ $customer->industry ?: '-' }}</dd>
          <dt class="col-5">Sales Engineer</dt>
          <dd class="col-7">
            @if($customer->salesEngineer)
              <div class="d-flex align-items-center gap-1">
                <img src="{{ $customer->salesEngineer->profile_photo_url }}" width="22" height="22" class="rounded-circle" style="object-fit: cover;">
                <span>{{ $customer->salesEngineer->name }}</span>
              </div>
            @else
              <span class="text-muted">Unassigned</span>
            @endif
          </dd>
          <dt class="col-5">Customer Type</dt>
          <dd class="col-7"><span class="badge bg-label-info">{{ ucfirst($customer->customer_type) }}</span></dd>
          <dt class="col-5">Address</dt>
          <dd class="col-7">{{ $customer->address ?: '-' }}</dd>
        </dl>
      </div>
    </div>
  </div>

  <div class="col-xl-8">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Recent RFQs</h5>
        <a href="{{ route('sales.rfqs') }}" class="btn btn-sm btn-outline-primary">Open RFQ Register</a>
      </div>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Number</th>
              <th>Received Date</th>
              <th>Value (₹)</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($customer->rfqs as $rfq)
              <tr>
                <td><strong>{{ $rfq->rfq_number }}</strong></td>
                <td>{{ $rfq->rfq_received_date?->format('d M Y') }}</td>
                <td>₹ {{ number_format((float) $rfq->total_quoted_price, 2) }}</td>
                <td>
                  <span class="badge bg-label-{{ $rfq->current_status === 'won' ? 'success' : ($rfq->current_status === 'lost' ? 'danger' : 'primary') }}">
                    {{ ucfirst(str_replace('_', ' ', $rfq->current_status)) }}
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-muted py-3">No RFQs recorded.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Customer Complaints</h5>
        <a href="{{ route('sales.complaints.index') }}" class="btn btn-sm btn-outline-primary">View All Complaints</a>
      </div>
      <div class="card-body">
        @forelse($customer->complaints as $complaint)
          <div class="border-bottom pb-2 mb-2 d-flex justify-content-between align-items-start">
            <div>
              <strong>{{ $complaint->subject }}</strong>
              <small class="d-block text-muted">{{ $complaint->description }}</small>
              <small class="text-muted">Reported on {{ $complaint->reported_date?->format('d M Y') }}</small>
            </div>
            <span class="badge bg-label-{{ $complaint->status === 'resolved' ? 'success' : 'warning' }}">
              {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
            </span>
          </div>
        @empty
          <p class="text-muted mb-0">No complaints recorded.</p>
        @endforelse
      </div>
    </div>
  </div>
</div>

<div class="card mt-4">
  <div class="card-header">
    <h5 class="mb-0">Customer Activity Trail</h5>
  </div>
  <div class="card-body">
    @forelse($activities as $activity)
      <div class="border-bottom py-2 d-flex justify-content-between align-items-center">
        <div>
          <strong>{{ ucfirst(str_replace('_', ' ', $activity->action)) }}</strong>
          <small class="d-block text-muted">By {{ $activity->user?->name ?: 'System' }} on {{ $activity->created_at->format('d M Y H:i:s') }}</small>
        </div>
      </div>
    @empty
      <p class="text-muted mb-0">No activity recorded.</p>
    @endforelse
  </div>
</div>
@endsection
