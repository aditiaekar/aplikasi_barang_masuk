<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel items untuk menyimpan data barang.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            $table->foreignId('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            $table->string('item_code')->unique();
            $table->string('name');
            $table->string('barcode')->unique()->nullable();
            $table->unsignedInteger('minimum_stock')->default(0);
            $table->string('image')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel items.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};