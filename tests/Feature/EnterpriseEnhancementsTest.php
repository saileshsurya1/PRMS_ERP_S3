<?php

namespace Tests\Feature;

use App\Models\ActivityRecord;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EnterpriseEnhancementsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_admin_gate_bypass_works(): void
    {
        $admin = User::where('role', 'owner')->first();
        $this->actingAs($admin);

        $this->assertTrue(Gate::allows('any-custom-ability-or-policy'));
    }

    public function test_department_crud_interface(): void
    {
        $admin = User::where('role', 'owner')->first();
        $this->actingAs($admin);

        $response = $this->get('/admin/departments');
        $response->assertStatus(200);
        $response->assertSee('Department Master');

        $storeResponse = $this->post('/admin/departments', [
            'name' => 'Logistics Department',
            'code' => 'LOG',
            'description' => 'Handles delivery and freight logistics',
            'is_active' => '1',
        ]);
        $storeResponse->assertRedirect('/admin/departments');
        $this->assertDatabaseHas('departments', ['name' => 'Logistics Department']);

        $department = Department::where('name', 'Logistics Department')->first();

        $updateResponse = $this->patch('/admin/departments/' . $department->id, [
            'name' => 'Global Logistics Department',
            'code' => 'GLOG',
            'description' => 'Handles international logistics',
            'is_active' => '1',
        ]);
        $updateResponse->assertRedirect('/admin/departments');
        $this->assertDatabaseHas('departments', ['name' => 'Global Logistics Department']);

        $deleteResponse = $this->delete('/admin/departments/' . $department->id);
        $deleteResponse->assertRedirect('/admin/departments');
        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }

    public function test_todo_crud_and_date_range_filters(): void
    {
        $admin = User::where('role', 'owner')->first();
        $this->actingAs($admin);

        $storeResponse = $this->post('/todos', [
            'title' => 'Feature Test Task',
            'description' => 'Test task description',
            'due_date' => '2026-09-15',
            'priority' => 'high',
            'status' => 'pending',
            'assigned_to_id' => $admin->id,
        ]);
        $storeResponse->assertRedirect('/todos');
        $this->assertDatabaseHas('todos', ['title' => 'Feature Test Task']);

        $todo = Todo::where('title', 'Feature Test Task')->first();

        $toggleResponse = $this->patch('/todos/' . $todo->id . '/toggle');
        $toggleResponse->assertRedirect();
        $this->assertEquals('completed', $todo->fresh()->status);

        $filterResponse = $this->get('/todos?startDate=2026-09-01&endDate=2026-09-30');
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Feature Test Task');

        $outOfRangeResponse = $this->get('/todos?startDate=2026-10-01&endDate=2026-10-31');
        $outOfRangeResponse->assertStatus(200);
        $outOfRangeResponse->assertDontSee('Feature Test Task');

        $deleteResponse = $this->delete('/todos/' . $todo->id);
        $deleteResponse->assertRedirect('/todos');
        $this->assertDatabaseMissing('todos', ['id' => $todo->id]);
    }

    public function test_profile_update_and_self_service_restrictions(): void
    {
        $engineer = User::where('role', 'sales_engineer')->first();
        $this->actingAs($engineer);

        $file = UploadedFile::fake()->image('engineer-avatar.png');

        $response = $this->patch('/profile', [
            'name' => 'Updated Engineer Name',
            'email' => $engineer->email,
            'role' => 'owner',
            'department' => 'Management',
            'photo' => $file,
        ]);

        $response->assertRedirect();

        $fresh = $engineer->fresh();
        $this->assertEquals('Updated Engineer Name', $fresh->name);
        $this->assertEquals('sales_engineer', $fresh->role);
        $this->assertEquals('Sales', $fresh->department);
        $this->assertNotNull($fresh->profile_photo_path);
        Storage::disk('public')->assertExists($fresh->profile_photo_path);
    }

    public function test_view_activity_logs_are_filtered_out(): void
    {
        $admin = User::where('role', 'owner')->first();
        $this->actingAs($admin);

        $customer = Customer::first();
        $initialAuditCount = ActivityRecord::count();

        $this->get('/customers/' . $customer->id);
        $this->assertEquals($initialAuditCount, ActivityRecord::count());

        $this->post('/todos', [
            'title' => 'Activity Test Task',
            'priority' => 'medium',
            'due_date' => now()->toDateString(),
        ]);

        $this->assertGreaterThan($initialAuditCount, ActivityRecord::count());
    }

    public function test_customer_to_engineer_mapping_and_photo(): void
    {
        $admin = User::where('role', 'owner')->first();
        $this->actingAs($admin);

        $engineer = User::where('role', 'sales_engineer')->first();
        $file = UploadedFile::fake()->image('company-logo.jpg');

        $response = $this->post('/customers', [
            'customer_code' => 'CUST-TEST-999',
            'company_name' => 'Test Tech Robotics',
            'contact_person' => 'Vikram Seth',
            'customer_type' => 'qualified',
            'sales_engineer_id' => $engineer->id,
            'status' => 'active',
            'portal_email' => 'vikram@robotics.test',
            'portal_password' => 'password123',
            'photo' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'customer_code' => 'CUST-TEST-999',
            'sales_engineer_id' => $engineer->id,
        ]);

        $customer = Customer::where('customer_code', 'CUST-TEST-999')->first();
        $this->assertEquals($engineer->id, $customer->salesEngineer->id);
        $this->assertNotNull($customer->photo);
        Storage::disk('public')->assertExists($customer->photo);
    }
}
