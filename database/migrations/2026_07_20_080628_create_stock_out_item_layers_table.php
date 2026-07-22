<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_out_item_layers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_out_item_id')
                ->constrained('stock_out_items')
                ->restrictOnDelete();
            $table->foreignId('stock_layer_id')
                ->constrained('stock_layers')
                ->restrictOnDelete();
            $table->foreignId('stock_in_item_id')
                ->constrained('stock_in_items')
                ->restrictOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_out_item_layers');
    }
};
