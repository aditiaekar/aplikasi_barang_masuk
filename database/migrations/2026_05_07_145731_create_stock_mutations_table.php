<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel stock_mutations untuk menyimpan riwayat perubahan stok.
     */
    public function up(): void
    {
        Schema::create('stock_mutations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_id')
                ->constrained('items')
                ->restrictOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->foreignId('stock_in_id')
                ->nullable()
                ->constrained('stock_ins')
                ->nullOnDelete();

            $table->enum('mutation_type', [
                'in',
            ])->default('in');

            $table->unsignedInteger('quantity');
            $table->unsignedInteger('stock_before')->default(0);
            $table->unsignedInteger('stock_after')->default(0);

            $table->dateTime('mutation_date');

            $table->text('description')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            $table->index('mutation_date');
            $table->index(['item_id', 'warehouse_id']);
            $table->index('mutation_type');
        });
    }

    /**
     * Menghapus tabel stock_mutations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_mutations');
    }
};