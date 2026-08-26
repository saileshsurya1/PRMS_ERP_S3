@extends('layouts/layoutMaster')
@section('title', 'User management')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><div><h4 class="mb-1">User management</h4><p class="text-muted mb-0">Create accounts and assign their workspace role.</p></div><a class="btn btn-outline-primary" href="{{ route('admin.menus') }}">Manage menus</a></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
<div class="row g-4"><div class="col-xl-5"><div class="card"><div class="card-header"><h5 class="mb-0">Create account</h5></div><div class="card-body"><form method="POST" action="{{ route('admin.users.store') }}">@csrf
<input class="form-control mb-3" name="name" placeholder="Full name" required><input class="form-control mb-3" type="email" name="email" placeholder="Email" required><input class="form-control mb-3" type="password" name="password" placeholder="Temporary password" required>
<select class="form-select mb-3" name="role" required><option value="user">User</option><option value="employee">Employee</option><option value="admin">Admin</option></select><input class="form-control mb-3" name="department" placeholder="Department (optional)"><button class="btn btn-primary w-100">Create account</button></form></div></div></div>
<div class="col-xl-7"><div class="card"><div class="table-responsive"><table class="table"><thead><tr><th>Name</th><th>Role</th><th>Department</th></tr></thead><tbody>@foreach($users as $user)<tr><td>{{ $user->name }}<small class="d-block text-muted">{{ $user->email }}</small></td><td><span class="badge bg-label-primary">{{ ucfirst($user->role) }}</span></td><td>{{ $user->department ?: 'All departments' }}</td></tr>@endforeach</tbody></table></div></div></div></div>
@endsection