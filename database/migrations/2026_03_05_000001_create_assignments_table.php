<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->increments('Assignment_Id');
            $table->string('order_number', 20);
            $table->unsignedInteger('phase_item_id')->nullable();
            $table->foreign('phase_item_id')->references('Phase_Item_Id')->on('order_phase_items')->nullOnDelete();
            $table->unsignedInteger('phase_id')->nullable();
            $table->foreign('phase_id')->references('Phase_Id')->on('order_phases')->nullOnDelete();
            $table->unsignedInteger('employee_id');
            $table->foreign('employee_id')->references('User_Id')->on('users')->cascadeOnDelete();
            $table->enum('priority', ['normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->unsignedInteger('assigned_by')->nullable();
            $table->foreign('assigned_by')->references('User_Id')->on('users')->nullOnDelete();
            $table->date('assigned_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};