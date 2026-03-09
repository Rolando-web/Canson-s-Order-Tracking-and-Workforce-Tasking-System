<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->increments('Order_Item_Id');
            $table->unsignedInteger('order_id');
            $table->foreign('order_id')->references('Order_Id')->on('orders')->cascadeOnDelete();
            $table->unsignedInteger('product_id');
            $table->foreign('product_id')->references('Product_Id')->on('products')->cascadeOnDelete();
            $table->string('name');
            $table->integer('quantity');
            $table->integer('completed_qty')->default(0);
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};