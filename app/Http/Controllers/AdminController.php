<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\MenuAccess;
use App\Models\MenuItem;
use App\Models\KpiTarget;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function users()
    {
        return view('content.admin.users', [
            'users' => User::with('customer')->orderBy('name')->paginate(15),
            'customers' => Customer::orderBy('company_name')->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:owner,sales_engineer,customer'],
            'status' => ['nullable', 'in:active,inactive'],
            'department' => ['nullable', 'string', 'max:120'],
            'monthly_target' => ['nullable', 'numeric', 'min:0'],
            'customer_id' => ['nullable', 'required_if:role,customer', 'exists:customers,id'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
        ]);

        $monthlyTarget = $data['monthly_target'] ?? null;
        unset($data['monthly_target']);
        if ($data['role'] !== 'customer') {
            unset($data['customer_id']);
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('profile-photos', 'public');
            $data['profile_photo_path'] = $path;
        }

        $data['password'] = Hash::make($data['password']);
        $data['status'] = $data['status'] ?? 'active';

        $user = User::create($data);

        if ($user->role === 'sales_engineer' && $monthlyTarget !== null) {
            KpiTarget::updateOrCreate(
                ['sales_engineer_id' => $user->id, 'kpi_code' => 'order_booking', 'period_type' => 'monthly', 'valid_from' => now()->startOfYear()->toDateString()],
                ['kpi_name' => 'Monthly order booking', 'target_value' => $monthlyTarget, 'target_unit' => 'currency', 'weight_percentage' => 100, 'created_by' => auth()->id()]
            );
        }

        $this->audit('created_user', User::class, $user->id);
        return back()->with('status', 'Account created successfully.');
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'in:owner,sales_engineer,customer'],
            'status' => ['required', 'in:active,inactive'],
            'department' => ['nullable', 'string', 'max:120'],
            'monthly_target' => ['nullable', 'numeric', 'min:0'],
            'customer_id' => ['nullable', 'required_if:role,customer', 'exists:customers,id'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
        ]);

        $monthlyTarget = $data['monthly_target'] ?? null;
        unset($data['monthly_target']);
        if ($data['role'] !== 'customer') {
            unset($data['customer_id']);
        }

        if ($request->hasFile('photo')) {
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('photo')->store('profile-photos', 'public');
            $data['profile_photo_path'] = $path;
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        if ($user->role === 'sales_engineer' && $monthlyTarget !== null) {
            KpiTarget::updateOrCreate(
                ['sales_engineer_id' => $user->id, 'kpi_code' => 'order_booking', 'period_type' => 'monthly', 'valid_from' => now()->startOfYear()->toDateString()],
                ['kpi_name' => 'Monthly order booking', 'target_value' => $monthlyTarget, 'target_unit' => 'currency', 'weight_percentage' => 100, 'created_by' => auth()->id()]
            );
        }

        $this->audit('updated_user', User::class, $user->id);
        return back()->with('status', 'User account updated successfully.');
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $this->audit('deleted_user', User::class, $user->id);
        if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }
        $user->delete();

        return back()->with('status', 'User deleted successfully.');
    }

    public function menus()
    {
        return view('content.admin.menus', [
            'menus' => MenuItem::with('accesses')->orderBy('sort_order')->get(),
            'users' => User::orderBy('name')->get()
        ]);
    }

    public function editMenu(MenuItem $menu)
    {
        return view('content.admin.menu-edit', compact('menu'));
    }

    public function updateMenu(Request $request, MenuItem $menu)
    {
        $menu->update($request->validate([
            'label' => ['required', 'string', 'max:120'],
            'route' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean']
        ]));

        $this->audit('updated_menu', MenuItem::class, $menu->id);
        return redirect()->route('admin.menus')->with('status', 'Menu updated.');
    }

    public function destroyMenu(MenuItem $menu)
    {
        $this->audit('deleted_menu', MenuItem::class, $menu->id);
        $menu->delete();
        return redirect()->route('admin.menus')->with('status', 'Menu deleted.');
    }

    public function access()
    {
        return view('content.admin.menu-access', [
            'menus' => MenuItem::where('is_active', true)->orderBy('sort_order')->get(),
            'users' => User::whereIn('role', ['sales_engineer', 'customer'])->orderBy('name')->get(),
            'accesses' => MenuAccess::where('subject_type', 'user')->get()->groupBy('subject_value')
        ]);
    }

    public function updateAccess(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'menus' => ['nullable', 'array'],
            'menus.*' => ['exists:menu_items,id']
        ]);

        MenuAccess::where('subject_type', 'user')->where('subject_value', (string) $data['user_id'])->delete();
        foreach ($data['menus'] ?? [] as $menuId) {
            MenuAccess::create([
                'menu_item_id' => $menuId,
                'subject_type' => 'user',
                'subject_value' => (string) $data['user_id']
            ]);
        }

        $this->audit('updated_menu_access', User::class, $data['user_id']);
        return back()->with('status', 'Employee menu access updated.');
    }

    public function storeMenu(Request $request)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'route' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0']
        ]);

        $menu = MenuItem::create($data + ['is_active' => true]);
        $this->audit('created_menu', MenuItem::class, $menu->id);
        return back()->with('status', 'Menu item created.');
    }

    public function storeAccess(Request $request, MenuItem $menu)
    {
        $data = $request->validate([
            'subject_type' => ['required', 'in:role,user,department'],
            'subject_value' => ['required', 'string', 'max:255']
        ]);

        MenuAccess::updateOrCreate(['menu_item_id' => $menu->id] + $data);
        $this->audit('granted_menu_access', MenuItem::class, $menu->id);
        return back()->with('status', 'Menu access updated.');
    }
}