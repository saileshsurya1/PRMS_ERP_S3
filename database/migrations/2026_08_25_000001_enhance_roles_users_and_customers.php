<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert any existing 'user' roles to 'sales_engineer' before altering enum
        DB::table('users')->where('role', 'user')->update(['role' => 'sales_engineer']);

        // Update role enum to only owner, sales_engineer, customer
        DB::statement("ALTER TABLE users MODIFY role ENUM('owner','sales_engineer','customer') NOT NULL DEFAULT 'sales_engineer'");

        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('role');
            }
            if (!Schema::hasColumn('users', 'profile_photo_path')) {
                $table->string('profile_photo_path', 2048)->nullable()->after('status');
            }
        });

        Schema::table('customers', function (Blueprint $table): void {
            if (!Schema::hasColumn('customers', 'sales_engineer_id')) {
                $table->foreignId('sales_engineer_id')->nullable()->after('assigned_sales_engineer_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('customers', 'photo')) {
                $table->string('photo', 2048)->nullable()->after('notes');
            }
        });

        // Sync existing assigned_sales_engineer_id to sales_engineer_id
        DB::statement("UPDATE customers SET sales_engineer_id = assigned_sales_engineer_id WHERE sales_engineer_id IS NULL AND assigned_sales_engineer_id IS NOT NULL");
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            if (Schema::hasColumn('customers', 'photo')) {
                $table->dropColumn('photo');
            }
            if (Schema::hasColumn('customers', 'sales_engineer_id')) {
                $table->dropConstrainedForeignId('sales_engineer_id');
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'profile_photo_path')) {
                $table->dropColumn('profile_photo_path');
            }
            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn('status');
            }
        });

        DB::statement("ALTER TABLE users MODIFY role ENUM('owner','sales_engineer','user','customer') NOT NULL DEFAULT 'user'");
    }
};
