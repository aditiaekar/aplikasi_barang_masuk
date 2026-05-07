<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel stock_ins untuk menyimpan realisasi barang masuk.
     */
    public function up(): void
    {
        Schema::create('stock_ins', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_in_request_id')
                ->unique()
                ->constrained('stock_in_requests')
                ->restrictOnDelete();

            $table->string('stock_in_number')->unique();

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->foreignId('received_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('stock_in_date');
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index('stock_in_date');
            $table->index(['supplier_id', 'warehouse_id']);
        });
    }

    /**
     * Menghapus tabel stock_ins.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ins');
    }
};