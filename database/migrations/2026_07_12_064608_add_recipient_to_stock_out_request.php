<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_out_requests', function (Blueprint $table) {
            $table->string('recipient_name')->nullable()->after('request_number');
            $table->string('recipient_postal_code',20)->nullable()->after('recipient_name');
            $table->text('recipient_address')->nullable()->after('recipient_postal_code');
            $table->string('recipient_phone',30)->nullable()->after('recipient_address');
            $table->string('ems_number',100)->nullable()->after('recipient_phone');
            $table->string('sender_name')->nullable()->after('ems_number');
        });
    }

    public function down(): void
    {
        Schema::table('stock_out_requests', function (Blueprint $table) {
            $table->dropColumn('recipient_name');
            $table->dropColumn('recipient_postal_code');
            $table->dropColumn('recipient_address');
            $table->dropColumn('recipient_phone');
            $table->dropColumn('ems_number');
            $table->dropColumn('sender_name');

        });
    }
};
