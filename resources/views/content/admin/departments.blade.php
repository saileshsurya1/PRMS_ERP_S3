@extends('layouts/layoutMaster')
@section('title', 'Department Master')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1">Department Master</h4>
    <p class="text-muted mb-0">Manage organization departments and organizational structure.</p>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
      <i class="mdi mdi-plus me-1"></i> Add Department
    </button>
    <a class="btn btn-outline-primary" href="{{ route('admin.users') }}">Manage Users</a>
    <a class="btn btn-outline-primary" href="{{ route('admin.menus') }}">System Configuration</a>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">All Departments</h5>
    <form method="GET" class="d-flex gap-2">
      <input type="text" name="search" class="form-control form-control-sm" placeholder="Search departments..." value="{{ request('search') }}">
      <button class="btn btn-sm btn-outline-primary" type="submit">Search</button>
    </form>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table align-middle table-hover mb-0">
      <thead>
        <tr>
          <th>Department Name</th>
          <th>Code</th>
          <th>Description</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($departments as $department)
          <tr>
            <td><strong>{{ $department->name }}</strong></td>
            <td><span class="badge bg-label-info">{{ $department->code ?: 'N/A' }}</span></td>
            <td><span class="text-muted">{{ Str::limit($department->description ?: '—', 50) }}</span></td>
            <td>
              <span class="badge bg-label-{{ $department->is_active ? 'success' : 'secondary' }}">
                {{ $department->is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="text-end">
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-sm btn-icon btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editDepartmentModal{{ $department->id }}" title="Edit">
                  <i class="mdi mdi-pencil-outline"></i>
                </button>
                <button type="button" class="btn btn-sm btn-icon btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteDepartmentModal{{ $department->id }}" title="Delete">
                  <i class="mdi mdi-trash-can-outline"></i>
                </button>
              </div>

              {{-- Edit Modal --}}
              <div class="modal fade text-start" id="editDepartmentModal{{ $department->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <form method="POST" action="{{ route('admin.departments.update', $department) }}">
                      @csrf
                      @method('PATCH')
                      <div class="modal-header">
                        <h5 class="modal-title">Edit Department</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <div class="mb-3">
                          <label class="form-label">Department Name</label>
                          <input type="text" class="form-control" name="name" value="{{ old('name', $department->name) }}" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Code</label>
                          <input type="text" class="form-control" name="code" value="{{ old('code', $department->code) }}" placeholder="e.g. SALES">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Description</label>
                          <textarea class="form-control" name="description" rows="3">{{ old('description', $department->description) }}</textarea>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Status</label>
                          <select class="form-select" name="is_active">
                            <option value="1" @selected($department->is_active)>Active</option>
                            <option value="0" @selected(!$department->is_active)>Inactive</option>
                          </select>
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
              <div class="modal fade text-start" id="deleteDepartmentModal{{ $department->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <form method="POST" action="{{ route('admin.departments.destroy', $department) }}">
                      @csrf
                      @method('DELETE')
                      <div class="modal-header">
                        <h5 class="modal-title text-danger"><i class="mdi mdi-alert-circle-outline me-1"></i> Delete Department</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <p class="mb-0">Are you sure you want to delete department <strong>{{ $department->name }}</strong>? This action cannot be undone.</p>
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
            <td colspan="5" class="text-center text-muted py-4">No departments found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @include('partials.pagination', ['paginator' => $departments])
</div>

{{-- Add Department Modal --}}
<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('admin.departments.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Create New Department</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Department Name</label>
            <input type="text" class="form-control" name="name" placeholder="e.g. Quality Assurance" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Code</label>
            <input type="text" class="form-control" name="code" placeholder="e.g. QA">
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="3" placeholder="Department responsibilities..."></textarea>
          </div>
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="addIsActive" checked>
            <label class="form-check-label" for="addIsActive">Active Status</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create Department</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
