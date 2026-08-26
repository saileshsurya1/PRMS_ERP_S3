<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_engineer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kpi_code');
            $table->string('kpi_name');
            $table->enum('period_type', ['daily', 'weekly', 'monthly']);
            $table->decimal('target_value', 15, 3);
            $table->enum('target_unit', ['currency', 'count', 'percentage', 'hours', 'boolean']);
            $table->decimal('weight_percentage', 5, 2)->default(0);
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->index(['kpi_code', 'period_type']);
        });

        Schema::create('kpi_actuals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_engineer_id')->constrained('users');
            $table->foreignId('kpi_target_id')->constrained('kpi_targets');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('actual_value', 15, 3)->default(0);
            $table->decimal('achievement_percentage', 7, 2)->default(0);
            $table->decimal('weighted_score', 7, 2)->default(0);
            $table->enum('calculation_status', ['calculated', 'approved', 'locked'])->default('calculated');
            $table->timestamp('calculated_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['sales_engineer_id', 'kpi_target_id', 'period_start', 'period_end'], 'kpi_actual_unique');
        });

        Schema::create('incentives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_engineer_id')->constrained('users');
            $table->date('period_month');
            $table->decimal('achievement_percentage', 7, 2);
            $table->enum('slab', ['no_incentive', 'standard', 'one_point_five_x', 'two_x_recognition']);
            $table->decimal('base_incentive_amount', 15, 2)->default(0);
            $table->decimal('multiplier', 4, 2)->default(0);
            $table->decimal('final_incentive_amount', 15, 2)->default(0);
            $table->enum('manager_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('manager_comments')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->unique(['sales_engineer_id', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incentives');
        Schema::dropIfExists('kpi_actuals');
        Schema::dropIfExists('kpi_targets');
    }
};