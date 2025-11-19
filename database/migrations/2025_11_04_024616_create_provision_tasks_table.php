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
        Schema::create('provision_tasks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->char('subscription_id', 26);
            $table->char('server_id', 26);
            $table->enum('action', [
                'create',
                'suspend',
                'unsuspend',
                'terminate',
                'change_plan',
                'reset_password',
                'sync'
            ]);
            $table->enum('status', ['queued', 'running', 'succeeded', 'failed'])->default('queued');
            $table->unsignedInteger('attempts')->default(0);
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('action');
            $table->index('subscription_id');
            $table->index(['status', 'action']);
            
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->restrictOnDelete();
            $table->foreign('server_id')->references('id')->on('servers')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provision_tasks');
    }
};
