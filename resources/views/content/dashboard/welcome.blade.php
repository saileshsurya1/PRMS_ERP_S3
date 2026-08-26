@extends('layouts/layoutMaster')
@section('title', 'Dashboard')
@section('content')
<div class="py-3">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <span class="text-primary fw-semibold text-uppercase small">PRMS Workspace</span>
      <h2 class="mt-1 mb-0">Welcome, {{ $user->name }}</h2>
      <p class="text-muted mb-0">{{ $today->format('l, d F Y') }}</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('todos.index') }}" class="btn btn-primary">
        <i class="mdi mdi-checkbox-marked-circle-outline me-1"></i> Open Task Register
      </a>
      @if($user->isAdmin() || $user->isOwner())
        <a href="{{ route('sales.dashboard') }}" class="btn btn-outline-primary">
          <i class="mdi mdi-chart-box-outline me-1"></i> Sales Review
        </a>
      @endif
    </div>
  </div>

  <div class="row g-4">
    {{-- Active Tasks & To-Dos --}}
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="mdi mdi-clipboard-list-outline me-1 text-primary"></i> Action Items & Tasks
          </h5>
          <a href="{{ route('todos.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body">
          @forelse($userTasks as $task)
            <div class="d-flex justify-content-between align-items-center border-bottom py-3">
              <div>
                <strong>{{ $task->title }}</strong>
                @if($task->description)
                  <small class="d-block text-muted">{{ Str::limit($task->description, 60) }}</small>
                @endif
                <div class="mt-1">
                  <span class="badge bg-label-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'secondary') }} me-1">
                    {{ ucfirst($task->priority) }}
                  </span>
                  <small class="text-muted">
                    Due: {{ $task->due_date ? $task->due_date->format('d M Y') : 'No date set' }}
                  </small>
                </div>
              </div>
              <form method="POST" action="{{ route('todos.toggle', $task) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-sm btn-outline-success" title="Mark Complete">
                  <i class="mdi mdi-check"></i>
                </button>
              </form>
            </div>
          @empty
            <div class="text-center py-4 text-muted">
              <i class="mdi mdi-check-all mdi-36px d-block mb-1 text-success"></i>
              No pending tasks. You're all caught up!
            </div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- RFQ Submissions & Due Dates --}}
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="mdi mdi-file-document-edit-outline me-1 text-info"></i> Quotation Deadlines
          </h5>
          <a href="{{ route('sales.rfqs') }}" class="btn btn-sm btn-outline-primary">Open RFQs</a>
        </div>
        <div class="card-body">
          @forelse($rfqTodos as $todo)
            <div class="d-flex justify-content-between align-items-center border-bottom py-3">
              <div>
                <strong>{{ $todo->rfq_number }}</strong>
                <span class="ms-2 badge bg-label-primary">{{ $todo->customer->company_name }}</span>
                <small class="d-block text-muted">
                  Submission Target: 
                  <span class="{{ $todo->quotation_submission_target_date && $todo->quotation_submission_target_date->isPast() ? 'text-danger fw-semibold' : '' }}">
                    {{ $todo->quotation_submission_target_date ? $todo->quotation_submission_target_date->format('d M Y') : 'Date not set' }}
                  </span>
                </small>
              </div>
              <a class="btn btn-sm btn-outline-primary" href="{{ route('sales.rfqs.show', $todo) }}">
                Details
              </a>
            </div>
          @empty
            <div class="text-center py-4 text-muted">
              <i class="mdi mdi-file-check-outline mdi-36px d-block mb-1 text-info"></i>
              No urgent quotation deadlines recorded.
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
