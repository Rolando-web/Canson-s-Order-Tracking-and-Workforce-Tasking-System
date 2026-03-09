<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->increments('Return_Id');
            $table->string('return_number', 20)->unique();
            $table->unsignedInteger('product_id');
            $table->foreign('product_id')->references('Product_Id')->on('products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->string('reason', 255);
            $table->enum('status', ['Pending', 'Covered'])->default('Pending');
            $table->string('customer_name', 100);
            $table->string('order_reference', 50)->nullable();
            $table->string('covered_by_order', 50)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('User_Id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};