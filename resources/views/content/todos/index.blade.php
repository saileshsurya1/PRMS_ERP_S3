@extends('layouts/layoutMaster')
@section('title', 'Tasks & To-Do List')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1">Tasks & To-Do Register</h4>
    <p class="text-muted mb-0">Organize follow-up tasks, schedule milestones, and track deadlines.</p>
  </div>
  <div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
      <i class="mdi mdi-plus me-1"></i> Add Task
    </button>
  </div>
</div>

{{-- Date Range & Filter Card --}}
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('todos.index') }}" class="row g-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label">Start Date</label>
        <input type="date" class="form-control" name="startDate" value="{{ request('startDate') }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">End Date</label>
        <input type="date" class="form-control" name="endDate" value="{{ request('endDate') }}">
      </div>
      <div class="col-md-2">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
          <option value="">All Statuses</option>
          <option value="pending" @selected(request('status') === 'pending')>Pending</option>
          <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
          <option value="completed" @selected(request('status') === 'completed')>Completed</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Priority</label>
        <select class="form-select" name="priority">
          <option value="">All Priorities</option>
          <option value="low" @selected(request('priority') === 'low')>Low</option>
          <option value="medium" @selected(request('priority') === 'medium')>Medium</option>
          <option value="high" @selected(request('priority') === 'high')>High</option>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary w-100">
          <i class="mdi mdi-filter-outline me-1"></i> Filter
        </button>
        @if(request()->hasAny(['startDate', 'endDate', 'status', 'priority', 'search']))
          <a href="{{ route('todos.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
            <i class="mdi mdi-refresh"></i>
          </a>
        @endif
      </div>
    </form>
  </div>
</div>

{{-- Tasks Table --}}
<div class="card">
  <div class="table-responsive text-nowrap">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th style="width: 40px;">Done</th>
          <th>Task Title</th>
          <th>Assignee</th>
          <th>Due Date</th>
          <th>Priority</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($todos as $todo)
          <tr class="{{ $todo->status === 'completed' ? 'table-light text-muted' : '' }}">
            <td>
              <form method="POST" action="{{ route('todos.toggle', $todo) }}">
                @csrf
                @method('PATCH')
                <input class="form-check-input" type="checkbox" onchange="this.form.submit()" @checked($todo->status === 'completed') title="Toggle Complete">
              </form>
            </td>
            <td>
              <div class="{{ $todo->status === 'completed' ? 'text-decoration-line-through' : '' }}">
                <strong class="d-block">{{ $todo->title }}</strong>
                @if($todo->description)
                  <small class="text-muted text-wrap d-block" style="max-width: 320px;">{{ $todo->description }}</small>
                @endif
              </div>
            </td>
            <td>
              @if($todo->assignedUser)
                <div class="d-flex align-items-center gap-1">
                  <img src="{{ $todo->assignedUser->profile_photo_url }}" width="24" height="24" class="rounded-circle" style="object-fit: cover;">
                  <span>{{ $todo->assignedUser->name }}</span>
                </div>
              @else
                <span class="text-muted">Unassigned</span>
              @endif
            </td>
            <td>
              @if($todo->due_date)
                @php
                  $isOverdue = $todo->due_date->isPast() && $todo->status !== 'completed';
                @endphp
                <span class="{{ $isOverdue ? 'text-danger fw-semibold' : '' }}">
                  {{ $todo->due_date->format('d M Y') }}
                  @if($isOverdue) <i class="mdi mdi-alert-circle-outline" title="Overdue"></i> @endif
                </span>
              @else
                <span class="text-muted">No date</span>
              @endif
            </td>
            <td>
              <span class="badge bg-label-{{ $todo->priority === 'high' ? 'danger' : ($todo->priority === 'medium' ? 'warning' : 'secondary') }}">
                {{ ucfirst($todo->priority) }}
              </span>
            </td>
            <td>
              <form method="POST" action="{{ route('todos.update', $todo) }}" class="d-inline">
                @csrf
                @method('PATCH')
                <input type="hidden" name="title" value="{{ $todo->title }}">
                <input type="hidden" name="priority" value="{{ $todo->priority }}">
                <input type="hidden" name="due_date" value="{{ $todo->due_date?->toDateString() }}">
                <input type="hidden" name="assigned_to_id" value="{{ $todo->assigned_to_id }}">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                  <option value="pending" @selected($todo->status === 'pending')>Pending</option>
                  <option value="in_progress" @selected($todo->status === 'in_progress')>In Progress</option>
                  <option value="completed" @selected($todo->status === 'completed')>Completed</option>
                </select>
              </form>
            </td>
            <td class="text-end">
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-sm btn-icon btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTaskModal{{ $todo->id }}" title="Edit Task">
                  <i class="mdi mdi-pencil-outline"></i>
                </button>
                <button type="button" class="btn btn-sm btn-icon btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteTaskModal{{ $todo->id }}" title="Delete Task">
                  <i class="mdi mdi-trash-can-outline"></i>
                </button>
              </div>

              {{-- Edit Task Modal --}}
              <div class="modal fade text-start" id="editTaskModal{{ $todo->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <form method="POST" action="{{ route('todos.update', $todo) }}">
                      @csrf
                      @method('PATCH')
                      <div class="modal-header">
                        <h5 class="modal-title">Edit Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <div class="mb-3">
                          <label class="form-label">Task Title</label>
                          <input type="text" class="form-control" name="title" value="{{ old('title', $todo->title) }}" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Description</label>
                          <textarea class="form-control" name="description" rows="3">{{ old('description', $todo->description) }}</textarea>
                        </div>
                        <div class="row g-3 mb-3">
                          <div class="col-md-6">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date" value="{{ old('due_date', $todo->due_date?->toDateString()) }}">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label">Assignee</label>
                            <select class="form-select" name="assigned_to_id">
                              <option value="">Unassigned</option>
                              @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected($todo->assigned_to_id == $user->id)>{{ $user->name }}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="row g-3">
                          <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <select class="form-select" name="priority">
                              <option value="low" @selected($todo->priority === 'low')>Low</option>
                              <option value="medium" @selected($todo->priority === 'medium')>Medium</option>
                              <option value="high" @selected($todo->priority === 'high')>High</option>
                            </select>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                              <option value="pending" @selected($todo->status === 'pending')>Pending</option>
                              <option value="in_progress" @selected($todo->status === 'in_progress')>In Progress</option>
                              <option value="completed" @selected($todo->status === 'completed')>Completed</option>
                            </select>
                          </div>
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

              {{-- Delete Task Modal --}}
              <div class="modal fade text-start" id="deleteTaskModal{{ $todo->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <form method="POST" action="{{ route('todos.destroy', $todo) }}">
                      @csrf
                      @method('DELETE')
                      <div class="modal-header">
                        <h5 class="modal-title text-danger"><i class="mdi mdi-alert-circle-outline me-1"></i> Delete Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <p class="mb-0">Are you sure you want to delete task <strong>{{ $todo->title }}</strong>?</p>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Delete</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center text-muted py-4">No tasks found. Click "Add Task" to create one.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @include('partials.pagination', ['paginator' => $todos])
</div>

{{-- Add Task Modal --}}
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('todos.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Create New Task</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Task Title</label>
            <input type="text" class="form-control" name="title" placeholder="e.g. Follow up on quotation" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="3" placeholder="Actionable details..."></textarea>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Due Date</label>
              <input type="date" class="form-control" name="due_date" value="{{ now()->toDateString() }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Assignee</label>
              <select class="form-select" name="assigned_to_id">
                @foreach($users as $user)
                  <option value="{{ $user->id }}" @selected(auth()->id() === $user->id)>{{ $user->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Priority</label>
              <select class="form-select" name="priority">
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select class="form-select" name="status">
                <option value="pending" selected>Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create Task</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
