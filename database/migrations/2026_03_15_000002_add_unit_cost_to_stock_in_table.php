<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_in', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->default(0)->after('new_stock');
        });
    }

    public function down(): void
    {
        Schema::table('stock_in', function (Blueprint $table) {
            $table->dropColumn('unit_cost');
        });
    }
};
