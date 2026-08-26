<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','employee','user','sales_engineer','sales_manager_owner','owner','sales_manager','department_user') NOT NULL DEFAULT 'department_user'");
        DB::statement("UPDATE users SET role = 'owner' WHERE role = 'admin'");
        DB::statement("UPDATE users SET role = 'sales_manager' WHERE role = 'sales_manager_owner'");
        DB::statement("UPDATE users SET role = 'department_user' WHERE role = 'employee'");
        DB::statement("ALTER TABLE users MODIFY role ENUM('owner','sales_manager','sales_engineer','department_user') NOT NULL DEFAULT 'department_user'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','employee','user','sales_engineer','sales_manager_owner') NOT NULL DEFAULT 'user'");
    }
};