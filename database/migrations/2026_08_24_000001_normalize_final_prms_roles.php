<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('owner','sales_manager','sales_engineer','department_user','admin','employee','user') NOT NULL DEFAULT 'user'");
        DB::table('users')->whereIn('role', ['admin', 'sales_manager', 'sales_manager_owner'])->update(['role' => 'owner']);
        DB::table('users')->whereIn('role', ['employee', 'department_user'])->update(['role' => 'user']);
        DB::statement("ALTER TABLE users MODIFY role ENUM('owner','sales_engineer','user') NOT NULL DEFAULT 'user'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('owner','sales_engineer','user','department_user','sales_manager') NOT NULL DEFAULT 'user'");
    }
};