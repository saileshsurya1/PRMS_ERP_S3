@extends('layouts/layoutMaster')
@section('title', 'Customer Complaints')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1">Customer Complaints</h4>
    <p class="text-muted mb-0">Log customer issues, assign engineer ownership, and monitor resolution timelines.</p>
  </div>
  <form method="GET" class="d-flex gap-2">
    <select class="form-select" name="status" onchange="this.form.submit()">
      <option value="">All Statuses</option>
      @foreach(['open', 'in_progress', 'resolved'] as $status)
        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
      @endforeach
    </select>
  </form>
</div>

<div class="row g-4">
  <div class="col-xl-4">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Log New Complaint</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('sales.complaints.store') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label">Customer</label>
            <select class="form-select" name="customer_id" required>
              <option value="">Select Customer</option>
              @foreach($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->company_name }} ({{ $customer->customer_code }})</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Reported Date</label>
            <input class="form-control" type="date" name="reported_date" value="{{ now()->toDateString() }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Subject / Issue Summary</label>
            <input class="form-control" name="subject" placeholder="e.g. Delivery delay" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Detailed Description</label>
            <textarea class="form-control" name="description" rows="4" placeholder="Specific problem details..." required></textarea>
          </div>
          <button type="submit" class="btn btn-primary w-100">Log Complaint</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-xl-8">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Active Complaints Register</h5>
      </div>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Issue</th>
              <th>Customer</th>
              <th>Reported</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($complaints as $complaint)
              <tr>
                <td>
                  <strong>{{ $complaint->subject }}</strong>
                  <small class="d-block text-muted text-wrap" style="max-width: 250px;">{{ Str::limit($complaint->description, 70) }}</small>
                </td>
                <td>{{ $complaint->customer->company_name }}</td>
                <td>{{ $complaint->reported_date?->format('d M Y') }}</td>
                <td>
                  <span class="badge bg-label-{{ $complaint->status === 'resolved' ? 'success' : ($complaint->status === 'in_progress' ? 'info' : 'warning') }}">
                    {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                  </span>
                </td>
                <td class="text-end">
                  <div class="d-flex justify-content-end gap-2">
                    <a class="btn btn-sm btn-icon btn-outline-info" href="{{ route('sales.complaints.show', $complaint) }}" title="View Details">
                      <i class="mdi mdi-eye-outline"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-icon btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editComplaintModal{{ $complaint->id }}" title="Update Complaint">
                      <i class="mdi mdi-pencil-outline"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteComplaintModal{{ $complaint->id }}" title="Delete Complaint">
                      <i class="mdi mdi-trash-can-outline"></i>
                    </button>
                  </div>

                  {{-- Edit Complaint Modal --}}
                  <div class="modal fade text-start" id="editComplaintModal{{ $complaint->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <form method="POST" action="{{ route('sales.complaints.update', $complaint) }}">
                          @csrf
                          @method('PATCH')
                          <div class="modal-header">
                            <h5 class="modal-title">Update Complaint: {{ $complaint->subject }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <div class="mb-3">
                              <label class="form-label">Status</label>
                              <select class="form-select" name="status">
                                <option value="open" @selected($complaint->status === 'open')>Open</option>
                                <option value="in_progress" @selected($complaint->status === 'in_progress')>In Progress</option>
                                <option value="resolved" @selected($complaint->status === 'resolved')>Resolved</option>
                              </select>
                            </div>
                            <div class="mb-3">
                              <label class="form-label">Resolution Notes</label>
                              <textarea class="form-control" name="resolution" rows="4" placeholder="Explain how the issue was investigated or resolved...">{{ old('resolution', $complaint->resolution) }}</textarea>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Resolution</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>

                  {{-- Delete Confirmation Modal --}}
                  <div class="modal fade text-start" id="deleteComplaintModal{{ $complaint->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                        <form method="POST" action="{{ route('sales.complaints.destroy', $complaint) }}">
                          @csrf
                          @method('DELETE')
                          <div class="modal-header">
                            <h5 class="modal-title text-danger"><i class="mdi mdi-alert-circle-outline me-1"></i> Delete Complaint</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <p class="mb-0">Are you sure you want to delete this complaint from <strong>{{ $complaint->customer->company_name }}</strong>?</p>
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
                <td colspan="5" class="text-center text-muted py-4">No complaints found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @include('partials.pagination', ['paginator' => $complaints])
    </div>
  </div>
</div>
@endsection
