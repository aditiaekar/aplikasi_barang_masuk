<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel stock_in_requests untuk menyimpan pengajuan barang masuk.
     */
    public function up(): void
    {
        Schema::create('stock_in_requests', function (Blueprint $table) {
            $table->id();

            $table->string('request_number')->unique();

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->foreignId('requested_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->date('request_date');

            $table->enum('status', [
                'draft',
                'pending',
                'approved',
                'rejected',
            ])->default('draft');

            $table->text('note')->nullable();

            $table->timestamp('submitted_at')->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();
            $table->text('rejected_reason')->nullable();

            $table->timestamps();

            $table->index('request_date');
            $table->index('status');
            $table->index(['supplier_id', 'warehouse_id']);
        });
    }

    /**
     * Menghapus tabel stock_in_requests.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_in_requests');
    }
};