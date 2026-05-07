<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migration untuk membuat tabel roles.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel roles jika migration di-rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};