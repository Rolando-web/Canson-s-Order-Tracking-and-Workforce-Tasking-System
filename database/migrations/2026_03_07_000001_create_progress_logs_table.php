<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progress_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('assignment_id')->nullable();
            $table->foreign('assignment_id')->references('Assignment_Id')->on('assignments')->nullOnDelete();
            $table->unsignedInteger('phase_item_id')->nullable();
            $table->foreign('phase_item_id')->references('Phase_Item_Id')->on('order_phase_items')->nullOnDelete();
            $table->unsignedInteger('order_item_id')->nullable();
            $table->foreign('order_item_id')->references('Order_Item_Id')->on('order_items')->nullOnDelete();
            $table->unsignedInteger('employee_id');
            $table->foreign('employee_id')->references('User_Id')->on('users')->cascadeOnDelete();
            $table->unsignedInteger('qty_added');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_logs');
    }
};
