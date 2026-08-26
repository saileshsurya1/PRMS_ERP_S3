<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['owner', 'sales_manager', 'sales_engineer', 'department_user'])->default('department_user')->after('email');
            });
        } else {
            DB::statement("ALTER TABLE users MODIFY role ENUM('owner','sales_manager','sales_engineer','department_user') NOT NULL DEFAULT 'department_user'");
        }
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'department')) $table->string('department')->nullable()->after('role');
            if (!Schema::hasColumn('users', 'employee_code')) $table->string('employee_code')->nullable()->unique()->after('department');
            if (!Schema::hasColumn('users', 'active')) $table->boolean('active')->default(true)->after('employee_code');
            if (!Schema::hasColumn('users', 'joined_date')) $table->date('joined_date')->nullable()->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['employee_code']);
            $table->dropColumn(['role', 'department', 'employee_code', 'active', 'joined_date']);
        });
    }
};
