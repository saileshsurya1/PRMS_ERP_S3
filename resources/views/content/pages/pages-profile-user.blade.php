@extends('layouts/layoutMaster')
@section('title', 'My Profile')
@section('content')
<div class="mb-4">
  <h4>My Profile</h4>
  <p class="text-muted">Manage your personal details, credentials, profile photo, and security.</p>
</div>

@if(session('status'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('status') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if($errors->any())
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

<div class="row g-4">
  {{-- Profile Header / Overview --}}
  <div class="col-lg-4">
    <div class="card mb-4 text-center">
      <div class="card-body">
        <div class="mx-auto mb-3" style="width: 120px; height: 120px;">
          <img src="{{ $profileUser->profile_photo_url }}" alt="{{ $profileUser->name }}" class="rounded-circle img-fluid w-100 h-100 shadow-sm" style="object-fit: cover; border: 3px solid #696cff;">
        </div>
        <h5 class="mb-1">{{ $profileUser->name }}</h5>
        <p class="text-muted mb-2">{{ $profileUser->email }}</p>
        <div class="d-flex justify-content-center gap-2 mb-3">
          <span class="badge bg-label-primary">{{ ucfirst(str_replace('_', ' ', $profileUser->role)) }}</span>
          <span class="badge bg-label-{{ $profileUser->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($profileUser->status ?? 'active') }}</span>
        </div>
        <hr class="my-3">
        <div class="text-start">
          <div class="mb-2"><strong>Phone:</strong> <span class="text-muted">{{ $profileUser->phone ?: 'Not set' }}</span></div>
          <div class="mb-2"><strong>Department:</strong> <span class="text-muted">{{ $profileUser->department ?: 'General' }}</span></div>
          <div class="mb-2"><strong>Joined:</strong> <span class="text-muted">{{ $profileUser->created_at?->format('d M Y') }}</span></div>
          @if($profileUser->customer)
            <div><strong>Company:</strong> <span class="text-muted">{{ $profileUser->customer->company_name }}</span></div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Edit Profile Form --}}
  <div class="col-lg-8">
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Update Profile Details</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
          @csrf
          @method('PATCH')

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label" for="first_name">First Name</label>
              <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $profileUser->first_name) }}" placeholder="John">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="last_name">Last Name</label>
              <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name', $profileUser->last_name) }}" placeholder="Doe">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label" for="email">Email Address</label>
              <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $profileUser->email) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="phone">Phone Number</label>
              <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $profileUser->phone) }}" placeholder="+91 98765 43210">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="address">Address</label>
            <textarea class="form-control" id="address" name="address" rows="2" placeholder="Full address">{{ old('address', $profileUser->address) }}</textarea>
          </div>

          <div class="mb-3">
            <label class="form-label" for="photo">Profile Photo</label>
            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
            <small class="text-muted">Allowed JPG, GIF, PNG, WebP. Max size of 2MB.</small>
          </div>

          @if(auth()->user()->isAdmin() || auth()->user()->isOwner())
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label" for="role">Role (Admin only)</label>
                <select class="form-select" id="role" name="role">
                  <option value="owner" @selected($profileUser->role === 'owner')>Owner / Admin</option>
                  <option value="sales_engineer" @selected($profileUser->role === 'sales_engineer')>Sales Engineer</option>
                  <option value="customer" @selected($profileUser->role === 'customer')>Customer</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="department">Department (Admin only)</label>
                <input type="text" class="form-control" id="department" name="department" value="{{ old('department', $profileUser->department) }}">
              </div>
            </div>
          @else
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label">Role</label>
                <input type="text" class="form-control bg-light" value="{{ ucfirst(str_replace('_', ' ', $profileUser->role)) }}" readonly disabled>
                <small class="text-muted">Role cannot be self-modified.</small>
              </div>
              <div class="col-md-6">
                <label class="form-label">Department</label>
                <input type="text" class="form-control bg-light" value="{{ $profileUser->department ?: 'General' }}" readonly disabled>
                <small class="text-muted">Department cannot be self-modified.</small>
              </div>
            </div>
          @endif

          <hr class="my-4">
          <h6 class="mb-3">Change Password (optional)</h6>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label" for="password">New Password</label>
              <input type="password" class="form-control" id="password" name="password" placeholder="••••••••">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="password_confirmation">Confirm New Password</label>
              <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="••••••••">
            </div>
          </div>

          <div class="text-end">
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>

    {{-- Activity Trail --}}
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Recent Activity Trail</h5>
      </div>
      <div class="list-group list-group-flush">
        @forelse($activities as $activity)
          <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <strong>{{ ucfirst(str_replace('_', ' ', $activity->action)) }}</strong>
              <small class="d-block text-muted">{{ $activity->created_at->format('d M Y H:i:s') }}</small>
            </div>
            <span class="badge bg-label-secondary">{{ class_basename($activity->subject_type) ?: 'System' }}</span>
          </div>
        @empty
          <div class="list-group-item text-muted text-center py-3">No activity recorded yet.</div>
        @endforelse
      </div>
      @if($activities->hasPages())
        <div class="card-footer">
          {{ $activities->links() }}
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
