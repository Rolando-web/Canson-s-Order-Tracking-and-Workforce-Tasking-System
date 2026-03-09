<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('order_phases', function (Blueprint $table) {
            $table->increments('Phase_Id');
            $table->unsignedInteger('order_id');
            $table->foreign('order_id')->references('Order_Id')->on('orders')->cascadeOnDelete();
            $table->unsignedTinyInteger('phase_number');
            $table->date('delivery_date');
            $table->string('status', 50)->default('Pending');
            $table->unsignedInteger('damage_qty')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('order_phases'); }
};