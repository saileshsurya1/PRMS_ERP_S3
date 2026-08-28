@php
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Register - PRMS Workspace')

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-auth.css')}}">
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover">
  <!-- Logo -->
  <a href="{{url('/')}}" class="auth-cover-brand d-flex align-items-center gap-2">
    <span class="app-brand-logo demo">@include('_partials.macros',["width"=>25,"withbg"=>'var(--bs-primary)'])</span>
    <span class="app-brand-text demo text-heading fw-bold">{{config('variables.templateName')}}</span>
  </a>
  <!-- /Logo -->
  <div class="authentication-inner row m-0">

    <!-- Left Illustration -->
    <div class="d-none d-lg-flex col-lg-6 col-xl-7 align-items-center justify-content-center p-5 pb-2">
      <img src="{{asset('assets/img/illustrations/auth-register-illustration-'.$configData['style'].'.png') }}" class="auth-cover-illustration w-100" alt="auth-illustration" data-app-light-img="illustrations/auth-register-illustration-light.png" data-app-dark-img="illustrations/auth-register-illustration-dark.png" />
      <img src="{{asset('assets/img/illustrations/auth-cover-register-mask-'.$configData['style'].'.png') }}" class="authentication-image" alt="mask" data-app-light-img="illustrations/auth-cover-register-mask-light.png" data-app-dark-img="illustrations/auth-cover-register-mask-dark.png" />
    </div>
    <!-- /Left Illustration -->

    <!-- Register Form -->
    <div class="d-flex col-12 col-lg-6 col-xl-5 align-items-center authentication-bg position-relative py-sm-5 px-4 py-4">
      <div class="w-100 mx-auto" style="max-width: 480px;">
        <h4 class="mb-1">Create an Account 🚀</h4>
        <p class="text-muted mb-3">Join PRMS Workspace to manage sales pipelines and projects.</p>

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
                <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name') }}" placeholder="John" required autofocus>
                <label for="first_name">First Name</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name') }}" placeholder="Doe" required>
                <label for="last_name">Last Name</label>
              </div>
            </div>
          </div>

          <div class="form-floating form-floating-outline mb-3">
            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
              <option value="sales_engineer" @selected(old('role', 'sales_engineer') === 'sales_engineer')>Sales Engineer</option>
              <option value="customer" @selected(old('role') === 'customer')>Customer</option>
              <option value="owner" @selected(old('role') === 'owner')>Owner (Admin)</option>
            </select>
            <label for="role">Account Role</label>
          </div>

          <div class="form-floating form-floating-outline mb-3">
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="name@company.com" required>
            <label for="email">Email Address</label>
          </div>

          <div class="form-floating form-floating-outline mb-3">
            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+91 98765 43210">
            <label for="phone">Phone Number</label>
          </div>

          <div class="form-floating form-floating-outline mb-3">
            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" placeholder="Address" style="height: 65px;">{{ old('address') }}</textarea>
            <label for="address">Address</label>
          </div>

          <div class="mb-3">
            <label class="form-label text-muted small" for="photo">Profile Photo (optional)</label>
            <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*">
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6 form-password-toggle">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
                  <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="••••••••" required />
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
    <!-- /Register Form -->
  </div>
</div>
@endsection
