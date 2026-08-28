@php
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Register - PRMS Workspace')

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-auth.css')}}">
@endsection

@section('content')
<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-4">

      <!-- Register Card -->
      <div class="card p-2">
        <!-- Logo -->
        <div class="app-brand justify-content-center mt-4">
          <a href="{{url('/')}}" class="app-brand-link gap-2">
            <span class="app-brand-logo demo">@include('_partials.macros',["width"=>25,"withbg"=>'var(--bs-primary)'])</span>
            <span class="app-brand-text demo text-heading fw-bold">{{config('variables.templateName')}}</span>
          </a>
        </div>
        <!-- /Logo -->

        <div class="card-body mt-2">
          <h4 class="mb-1 text-center">Create an Account 🚀</h4>
          <p class="text-muted mb-3 text-center">Join PRMS Workspace</p>

          @if ($errors->any())
            <div class="alert alert-danger alert-dismissible mb-3" role="alert">
              <div class="d-flex align-items-center gap-2">
                <i class="mdi mdi-alert-circle-outline fs-5"></i>
                <div>
                  <strong>Registration Error</strong>
                  <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              </div>
            </div>
          @endif

          <form id="formAuthentication" class="mb-3" action="{{ route('register.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-2 mb-3">
              <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name') }}" placeholder="John" required autofocus>
                  <label for="first_name">First Name</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name') }}" placeholder="Doe" required>
                  <label for="last_name">Last Name</label>
                </div>
              </div>
            </div>

            <div class="form-floating form-floating-outline mb-3">
              <select class="form-select" id="role" name="role" required>
                <option value="sales_engineer" @selected(old('role', 'sales_engineer') === 'sales_engineer')>Sales Engineer</option>
                <option value="customer" @selected(old('role') === 'customer')>Customer</option>
                <option value="owner" @selected(old('role') === 'owner')>Owner (Admin)</option>
              </select>
              <label for="role">Account Role</label>
            </div>

            <div class="form-floating form-floating-outline mb-3">
              <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="name@company.com" required>
              <label for="email">Email Address</label>
            </div>

            <div class="form-floating form-floating-outline mb-3">
              <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+91 98765 43210">
              <label for="phone">Phone Number</label>
            </div>

            <div class="form-floating form-floating-outline mb-3">
              <textarea class="form-control" id="address" name="address" placeholder="Address" style="height: 65px;">{{ old('address') }}</textarea>
              <label for="address">Address</label>
            </div>

            <div class="mb-3">
              <label class="form-label text-muted small" for="photo">Profile Photo (optional)</label>
              <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
            </div>

            <div class="row g-2 mb-3">
              <div class="col-md-6 form-password-toggle">
                <div class="input-group input-group-merge">
                  <div class="form-floating form-floating-outline">
                    <input type="password" id="password" class="form-control" name="password" placeholder="••••••••" required />
                    <label for="password">Password</label>
                  </div>
                  <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
                </div>
              </div>
              <div class="col-md-6 form-password-toggle">
                <div class="input-group input-group-merge">
                  <div class="form-floating form-floating-outline">
                    <input type="password" id="password_confirmation" class="form-control" name="password_confirmation" placeholder="••••••••" required />
                    <label for="password_confirmation">Confirm Password</label>
                  </div>
                  <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
                </div>
              </div>
            </div>

            <button type="submit" class="btn btn-primary d-grid w-100 mt-3">
              Sign Up
            </button>
          </form>

          <p class="text-center mt-3 mb-0">
            <span>Already have an account?</span>
            <a href="{{ route('login') }}">
              <span>Sign in instead</span>
            </a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
