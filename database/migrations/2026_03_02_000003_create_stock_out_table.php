<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_out', function (Blueprint $table) {
            $table->increments('Stock_Out_Id');
            $table->unsignedInteger('item_id');
            $table->foreign('item_id')->references('Item_Id')->on('inventory_items')->cascadeOnDelete();
            $table->integer('quantity');
            $table->integer('previous_stock')->default(0);
            $table->integer('new_stock')->default(0);
            $table->string('reference_number', 50)->nullable();
            $table->string('reason', 100)->nullable();
            $table->unsignedInteger('order_id')->nullable();
            $table->foreign('order_id')->references('Order_Id')->on('orders')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('User_Id')->on('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_out');
    }
};