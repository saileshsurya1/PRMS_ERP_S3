<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('owner','sales_engineer','user','customer') NOT NULL DEFAULT 'user'");
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('customer_id')->nullable()->after('role')->constrained('customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('customer_id');
        });
        DB::statement("ALTER TABLE users MODIFY role ENUM('owner','sales_engineer','user') NOT NULL DEFAULT 'user'");
    }
};
