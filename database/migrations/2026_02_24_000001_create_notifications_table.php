<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');          // who receives this notification
            $table->string('type', 50);                     // e.g. new_order, work_assigned, delivery_assigned
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();               // extra payload (order_id, assignment_id, etc.)
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
