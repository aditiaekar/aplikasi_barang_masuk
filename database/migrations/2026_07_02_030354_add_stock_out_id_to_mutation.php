<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_mutations', function (Blueprint $table) {
            $table->foreignId('stock_out_id')
                ->nullable()
                ->after('stock_in_id')
                ->constrained('stock_outs')
                ->nullOnDelete();

            $table->enum('mutation_type', ['in', 'out'])
                ->default('in')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('stock_mutations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stock_out_id');
        });
    }
};
