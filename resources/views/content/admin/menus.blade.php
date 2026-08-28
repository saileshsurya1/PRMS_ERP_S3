@extends('layouts/layoutMaster')
@section('title', 'System Configuration')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1">System Configuration</h4>
    <p class="text-muted mb-0">Manage navigation structure, menu items, routes, and role access permissions.</p>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-primary" href="{{ route('admin.menu-access') }}">Employee Menu Access</a>
    <a class="btn btn-outline-primary" href="{{ route('admin.departments.index') }}">Departments</a>
    <a class="btn btn-outline-primary" href="{{ route('admin.users') }}">Manage Users</a>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header">
    <h5 class="mb-0">Create Navigation Menu Item</h5>
  </div>
  <div class="card-body">
    <p class="text-muted">Create a system menu item, specify its route name (e.g. <code>customers.index</code>, <code>todos.index</code>, <code>sales.rfqs</code>), then configure permissions.</p>
    <form class="row g-3" method="POST" action="{{ route('admin.menus.store') }}">
      @csrf
      <div class="col-md-3">
        <label class="form-label">Menu Label</label>
        <input class="form-control" name="label" placeholder="e.g. Tasks & To-Do" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Route Name / URL</label>
        <input class="form-control" name="route" placeholder="e.g. todos.index" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Icon Class</label>
        <input class="form-control" name="icon" placeholder="menu-icon tf-icons mdi mdi-menu">
      </div>
      <div class="col-md-1">
        <label class="form-label">Sort</label>
        <input class="form-control" type="number" name="sort_order" value="10">
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100">Add Item</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Configured Navigation Items</h5>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Menu Item</th>
          <th>Route / URL</th>
          <th>Status</th>
          <th>Access Granted</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($menus as $menu)
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                @if($menu->icon)
                  <i class="{{ $menu->icon }} text-primary"></i>
                @endif
                <strong>{{ $menu->label }}</strong>
              </div>
            </td>
            <td><code>{{ $menu->route }}</code></td>
            <td>
              <span class="badge bg-label-{{ $menu->is_active ? 'success' : 'secondary' }}">
                {{ $menu->is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td>
              @forelse($menu->accesses as $access)
                <span class="badge bg-label-secondary me-1">{{ $access->subject_type }}: {{ $access->subject_value }}</span>
              @empty
                <span class="text-muted small">No specific rules</span>
              @endforelse
            </td>
            <td class="text-end">
              <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit Menu Item">
                  <i class="mdi mdi-pencil-outline"></i>
                </a>
                <button type="button" class="btn btn-sm btn-icon btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteMenuModal{{ $menu->id }}" title="Delete Menu Item">
                  <i class="mdi mdi-trash-can-outline"></i>
                </button>
              </div>

              {{-- Delete Confirmation Modal --}}
              <div class="modal fade text-start" id="deleteMenuModal{{ $menu->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}">
                      @csrf
                      @method('DELETE')
                      <div class="modal-header">
                        <h5 class="modal-title text-danger"><i class="mdi mdi-alert-circle-outline me-1"></i> Delete Menu Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <p class="mb-0">Are you sure you want to delete navigation item <strong>{{ $menu->label }}</strong> (<code>{{ $menu->route }}</code>)?</p>
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
        @endforeach
      </tbody>
    </table>
  </div>
  @include('partials.pagination', ['paginator' => $menus])
</div>
@endsection
