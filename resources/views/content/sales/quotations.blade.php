@extends('layouts/layoutMaster')
@section('title', 'Quotation Tracking')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1">Quotation Tracking</h4>
    <p class="text-muted mb-0">Track commercial quote submissions and track conversion lead time.</p>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-primary" href="{{ route('sales.rfqs') }}">
      <i class="mdi mdi-file-document-edit-outline me-1"></i> RFQ Register
    </a>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header">
    <h5 class="mb-0">Generate New Quotation</h5>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('sales.quotations.store') }}" class="row g-3">
      @csrf
      <div class="col-md-4">
        <label class="form-label">Associated RFQ</label>
        <select class="form-select" name="rfq_id" required>
          <option value="">Select RFQ</option>
          @foreach($rfqs as $rfq)
            <option value="{{ $rfq->id }}">{{ $rfq->rfq_number }} - {{ $rfq->customer->company_name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Quotation Number</label>
        <input class="form-control" name="quotation_number" placeholder="e.g. QUO-2026-001" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Quoted Price (₹)</label>
        <input class="form-control" type="number" step="0.01" min="0.01" name="quoted_price" placeholder="0.00" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Quotation Creation Date</label>
        <input class="form-control" type="date" name="quotation_date" value="{{ now()->toDateString() }}" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Actual Quoted Date</label>
        <input class="form-control" type="date" name="quoted_date" value="{{ now()->toDateString() }}" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
          <option value="draft">Draft</option>
          <option value="submitted" selected>Submitted</option>
          <option value="under_review">Under review</option>
          <option value="won">Won</option>
          <option value="lost">Lost</option>
        </select>
      </div>
      <div class="col-12 text-end">
        <button type="submit" class="btn btn-primary">Save Quotation</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="table-responsive text-nowrap">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Quotation</th>
          <th>RFQ Reference</th>
          <th>Customer</th>
          <th>Quoted (₹)</th>
          <th>Status</th>
          <th>Lead Time</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($quotations as $quotation)
          <tr>
            <td><strong>{{ $quotation->quotation_number }}</strong></td>
            <td><code>{{ $quotation->rfq->rfq_number }}</code></td>
            <td>{{ $quotation->rfq->customer->company_name }}</td>
            <td>₹ {{ number_format((float)$quotation->quoted_price, 2) }}</td>
            <td>
              <span class="badge bg-label-{{ $quotation->status === 'won' ? 'success' : ($quotation->status === 'lost' ? 'danger' : 'info') }}">
                {{ ucfirst(str_replace('_', ' ', $quotation->status)) }}
              </span>
            </td>
            <td>{{ $quotation->submission_lead_time_hours ? $quotation->submission_lead_time_hours.' hrs' : 'Pending' }}</td>
            <td class="text-end">
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-sm btn-icon btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editQuotationModal{{ $quotation->id }}" title="Edit Quotation">
                  <i class="mdi mdi-pencil-outline"></i>
                </button>
                <button type="button" class="btn btn-sm btn-icon btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteQuotationModal{{ $quotation->id }}" title="Delete Quotation">
                  <i class="mdi mdi-trash-can-outline"></i>
                </button>
              </div>

              {{-- Edit Quotation Modal --}}
              <div class="modal fade text-start" id="editQuotationModal{{ $quotation->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <form method="POST" action="{{ route('sales.quotations.update', $quotation) }}">
                      @csrf
                      @method('PATCH')
                      <div class="modal-header">
                        <h5 class="modal-title">Edit Quotation: {{ $quotation->quotation_number }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <div class="mb-3">
                          <label class="form-label">Quoted Price (₹)</label>
                          <input type="number" step="0.01" class="form-control" name="quoted_price" value="{{ old('quoted_price', $quotation->quoted_price) }}" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Awarded Price (₹)</label>
                          <input type="number" step="0.01" class="form-control" name="awarded_price" value="{{ old('awarded_price', $quotation->awarded_price) }}">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Status</label>
                          <select class="form-select" name="status">
                            <option value="draft" @selected($quotation->status === 'draft')>Draft</option>
                            <option value="submitted" @selected($quotation->status === 'submitted')>Submitted</option>
                            <option value="under_review" @selected($quotation->status === 'under_review')>Under review</option>
                            <option value="won" @selected($quotation->status === 'won')>Won</option>
                            <option value="lost" @selected($quotation->status === 'lost')>Lost</option>
                            <option value="cancelled" @selected($quotation->status === 'cancelled')>Cancelled</option>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Actual Submitted Date</label>
                          <input type="date" class="form-control" name="actual_submitted_date" value="{{ old('actual_submitted_date', $quotation->actual_submitted_date?->toDateString()) }}">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Notes / Remarks</label>
                          <textarea class="form-control" name="notes" rows="2">{{ old('notes', $quotation->notes) }}</textarea>
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
              <div class="modal fade text-start" id="deleteQuotationModal{{ $quotation->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <form method="POST" action="{{ route('sales.quotations.destroy', $quotation) }}">
                      @csrf
                      @method('DELETE')
                      <div class="modal-header">
                        <h5 class="modal-title text-danger"><i class="mdi mdi-alert-circle-outline me-1"></i> Delete Quotation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <p class="mb-0">Are you sure you want to delete quotation <strong>{{ $quotation->quotation_number }}</strong>?</p>
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
            <td colspan="7" class="text-center text-muted py-4">No quotations found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @include('partials.pagination', ['paginator' => $quotations])
</div>
@endsection
