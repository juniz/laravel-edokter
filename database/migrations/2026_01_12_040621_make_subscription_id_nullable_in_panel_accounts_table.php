<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('panel_accounts', function (Blueprint $table) {
            // Drop foreign key constraint terlebih dahulu
            $table->dropForeign(['subscription_id']);
        });

        // Ubah kolom menjadi nullable menggunakan DB facade
        // karena change() memerlukan doctrine/dbal package
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `panel_accounts` MODIFY `subscription_id` CHAR(26) NULL');

        Schema::table('panel_accounts', function (Blueprint $table) {
            // Tambah kembali foreign key constraint dengan nullOnDelete
            // karena sekarang subscription_id bisa null (untuk manual accounts)
            $table->foreign('subscription_id')
                ->references('id')
                ->on('subscriptions')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panel_accounts', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['subscription_id']);
        });

        // Ubah kembali menjadi not nullable menggunakan DB facade
        // Pastikan tidak ada NULL values sebelum menjalankan ini
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `panel_accounts` MODIFY `subscription_id` CHAR(26) NOT NULL');

        Schema::table('panel_accounts', function (Blueprint $table) {
            // Tambah kembali foreign key constraint dengan restrictOnDelete
            $table->foreign('subscription_id')
                ->references('id')
                ->on('subscriptions')
                ->restrictOnDelete();
        });
    }
};
