<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TodoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Todo::with(['creator', 'assignedUser'])->latest('due_date');

        // Non-admins see tasks created by them or assigned to them
        if (!$user->isAdmin() && !$user->isOwner()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('assigned_to_id', $user->id);
            });
        }

        // Date Range Filters
        if ($request->filled('startDate')) {
            $query->whereDate('due_date', '>=', Carbon::parse($request->input('startDate'))->toDateString());
        }
        if ($request->filled('endDate')) {
            $query->whereDate('due_date', '<=', Carbon::parse($request->input('endDate'))->toDateString());
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->string('priority'));
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $todos = $query->paginate(15)->withQueryString();
        $users = User::where('status', 'active')->orderBy('name')->get();

        return view('content.todos.index', compact('todos', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'status' => ['nullable', Rule::in(['pending', 'in_progress', 'completed'])],
            'assigned_to_id' => ['nullable', 'exists:users,id'],
        ]);

        $data['user_id'] = $request->user()->id;
        $data['status'] = $data['status'] ?? 'pending';
        $data['assigned_to_id'] = $data['assigned_to_id'] ?? $request->user()->id;

        $todo = Todo::create($data);
        $this->audit('created_todo', Todo::class, $todo->id);

        return redirect()->route('todos.index')->with('status', 'Task added successfully.');
    }

    public function update(Request $request, Todo $todo)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            abort_unless($todo->user_id === $user->id || $todo->assigned_to_id === $user->id, 403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
            'assigned_to_id' => ['nullable', 'exists:users,id'],
        ]);

        $todo->update($data);
        $this->audit('updated_todo', Todo::class, $todo->id);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'todo' => $todo]);
        }

        return redirect()->route('todos.index')->with('status', 'Task updated successfully.');
    }

    public function toggle(Request $request, Todo $todo)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            abort_unless($todo->user_id === $user->id || $todo->assigned_to_id === $user->id, 403);
        }

        $newStatus = $todo->status === 'completed' ? 'pending' : 'completed';
        $todo->update(['status' => $newStatus]);
        $this->audit('toggled_todo_status', Todo::class, $todo->id, ['status' => $newStatus]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'status' => $newStatus]);
        }

        return back()->with('status', 'Task marked as ' . $newStatus . '.');
    }

    public function destroy(Todo $todo)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort_unless($todo->user_id === $user->id || $todo->assigned_to_id === $user->id, 403);
        }

        $this->audit('deleted_todo', Todo::class, $todo->id);
        $todo->delete();

        return redirect()->route('todos.index')->with('status', 'Task deleted successfully.');
    }
}
