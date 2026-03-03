<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('order_phase_items', function (Blueprint $table) {
            $table->increments('Phase_Item_Id');
            $table->unsignedInteger('phase_id');
            $table->foreign('phase_id')->references('Phase_Id')->on('order_phases')->cascadeOnDelete();
            $table->unsignedInteger('inventory_item_id')->nullable();
            $table->foreign('inventory_item_id')->references('Item_Id')->on('inventory_items')->nullOnDelete();
            $table->string('name', 255);
            $table->unsignedInteger('base_qty')->default(0);       // qty as set by manager
            $table->unsignedInteger('damage_carry')->default(0);   // carried over from prev phase damage
            $table->unsignedInteger('required_qty')->default(0);   // = base_qty + damage_carry
            $table->unsignedInteger('completed_qty')->default(0);  // updated by workers
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('order_phase_items'); }
};