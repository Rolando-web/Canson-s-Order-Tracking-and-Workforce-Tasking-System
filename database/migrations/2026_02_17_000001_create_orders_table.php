<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('Order_Id');
            $table->string('order_number', 20)->unique();
            $table->string('customer_name', 100);
            $table->string('contact_number', 11);
            $table->text('delivery_address');
            $table->date('delivery_date');
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->string('status', 50)->default('Pending');
            $table->enum('priority', ['Normal', 'High', 'Urgent'])->default('Normal');
            $table->string('assigned', 100)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('User_Id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};