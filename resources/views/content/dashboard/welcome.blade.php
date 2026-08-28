@extends('layouts/layoutMaster')
@section('title', 'Dashboard')
@section('content')
<div class="py-3">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <span class="text-primary fw-semibold text-uppercase small">PRMS Workspace</span>
      <h2 class="mt-1 mb-0">{{ $greeting ?? 'Welcome' }}, {{ $user->first_name ?: $user->name }} 👋</h2>
      <div class="d-flex flex-wrap align-items-center gap-3 mt-2 text-muted">
        <span><i class="mdi mdi-calendar-blank-outline me-1"></i>{{ $today->format('l, d F Y') }}</span>
        <span class="badge bg-label-primary px-3 py-1 font-monospace" style="font-size: 0.95rem;">
          <i class="mdi mdi-clock-outline me-1"></i><span id="dashboard-live-clock">{{ $currentTime ?? $today->format('h:i:s A') }}</span>
        </span>
      </div>
    </div>
    <div class="d-flex gap-2 mt-3 mt-md-0">
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
        @include('partials.pagination', ['paginator' => $userTasks, 'pageName' => 'tasks_page', 'perPageParam' => 'tasks_per_page'])
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
        @include('partials.pagination', ['paginator' => $rfqTodos, 'pageName' => 'rfqs_page', 'perPageParam' => 'rfqs_per_page'])
      </div>
    </div>
  </div>
</div>
@endsection

@push('page-script')
<script>
  (function() {
    function updateLiveClock() {
      const now = new Date();
      let hours = now.getHours();
      const minutes = String(now.getMinutes()).padStart(2, '0');
      const seconds = String(now.getSeconds()).padStart(2, '0');
      const ampm = hours >= 12 ? 'PM' : 'AM';
      hours = hours % 12;
      hours = hours ? String(hours).padStart(2, '0') : '12';
      const timeStr = `${hours}:${minutes}:${seconds} ${ampm}`;
      const clockEl = document.getElementById('dashboard-live-clock');
      if (clockEl) {
        clockEl.textContent = timeStr;
      }
    }
    setInterval(updateLiveClock, 1000);
    updateLiveClock();
  })();
</script>
@endpush
