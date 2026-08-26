@extends('layouts/layoutMaster')
@section('title', 'Edit System Configuration Menu')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1">Edit Navigation Menu Item</h4>
    <p class="text-muted mb-0">Update route, label, icon, and display status.</p>
  </div>
  <a class="btn btn-outline-primary" href="{{ route('admin.menus') }}">Back to System Configuration</a>
</div>

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('admin.menus.update', $menu) }}">
      @csrf
      @method('PATCH')
      <div class="mb-3">
        <label class="form-label">Menu Label</label>
        <input class="form-control" name="label" value="{{ old('label', $menu->label) }}" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Route Name / URL</label>
        <input class="form-control" name="route" value="{{ old('route', $menu->route) }}" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Icon Class</label>
        <input class="form-control" name="icon" value="{{ old('icon', $menu->icon) }}">
      </div>
      <div class="mb-3">
        <label class="form-label">Sort Order</label>
        <input class="form-control" type="number" name="sort_order" value="{{ old('sort_order', $menu->sort_order) }}" min="0">
      </div>
      <div class="mb-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="is_active">
          <option value="1" @selected($menu->is_active)>Active</option>
          <option value="0" @selected(!$menu->is_active)>Inactive</option>
        </select>
      </div>
      <button class="btn btn-primary">Save Changes</button>
    </form>
  </div>
</div>
@endsection
