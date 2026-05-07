<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel stock_in_items untuk menyimpan detail realisasi barang masuk.
     */
    public function up(): void
    {
        Schema::create('stock_in_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_in_id')
                ->constrained('stock_ins')
                ->cascadeOnDelete();

            $table->foreignId('item_id')
                ->constrained('items')
                ->restrictOnDelete();

            $table->foreignId('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            $table->unsignedInteger('quantity');
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index('item_id');
        });
    }

    /**
     * Menghapus tabel stock_in_items.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_in_items');
    }
};