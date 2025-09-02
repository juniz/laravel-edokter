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
        Schema::table('audit_sql_logs', function (Blueprint $table) {
            $table->string('query_hash', 64)->nullable()->after('url');
            $table->index('query_hash'); // Index untuk performance
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_sql_logs', function (Blueprint $table) {
            $table->dropIndex(['query_hash']);
            $table->dropColumn('query_hash');
        });
    }
};
