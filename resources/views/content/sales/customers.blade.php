@extends('layouts/layoutMaster')
@section('title', 'Customers')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div>
    <h4 class="mb-1">Customer Directory</h4>
    <p class="text-muted mb-0">Search customer ownership, mapped engineers, and active opportunities.</p>
  </div>
  <div class="d-flex gap-2">
    @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
      <a class="btn btn-primary" href="{{ route('customers.create') }}">
        <i class="mdi mdi-plus me-1"></i> Add Customer
      </a>
    @endif
    <form class="d-flex gap-2" method="GET">
      <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search customers...">
      <button class="btn btn-outline-primary">Search</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="table-responsive text-nowrap">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Customer</th>
          <th>Contact</th>
          <th>Type</th>
          <th>Sales Engineer</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($customers as $customer)
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <img src="{{ $customer->photo_url }}" alt="{{ $customer->company_name }}" class="rounded-circle" width="36" height="36" style="object-fit: cover;">
                <div>
                  <strong>{{ $customer->company_name }}</strong>
                  <small class="d-block text-muted">{{ $customer->customer_code }}</small>
                </div>
              </div>
            </td>
            <td>
              {{ $customer->contact_person }}
              <small class="d-block text-muted">{{ $customer->email ?: $customer->phone }}</small>
            </td>
            <td>
              <span class="badge bg-label-info">{{ ucfirst($customer->customer_type) }}</span>
            </td>
            <td>
              @if($customer->salesEngineer)
                <span class="fw-medium">{{ $customer->salesEngineer->name }}</span>
              @else
                <span class="text-muted">Unassigned</span>
              @endif
            </td>
            <td>
              <span class="badge bg-label-{{ $customer->status === 'active' ? 'success' : ($customer->status === 'lost' ? 'danger' : 'secondary') }}">
                {{ ucfirst($customer->status) }}
              </span>
            </td>
            <td class="text-end">
              <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-sm btn-icon btn-outline-info" href="{{ route('customers.show', $customer) }}" title="View Customer Details">
                  <i class="mdi mdi-eye-outline"></i>
                </a>
                @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                  <a class="btn btn-sm btn-icon btn-outline-primary" href="{{ route('customers.edit', $customer) }}" title="Edit Customer">
                    <i class="mdi mdi-pencil-outline"></i>
                  </a>
                  <button type="button" class="btn btn-sm btn-icon btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteCustomerModal{{ $customer->id }}" title="Delete Customer">
                    <i class="mdi mdi-trash-can-outline"></i>
                  </button>
                @endif
              </div>

              {{-- Delete Confirmation Modal --}}
              @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                <div class="modal fade text-start" id="deleteCustomerModal{{ $customer->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <form method="POST" action="{{ route('customers.destroy', $customer) }}">
                        @csrf
                        @method('DELETE')
                        <div class="modal-header">
                          <h5 class="modal-title text-danger"><i class="mdi mdi-alert-circle-outline me-1"></i> Delete Customer</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <p class="mb-0">Are you sure you want to delete customer <strong>{{ $customer->company_name }}</strong> ({{ $customer->customer_code }})? This will also remove the customer portal login.</p>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-danger">Confirm Delete</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-4">No customers found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($customers->hasPages())
    <div class="card-footer">
      {{ $customers->links() }}
    </div>
  @endif
</div>
@endsection
