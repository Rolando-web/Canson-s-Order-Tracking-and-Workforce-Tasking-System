<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_id', 20)->unique();                // e.g. DC-2026-0001
            $table->foreignId('item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->integer('quantity');
            $table->string('reason', 255);
            $table->enum('status', ['Pending', 'Covered'])->default('Pending');
            $table->string('customer_name', 100);
            $table->string('order_reference', 50)->nullable();        // original order that had damage
            $table->string('covered_by_order', 50)->nullable();       // order that includes the replacement
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
