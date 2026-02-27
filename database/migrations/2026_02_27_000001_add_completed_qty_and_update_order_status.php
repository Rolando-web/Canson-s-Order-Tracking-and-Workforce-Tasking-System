<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add completed_qty to track employee progress on each order item
        Schema::table('order_items', function (Blueprint $table) {
            $table->integer('completed_qty')->default(0)->after('quantity');
        });

        // Change order status column to support more statuses
        // Drop the enum constraint and use a string instead
        DB::statement("ALTER TABLE orders MODIFY COLUMN status VARCHAR(50) DEFAULT 'Pending'");
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('completed_qty');
        });

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('Pending', 'In-Progress', 'Completed') DEFAULT 'Pending'");
    }
};
