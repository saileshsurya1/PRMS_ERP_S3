<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $departments = Department::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('content.admin.departments', compact('departments'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:departments,name'],
            'code' => ['nullable', 'string', 'max:20', 'unique:departments,code'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : true;

        $department = Department::create($data);
        $this->audit('created_department', Department::class, $department->id);

        return redirect()->route('admin.departments.index')->with('status', 'Department created successfully.');
    }

    public function update(Request $request, Department $department)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('departments')->ignore($department->id)],
            'code' => ['nullable', 'string', 'max:20', Rule::unique('departments')->ignore($department->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['required', 'boolean'],
        ]);

        $department->update($data);
        $this->audit('updated_department', Department::class, $department->id);

        return redirect()->route('admin.departments.index')->with('status', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $this->audit('deleted_department', Department::class, $department->id);
        $department->delete();

        return redirect()->route('admin.departments.index')->with('status', 'Department deleted successfully.');
    }
}
