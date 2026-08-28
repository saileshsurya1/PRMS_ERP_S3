@extends('layouts/layoutMaster')
@section('title', 'Menu Access Control')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-1">Menu & Access Control</h4>
    <p class="text-muted mb-0 small">Owner-managed permissions: configure which system menus each Role and User can access.</p>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.menus') }}">
      <i class="mdi mdi-cog-outline me-1"></i> System Configuration
    </a>
    <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.users') }}">
      <i class="mdi mdi-account-cog-outline me-1"></i> Manage Users
    </a>
  </div>
</div>

@if(session('status'))
  <div class="alert alert-success alert-dismissible py-2 mb-3" role="alert">
    <div class="d-flex align-items-center gap-2">
      <i class="mdi mdi-check-circle-outline fs-5"></i>
      <div class="small">{{ session('status') }}</div>
    </div>
  </div>
@endif

<div class="nav-align-top">
  <ul class="nav nav-tabs nav-fill" role="tablist">
    <li class="nav-item">
      <button type="button" class="nav-link active py-2" role="tab" data-bs-toggle="tab" data-bs-target="#navs-role-access" aria-controls="navs-role-access" aria-selected="true" style="font-size: 0.875rem;">
        <i class="mdi mdi-shield-account-outline me-1"></i> 1. Role-Based Menu Access
      </button>
    </li>
    <li class="nav-item">
      <button type="button" class="nav-link py-2" role="tab" data-bs-toggle="tab" data-bs-target="#navs-user-access" aria-controls="navs-user-access" aria-selected="false" style="font-size: 0.875rem;">
        <i class="mdi mdi-account-key-outline me-1"></i> 2. User-Specific Overrides
      </button>
    </li>
  </ul>

  <div class="tab-content border-0 p-0 pt-3">
    {{-- Tab 1: Role-Based Menu Access --}}
    <div class="tab-pane fade show active" id="navs-role-access" role="tabpanel">
      <div class="row g-3">
        @foreach($roles as $role)
          @php
            $currentRoleMenuIds = ($roleAccesses[$role] ?? collect())->pluck('menu_item_id')->toArray();
          @endphp
          <div class="col-lg-6">
            <div class="card h-100 shadow-sm border">
              <div class="card-header py-2 px-3 border-bottom d-flex justify-content-between align-items-center bg-light">
                <div>
                  <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">{{ ucfirst(str_replace('_', ' ', $role)) }} Role</h6>
                  <span class="text-muted" style="font-size: 0.75rem;">Default accessible menus for {{ str_replace('_', ' ', $role) }}s</span>
                </div>
                <span class="badge bg-label-{{ $role === 'sales_engineer' ? 'primary' : 'info' }} px-2 py-1" style="font-size: 0.75rem;">
                  {{ count($currentRoleMenuIds) }} Active
                </span>
              </div>
              <div class="card-body p-3">
                <form method="POST" action="{{ route('admin.menu-access.role.update') }}">
                  @csrf
                  <input type="hidden" name="role" value="{{ $role }}">

                  <div class="row g-2 mb-3">
                    @foreach($menus as $menu)
                      <div class="col-md-6">
                        <div class="menu-checkbox-card border rounded p-2.5 bg-white d-flex align-items-center gap-2 h-100">
                          <input class="form-check-input m-0 flex-shrink-0 cursor-pointer" type="checkbox" name="menus[]" value="{{ $menu->id }}" id="role_{{ $role }}_menu_{{ $menu->id }}" @checked(in_array($menu->id, $currentRoleMenuIds))>
                          <label class="form-check-label mb-0 flex-grow-1 cursor-pointer overflow-hidden ps-1" for="role_{{ $role }}_menu_{{ $menu->id }}">
                            <span class="fw-semibold d-block text-truncate text-dark" style="font-size: 0.825rem;">{{ $menu->label }}</span>
                            <span class="text-muted d-block text-truncate font-monospace" style="font-size: 0.7rem;">{{ $menu->route }}</span>
                          </label>
                        </div>
                      </div>
                    @endforeach
                  </div>

                  <div class="text-end">
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                      <i class="mdi mdi-content-save-outline me-1"></i> Save {{ ucfirst(str_replace('_', ' ', $role)) }} Access
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Tab 2: User-Specific Overrides --}}
    <div class="tab-pane fade" id="navs-user-access" role="tabpanel">
      <div class="card shadow-sm border">
        <div class="card-header py-2 px-3 border-bottom bg-light">
          <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">User-Specific Permission Overrides</h6>
          <span class="text-muted" style="font-size: 0.75rem;">Select an employee or customer to grant or revoke specific menu items individually.</span>
        </div>
        <div class="card-body p-3">
          <form method="POST" action="{{ route('admin.menu-access.update') }}">
            @csrf
            <div class="row g-3 align-items-center mb-3">
              <div class="col-md-6">
                <label class="form-label fw-bold small mb-1" for="employee-select">Select User / Employee</label>
                <select class="form-select form-select-sm" name="user_id" id="employee-select" required>
                  <option value="">-- Choose User to Configure --</option>
                  @foreach($users as $user)
                    <option value="{{ $user->id }}" data-role="{{ $user->role }}">
                      {{ $user->name }} ({{ $user->email }}) — [{{ ucfirst(str_replace('_', ' ', $user->role)) }}]
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6 d-flex justify-content-md-end gap-2 pt-md-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-select-all" style="font-size: 0.75rem;">Select All</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-deselect-all" style="font-size: 0.75rem;">Deselect All</button>
              </div>
            </div>

            <div class="row g-2 mb-3">
              @foreach($menus as $menu)
                <div class="col-md-4 col-sm-6">
                  <div class="menu-checkbox-card border rounded p-2.5 bg-white d-flex align-items-center gap-2 h-100">
                    <input class="form-check-input user-menu-check m-0 flex-shrink-0 cursor-pointer" type="checkbox" name="menus[]" value="{{ $menu->id }}" id="user_menu_{{ $menu->id }}">
                    <label class="form-check-label mb-0 flex-grow-1 cursor-pointer overflow-hidden ps-1" for="user_menu_{{ $menu->id }}">
                      <span class="fw-semibold d-block text-truncate text-dark" style="font-size: 0.825rem;">{{ $menu->label }}</span>
                      <span class="text-muted d-block text-truncate font-monospace" style="font-size: 0.7rem;">{{ $menu->route }}</span>
                    </label>
                  </div>
                </div>
              @endforeach
            </div>

            <div class="text-end">
              <button type="submit" class="btn btn-primary btn-sm px-3" id="btn-save-user-access" disabled>
                <i class="mdi mdi-content-save-outline me-1"></i> Save User Menu Permissions
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .menu-checkbox-card {
    border-color: #e4e6eb !important;
    background-color: #ffffff !important;
    transition: all 0.15s ease-in-out;
  }
  .menu-checkbox-card:hover {
    border-color: #666cff !important;
    background-color: #f7f8ff !important;
  }
  .menu-checkbox-card .form-check-input {
    position: static !important;
    float: none !important;
    margin: 0 !important;
    width: 1.15rem;
    height: 1.15rem;
  }
</style>
@endsection

@push('page-script')
<script>
  const userAccessMap = @json($userAccesses->map(fn($items) => $items->pluck('menu_item_id')->values())->toArray());
  const roleAccessMap = @json($roleAccesses->map(fn($items) => $items->pluck('menu_item_id')->values())->toArray());

  const userSelect = document.getElementById('employee-select');
  const userChecks = [...document.querySelectorAll('.user-menu-check')];
  const saveBtn = document.getElementById('btn-save-user-access');
  const btnSelectAll = document.getElementById('btn-select-all');
  const btnDeselectAll = document.getElementById('btn-deselect-all');

  if (userSelect) {
    userSelect.addEventListener('change', () => {
      if (!userSelect.value) {
        saveBtn.disabled = true;
        userChecks.forEach(c => c.checked = false);
        return;
      }

      saveBtn.disabled = false;
      const selectedOption = userSelect.options[userSelect.selectedIndex];
      const userRole = selectedOption.getAttribute('data-role');

      // If user has specific overrides, load them; otherwise load role defaults
      const hasSpecific = userAccessMap.hasOwnProperty(userSelect.value);
      const allowed = hasSpecific ? (userAccessMap[userSelect.value] || []) : (roleAccessMap[userRole] || []);

      userChecks.forEach(check => {
        check.checked = allowed.includes(Number(check.value));
      });
    });
  }

  if (btnSelectAll) {
    btnSelectAll.addEventListener('click', () => {
      userChecks.forEach(c => c.checked = true);
    });
  }

  if (btnDeselectAll) {
    btnDeselectAll.addEventListener('click', () => {
      userChecks.forEach(c => c.checked = false);
    });
  }
</script>
@endpush
