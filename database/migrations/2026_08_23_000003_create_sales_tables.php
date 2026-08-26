<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique();
            $table->string('company_name');
            $table->string('contact_person');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('industry')->nullable();
            $table->enum('customer_type', ['new', 'existing', 'qualified'])->default('new');
            $table->foreignId('assigned_sales_engineer_id')->constrained('users');
            $table->date('first_contact_date')->nullable();
            $table->date('last_contact_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'lost'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('sales_engineer_id')->constrained('users');
            $table->date('rfq_received_date');
            $table->string('rfq_number')->unique();
            $table->text('rfq_description');
            $table->decimal('quantity', 15, 3);
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->date('action_open_date')->nullable();
            $table->date('quotation_submission_target_date')->nullable();
            $table->enum('current_status', ['follow_up', 'follow_through', 'won', 'lost', 'cancelled'])->default('follow_up');
            $table->boolean('order_cancelled')->default(false);
            $table->decimal('order_cancelled_amount', 15, 2)->default(0);
            $table->text('order_cancel_reason')->nullable();
            $table->decimal('total_quoted_price', 15, 2)->default(0);
            $table->decimal('total_awarded_price', 15, 2)->default(0);
            $table->decimal('total_invoiced_price', 15, 2)->default(0);
            $table->decimal('advance_received', 15, 2)->default(0);
            $table->date('pending_amount_due_date')->nullable();
            $table->text('payment_pending_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained()->cascadeOnDelete();
            $table->string('quotation_number')->unique();
            $table->date('quotation_date');
            $table->date('quoted_date');
            $table->date('submission_target_date')->nullable();
            $table->date('actual_submitted_date')->nullable();
            $table->decimal('quoted_price', 15, 2);
            $table->decimal('awarded_price', 15, 2)->nullable();
            $table->enum('status', ['draft', 'submitted', 'under_review', 'won', 'lost', 'cancelled'])->default('draft');
            $table->boolean('commercial_accuracy')->default(true);
            $table->text('loss_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('daily_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_engineer_id')->constrained('users');
            $table->date('activity_date');
            $table->unsignedInteger('customer_calls')->default(0);
            $table->unsignedInteger('follow_up_calls')->default(0);
            $table->unsignedInteger('customer_visits')->default(0);
            $table->unsignedInteger('online_meetings')->default(0);
            $table->unsignedInteger('rfqs_received')->default(0);
            $table->unsignedInteger('quotations_submitted')->default(0);
            $table->boolean('crm_updated')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['sales_engineer_id', 'activity_date']);
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained()->cascadeOnDelete();
            $table->date('received_date');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('INR');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_engineer_id')->constrained('users');
            $table->date('reported_date');
            $table->string('subject');
            $table->text('description');
            $table->enum('status', ['open', 'in_progress', 'resolved'])->default('open');
            $table->text('resolution')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_records');
        Schema::dropIfExists('customer_complaints');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('daily_activity_logs');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('rfqs');
        Schema::dropIfExists('customers');
    }
};