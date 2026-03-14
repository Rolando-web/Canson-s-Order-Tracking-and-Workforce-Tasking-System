<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_in', function (Blueprint $table) {
            $table->increments('Stock_In_Id');
            $table->unsignedInteger('product_id');
            $table->foreign('product_id')->references('Product_Id')->on('products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->integer('previous_stock')->default(0);
            $table->integer('new_stock')->default(0);
            $table->string('reference_number', 50)->nullable();
            $table->unsignedInteger('supplier_id')->nullable();
            $table->foreign('supplier_id')->references('Supplier_Id')->on('suppliers')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('User_Id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_in');
    }
};