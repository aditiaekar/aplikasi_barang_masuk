<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom role dan profil pengguna ke tabel users.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->nullable()
                ->after('id')
                ->constrained('roles')
                ->nullOnDelete();

            $table->string('username')
                ->unique()
                ->after('name');

            $table->string('phone')
                ->nullable()
                ->after('email');

            $table->boolean('is_active')
                ->default(true)
                ->after('password');

            $table->timestamp('last_login_at')
                ->nullable()
                ->after('is_active');
        });
    }

    /**
     * Menghapus kolom tambahan jika migration di-rollback.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');

            $table->dropUnique('users_username_unique');

            $table->dropColumn([
                'username',
                'phone',
                'is_active',
                'last_login_at',
            ]);
        });
    }
};