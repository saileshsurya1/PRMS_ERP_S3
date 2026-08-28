@extends('layouts/layoutMaster')
@section('title', 'RFQ Register')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1">RFQ Register</h4>
    <p class="text-muted mb-0">Capture customer requests from initial receipt through order award outcome.</p>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-primary" href="{{ route('sales.quotations') }}">
      <i class="mdi mdi-file-chart-outline me-1"></i> Quotation Register
    </a>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header">
    <h5 class="mb-0">Capture New RFQ</h5>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('sales.rfqs.store') }}" class="row g-3">
      @csrf
      <div class="col-md-4">
        <label class="form-label">Customer</label>
        <select class="form-select" name="customer_id" required>
          <option value="">Select Customer</option>
          @foreach($customers as $customer)
            <option value="{{ $customer->id }}">{{ $customer->company_name }} ({{ $customer->customer_code }})</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">RFQ Number</label>
        <input class="form-control" name="rfq_number" placeholder="e.g. RFQ-2026-001" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Received Date</label>
        <input class="form-control" type="date" name="rfq_received_date" value="{{ now()->toDateString() }}" required>
      </div>
      <div class="col-md-8">
        <label class="form-label">Description / Requirement</label>
        <textarea class="form-control" name="rfq_description" rows="2" placeholder="Technical specifications..." required></textarea>
      </div>
      <div class="col-md-4">
        <label class="form-label">Quantity</label>
        <input class="form-control" type="number" step="0.001" min="0.001" name="quantity" placeholder="Quantity" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Lead Time (days)</label>
        <input class="form-control" type="number" min="0" name="lead_time_days" placeholder="e.g. 30">
      </div>
      <div class="col-md-3">
        <label class="form-label">Quotation Target Date</label>
        <input class="form-control" type="date" name="quotation_submission_target_date" value="{{ now()->addDays(7)->toDateString() }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">Estimated Price (₹)</label>
        <input class="form-control" type="number" min="0" step="0.01" name="total_quoted_price" placeholder="0.00">
      </div>
      <div class="col-md-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="current_status">
          <option value="follow_up">Follow-up</option>
          <option value="follow_through">Follow through</option>
          <option value="won">Won</option>
          <option value="lost">Lost</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div class="col-12 text-end">
        <button type="submit" class="btn btn-primary">Save RFQ</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="table-responsive text-nowrap">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>RFQ Number</th>
          <th>Customer</th>
          <th>Received Date</th>
          <th>Quoted (₹)</th>
          <th>Status</th>
          <th>Pending (₹)</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rfqs as $rfq)
          <tr>
            <td><strong>{{ $rfq->rfq_number }}</strong></td>
            <td>{{ $rfq->customer->company_name }}</td>
            <td>{{ $rfq->rfq_received_date->format('d M Y') }}</td>
            <td>₹ {{ number_format((float)$rfq->total_quoted_price, 2) }}</td>
            <td>
              <span class="badge bg-label-{{ $rfq->current_status === 'won' ? 'success' : ($rfq->current_status === 'lost' ? 'danger' : 'primary') }}">
                {{ ucfirst(str_replace('_', ' ', $rfq->current_status)) }}
              </span>
            </td>
            <td>₹ {{ number_format((float)$rfq->pending_amount, 2) }}</td>
            <td class="text-end">
              <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-sm btn-icon btn-outline-info" href="{{ route('sales.rfqs.show', $rfq) }}" title="View RFQ Details">
                  <i class="mdi mdi-eye-outline"></i>
                </a>
                <button type="button" class="btn btn-sm btn-icon btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editRfqModal{{ $rfq->id }}" title="Edit RFQ">
                  <i class="mdi mdi-pencil-outline"></i>
                </button>
                <button type="button" class="btn btn-sm btn-icon btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteRfqModal{{ $rfq->id }}" title="Delete RFQ">
                  <i class="mdi mdi-trash-can-outline"></i>
                </button>
              </div>

              {{-- Edit RFQ Modal --}}
              <div class="modal fade text-start" id="editRfqModal{{ $rfq->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <form method="POST" action="{{ route('sales.rfqs.update', $rfq) }}">
                      @csrf
                      @method('PATCH')
                      <div class="modal-header">
                        <h5 class="modal-title">Edit RFQ: {{ $rfq->rfq_number }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <div class="mb-3">
                          <label class="form-label">Description</label>
                          <textarea class="form-control" name="rfq_description" rows="3" required>{{ old('rfq_description', $rfq->rfq_description) }}</textarea>
                        </div>
                        <div class="row g-3 mb-3">
                          <div class="col-md-6">
                            <label class="form-label">Quantity</label>
                            <input class="form-control" type="number" step="0.001" name="quantity" value="{{ old('quantity', $rfq->quantity) }}" required>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label">Lead Time (days)</label>
                            <input class="form-control" type="number" name="lead_time_days" value="{{ old('lead_time_days', $rfq->lead_time_days) }}">
                          </div>
                        </div>
                        <div class="row g-3 mb-3">
                          <div class="col-md-6">
                            <label class="form-label">Quoted Price (₹)</label>
                            <input class="form-control" type="number" step="0.01" name="total_quoted_price" value="{{ old('total_quoted_price', $rfq->total_quoted_price) }}">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label">Awarded Price (₹)</label>
                            <input class="form-control" type="number" step="0.01" name="total_awarded_price" value="{{ old('total_awarded_price', $rfq->total_awarded_price) }}">
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Status</label>
                          <select class="form-select" name="current_status">
                            <option value="follow_up" @selected($rfq->current_status === 'follow_up')>Follow-up</option>
                            <option value="follow_through" @selected($rfq->current_status === 'follow_through')>Follow through</option>
                            <option value="won" @selected($rfq->current_status === 'won')>Won</option>
                            <option value="lost" @selected($rfq->current_status === 'lost')>Lost</option>
                            <option value="cancelled" @selected($rfq->current_status === 'cancelled')>Cancelled</option>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Target Submission Date</label>
                          <input class="form-control" type="date" name="quotation_submission_target_date" value="{{ old('quotation_submission_target_date', $rfq->quotation_submission_target_date?->toDateString()) }}">
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

              {{-- Delete Confirmation Modal --}}
              <div class="modal fade text-start" id="deleteRfqModal{{ $rfq->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <form method="POST" action="{{ route('sales.rfqs.destroy', $rfq) }}">
                      @csrf
                      @method('DELETE')
                      <div class="modal-header">
                        <h5 class="modal-title text-danger"><i class="mdi mdi-alert-circle-outline me-1"></i> Delete RFQ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <p class="mb-0">Are you sure you want to delete RFQ <strong>{{ $rfq->rfq_number }}</strong>? Associated quotations and payment records will also be removed.</p>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Delete</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center text-muted py-4">No RFQs recorded.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @include('partials.pagination', ['paginator' => $rfqs])
</div>
@endsection
