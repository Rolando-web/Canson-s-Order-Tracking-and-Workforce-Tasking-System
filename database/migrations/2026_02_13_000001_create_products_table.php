<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('Product_Id');
            $table->string('name');
            $table->string('item_code')->unique();
            $table->string('category');
            $table->integer('stock')->default(0);
            $table->string('unit', 50)->nullable();
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->enum('status', ['In Stock', 'Low Stock', 'Out of Stock'])->default('In Stock');
            $table->boolean('is_best_seller')->default(false);
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};