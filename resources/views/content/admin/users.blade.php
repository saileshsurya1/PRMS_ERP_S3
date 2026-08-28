@extends('layouts/layoutMaster')
@section('title', 'User & Employee Management')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1">User & Employee Management</h4>
    <p class="text-muted mb-0">Create employee accounts, manage roles, departments, personal details, and account status.</p>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-primary" href="{{ route('admin.departments.index') }}">Departments</a>
    <a class="btn btn-outline-primary" href="{{ route('admin.menus') }}">System Configuration</a>
    <a class="btn btn-outline-primary" href="{{ route('admin.menu-access') }}">Menu Access</a>
  </div>
</div>

@if(session('status'))
  <div class="alert alert-success alert-dismissible mb-4" role="alert">
    <div class="d-flex align-items-center gap-2">
      <i class="mdi mdi-check-circle-outline fs-5"></i>
      <div>{{ session('status') }}</div>
    </div>
  </div>
@endif

@if($errors->any())
  <div class="alert alert-danger alert-dismissible mb-4" role="alert">
    <div class="d-flex align-items-center gap-2">
      <i class="mdi mdi-alert-circle-outline fs-5"></i>
      <div>
        <ul class="mb-0 ps-3">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
@endif

<div class="row g-4">
  {{-- Create Account Card --}}
  <div class="col-xl-4">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Create Employee Account</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
          @csrf

          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label">First Name</label>
              <input class="form-control" name="first_name" value="{{ old('first_name') }}" placeholder="John" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Last Name</label>
              <input class="form-control" name="last_name" value="{{ old('last_name') }}" placeholder="Doe" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input class="form-control" type="email" name="email" value="{{ old('email') }}" placeholder="name@company.com" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Phone Number</label>
            <input class="form-control" type="text" name="phone" value="{{ old('phone') }}" placeholder="+91 98765 43210">
          </div>

          <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea class="form-control" name="address" rows="2" placeholder="Full residential/office address">{{ old('address') }}</textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password" placeholder="Temporary password (min 8 chars)" required>
          </div>

          <div class="mb-3">
            <label class="form-label">System Role</label>
            <select class="form-select" name="role" id="user-role" required>
              <option value="sales_engineer" @selected(old('role', 'sales_engineer') === 'sales_engineer')>Sales Engineer</option>
              <option value="owner" @selected(old('role') === 'owner')>Owner / Admin</option>
              <option value="customer" @selected(old('role') === 'customer')>Customer Portal Login</option>
            </select>
          </div>

          <div class="mb-3 d-none" id="customer-account-wrapper">
            <label class="form-label">Link to Customer Account</label>
            <select class="form-select" name="customer_id" id="customer-account">
              <option value="">Select customer</option>
              @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->company_name }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3" id="department-wrapper">
            <label class="form-label">Department</label>
            <select class="form-select" name="department">
              <option value="">Select Department</option>
              @foreach($departments as $dept)
                <option value="{{ $dept->name }}" @selected(old('department') === $dept->name)>{{ $dept->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3" id="status-wrapper">
            <label class="form-label">Account Status</label>
            <select class="form-select" name="status">
              <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
              <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
            </select>
          </div>

          <div id="target-field" class="mb-3">
            <label class="form-label" for="monthly-target">Monthly Order-Booking Target (₹)</label>
            <input class="form-control" type="number" min="0" step="0.01" name="monthly_target" id="monthly-target" value="{{ old('monthly_target') }}" placeholder="e.g. 10000000">
            <small class="text-muted">Used for KPI and sales incentive calculations.</small>
          </div>

          <div class="mb-3">
            <label class="form-label">Profile Photo (optional)</label>
            <input type="file" class="form-control" name="photo" accept="image/*">
          </div>

          <button type="submit" class="btn btn-primary w-100">Create Account</button>
        </form>
      </div>
    </div>
  </div>

  {{-- Users Table with Actions Column --}}
  <div class="col-xl-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">User & Employee Directory</h5>
      </div>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>User</th>
              <th>Phone</th>
              <th>Role</th>
              <th>Department</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($users as $user)
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="rounded-circle" width="36" height="36" style="object-fit: cover;">
                    <div>
                      <strong>{{ $user->name }}</strong>
                      <small class="d-block text-muted">{{ $user->email }}</small>
                    </div>
                  </div>
                </td>
                <td>{{ $user->phone ?: '-' }}</td>
                <td>
                  <span class="badge bg-label-{{ $user->isAdmin() ? 'danger' : ($user->isSalesEngineer() ? 'primary' : 'info') }}">
                    {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                  </span>
                </td>
                <td>{{ $user->department ?: 'General' }}</td>
                <td>
                  <span class="badge bg-label-{{ ($user->status === 'active' && ($user->active ?? true)) ? 'success' : 'secondary' }}">
                    {{ ucfirst($user->status ?? 'active') }}
                  </span>
                </td>
                <td class="text-end">
                  <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-icon btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}" title="Edit User">
                      <i class="mdi mdi-pencil-outline"></i>
                    </button>
                    @if($user->id !== auth()->id())
                      <button type="button" class="btn btn-sm btn-icon btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal{{ $user->id }}" title="Delete User">
                        <i class="mdi mdi-trash-can-outline"></i>
                      </button>
                    @endif
                  </div>

                  {{-- Edit User Modal --}}
                  <div class="modal fade text-start" id="editUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
                          @csrf
                          @method('PATCH')
                          <div class="modal-header">
                            <h5 class="modal-title">Edit User Account: {{ $user->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <div class="row g-2 mb-3">
                              <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name" value="{{ old('first_name', $user->first_name) }}" placeholder="John">
                              </div>
                              <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" value="{{ old('last_name', $user->last_name) }}" placeholder="Doe">
                              </div>
                            </div>

                            <div class="mb-3">
                              <label class="form-label">Email Address</label>
                              <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
                            </div>

                            <div class="mb-3">
                              <label class="form-label">Phone Number</label>
                              <input type="text" class="form-control" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+91 98765 43210">
                            </div>

                            <div class="mb-3">
                              <label class="form-label">Address</label>
                              <textarea class="form-control" name="address" rows="2" placeholder="Full address">{{ old('address', $user->address) }}</textarea>
                            </div>

                            <div class="mb-3">
                              <label class="form-label">System Role</label>
                              <select class="form-select edit-user-role" name="role" data-target="{{ $user->id }}" required>
                                <option value="sales_engineer" @selected($user->role === 'sales_engineer')>Sales Engineer</option>
                                <option value="owner" @selected($user->role === 'owner')>Owner / Admin</option>
                                <option value="customer" @selected($user->role === 'customer')>Customer</option>
                              </select>
                            </div>

                            <div class="mb-3">
                              <label class="form-label">Department</label>
                              <select class="form-select" name="department">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                  <option value="{{ $dept->name }}" @selected($user->department === $dept->name)>{{ $dept->name }}</option>
                                @endforeach
                              </select>
                            </div>

                            <div class="mb-3">
                              <label class="form-label">Status (Admin Control)</label>
                              <select class="form-select" name="status">
                                <option value="active" @selected(($user->status ?? 'active') === 'active')>Active</option>
                                <option value="inactive" @selected(($user->status ?? 'active') === 'inactive')>Inactive</option>
                              </select>
                            </div>

                            @if($user->role === 'sales_engineer')
                              <div class="mb-3">
                                <label class="form-label">Monthly Target (₹)</label>
                                <input type="number" class="form-control" name="monthly_target" value="{{ \App\Models\KpiTarget::where('sales_engineer_id', $user->id)->where('kpi_code', 'order_booking')->latest('valid_from')->value('target_value') }}">
                              </div>
                            @endif

                            <div class="mb-3">
                              <label class="form-label">Change Profile Photo</label>
                              <input type="file" class="form-control" name="photo" accept="image/*">
                            </div>

                            <div class="mb-3">
                              <label class="form-label">Reset Password (leave blank to keep current)</label>
                              <input type="password" class="form-control" name="password" placeholder="New password (min 8 characters)">
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

                  {{-- Delete User Confirmation Modal --}}
                  @if($user->id !== auth()->id())
                    <div class="modal fade text-start" id="deleteUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                          <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                            @csrf
                            @method('DELETE')
                            <div class="modal-header">
                              <h5 class="modal-title text-danger"><i class="mdi mdi-alert-circle-outline me-1"></i> Delete User</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              <p class="mb-0">Are you sure you want to delete user account <strong>{{ $user->name }} ({{ $user->email }})</strong>?</p>
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
                <td colspan="6" class="text-center text-muted py-4">No users found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @include('partials.pagination', ['paginator' => $users])
    </div>
  </div>
</div>
@endsection

@push('page-script')
<script>
  const roleSelect = document.getElementById('user-role');
  const targetWrapper = document.getElementById('target-field');
  const customerWrapper = document.getElementById('customer-account-wrapper');

  const toggleCreateFields = () => {
    if (roleSelect) {
      targetWrapper.classList.toggle('d-none', roleSelect.value !== 'sales_engineer');
      customerWrapper.classList.toggle('d-none', roleSelect.value !== 'customer');
    }
  };

  if (roleSelect) {
    roleSelect.addEventListener('change', toggleCreateFields);
    toggleCreateFields();
  }
</script>
@endpush
