<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed standard departments
        $departments = [
            ['name' => 'Sales', 'code' => 'SALES', 'description' => 'Direct and channel sales division'],
            ['name' => 'Engineering', 'code' => 'ENG', 'description' => 'Technical design and engineering'],
            ['name' => 'Finance', 'code' => 'FIN', 'description' => 'Accounts, billing and commercial finance'],
            ['name' => 'Operations', 'code' => 'OPS', 'description' => 'Manufacturing and supply chain operations'],
            ['name' => 'Quality Assurance', 'code' => 'QA', 'description' => 'Quality control and testing'],
            ['name' => 'Management', 'code' => 'MGMT', 'description' => 'Executive and administration management'],
        ];

        foreach ($departments as $dept) {
            \App\Models\Department::updateOrCreate(['name' => $dept['name']], $dept + ['is_active' => true]);
        }

        $admin = \App\Models\User::updateOrCreate(['email' => 'owner@prms.test'], [
            'name' => 'PRMS Owner',
            'role' => 'owner',
            'status' => 'active',
            'department' => 'Management',
            'password' => Hash::make('password'),
        ]);

        $admin->notifications()->where('type', \App\Notifications\PrmsNotification::class)->delete();
        $admin->notify(new \App\Notifications\PrmsNotification('RFQ follow-up due', 'Review open RFQs and update the next action today.', 'mdi mdi-file-document-edit-outline', 'warning', route('sales.rfqs')));
        $admin->notify(new \App\Notifications\PrmsNotification('Quotation review', 'Check quotations waiting for review before they pass seven days.', 'mdi mdi-file-chart-outline', 'info', route('sales.quotations')));
        $admin->notify(new \App\Notifications\PrmsNotification('Collection watch', 'Review outstanding invoice collections and upcoming due dates.', 'mdi mdi-cash-alert', 'danger', route('sales.rfqs')));

        // Seed System Menu items
        $menuItems = [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'menu-icon tf-icons mdi mdi-view-dashboard-outline', 'sort_order' => 1],
            ['label' => 'Tasks & To-Do', 'route' => 'todos.index', 'icon' => 'menu-icon tf-icons mdi mdi-checkbox-marked-circle-outline', 'sort_order' => 2],
            ['label' => 'Owner sales review', 'route' => 'sales.dashboard', 'icon' => 'menu-icon tf-icons mdi mdi-chart-box-outline', 'sort_order' => 3],
            ['label' => 'Sales engineer KPIs', 'route' => 'sales.kpis', 'icon' => 'menu-icon tf-icons mdi mdi-chart-line', 'sort_order' => 4],
            ['label' => 'RFQ register', 'route' => 'sales.rfqs', 'icon' => 'menu-icon tf-icons mdi mdi-file-document-edit-outline', 'sort_order' => 5],
            ['label' => 'Quotations', 'route' => 'sales.quotations', 'icon' => 'menu-icon tf-icons mdi mdi-file-chart-outline', 'sort_order' => 6],
            ['label' => 'Daily KPI log', 'route' => 'sales.daily-log', 'icon' => 'menu-icon tf-icons mdi mdi-calendar-check-outline', 'sort_order' => 7],
            ['label' => 'Customers', 'route' => 'customers.index', 'icon' => 'menu-icon tf-icons mdi mdi-account-tie-outline', 'sort_order' => 8],
            ['label' => 'Customer complaints', 'route' => 'sales.complaints.index', 'icon' => 'menu-icon tf-icons mdi mdi-alert-circle-outline', 'sort_order' => 9],
            ['label' => 'Employees', 'route' => 'admin.users', 'icon' => 'menu-icon tf-icons mdi mdi-account-group-outline', 'sort_order' => 90],
            ['label' => 'Departments', 'route' => 'admin.departments.index', 'icon' => 'menu-icon tf-icons mdi mdi-domain', 'sort_order' => 91],
            ['label' => 'System Configuration', 'route' => 'admin.menus', 'icon' => 'menu-icon tf-icons mdi mdi-cog-outline', 'sort_order' => 92],
            ['label' => 'Employee menu access', 'route' => 'admin.menu-access', 'icon' => 'menu-icon tf-icons mdi mdi-account-key-outline', 'sort_order' => 93],
        ];

        // Clean up legacy 'Master menus' label if present
        \App\Models\MenuItem::where('route', 'admin.menus')->update(['label' => 'System Configuration', 'icon' => 'menu-icon tf-icons mdi mdi-cog-outline']);

        foreach ($menuItems as $data) {
            $menu = \App\Models\MenuItem::updateOrCreate(['route' => $data['route']], $data + ['is_active' => true]);

            foreach (['owner', 'sales_engineer', 'customer'] as $role) {
                if (in_array($data['route'], ['sales.dashboard', 'sales.kpis', 'sales.rfqs', 'sales.quotations', 'sales.daily-log', 'customers.index', 'sales.complaints.index'], true) && !in_array($role, ['owner', 'sales_engineer'], true)) {
                    continue;
                }
                if (in_array($data['route'], ['admin.users', 'admin.departments.index', 'admin.menus', 'admin.menu-access'], true) && $role !== 'owner') {
                    continue;
                }
                if ($data['route'] === 'customer.dashboard' && $role !== 'customer') {
                    continue;
                }

                \App\Models\MenuAccess::updateOrCreate([
                    'menu_item_id' => $menu->id,
                    'subject_type' => 'role',
                    'subject_value' => $role,
                ]);
            }
        }
    }
}