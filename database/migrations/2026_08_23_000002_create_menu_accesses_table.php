<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->enum('subject_type', ['role', 'user', 'department']);
            $table->string('subject_value');
            $table->timestamps();
            $table->unique(['menu_item_id', 'subject_type', 'subject_value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_accesses');
    }
};