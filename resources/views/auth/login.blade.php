@php($customizerHidden = 'customizer-hide')
@extends('layouts/layoutMaster')

@section('title', 'Sign in')
@section('content')
<div class="authentication-wrapper authentication-basic container-p-y">
  <div class="authentication-inner py-4">
    <div class="card p-2">
      <div class="app-brand justify-content-center mt-5">
        <span class="app-brand-text demo text-heading fw-bold">{{ config('variables.templateName') }}</span>
      </div>
      <div class="card-body mt-2">
        <h4 class="mb-2">Welcome back</h4>
        <p class="mb-4">Sign in to continue to your workspace.</p>
        @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
        <form method="POST" action="{{ route('login.store') }}">
          @csrf
          <div class="form-floating form-floating-outline mb-3"><input class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus><label>Email</label></div>
          <div class="form-floating form-floating-outline mb-3"><input class="form-control" type="password" name="password" required><label>Password</label></div>
          <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember" id="remember"><label class="form-check-label" for="remember">Remember me</label></div>
          <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection