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
        Schema::create('panel_accounts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->char('server_id', 26);
            $table->char('subscription_id', 26);
            $table->string('username');
            $table->string('domain');
            $table->enum('status', ['active', 'suspended', 'terminated'])->default('active');
            $table->timestamp('last_sync_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['server_id', 'username']);
            $table->index('subscription_id');
            $table->index('status');
            
            $table->foreign('server_id')->references('id')->on('servers')->restrictOnDelete();
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panel_accounts');
    }
};
