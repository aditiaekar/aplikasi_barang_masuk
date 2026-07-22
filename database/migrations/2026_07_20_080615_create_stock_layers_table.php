<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_layers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')
                ->constrained('items')
                ->restrictOnDelete();
            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();
            $table->foreignId('stock_in_item_id')
                ->constrained('stock_in_items')
                ->cascadeOnDelete();
            $table->unsignedInteger('quantity_in')->default(0);
            $table->unsignedInteger('quantity_remaining')->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_layers');
    }
};
