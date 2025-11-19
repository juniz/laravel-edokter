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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->char('customer_id', 26);
            $table->char('product_id', 26);
            $table->char('plan_id', 26);
            $table->enum('status', [
                'trialing',
                'active',
                'past_due',
                'suspended',
                'cancelled',
                'terminated'
            ])->default('trialing');
            $table->timestamp('start_at');
            $table->timestamp('end_at')->nullable();
            $table->timestamp('next_renewal_at')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->enum('provisioning_status', [
                'pending',
                'in_progress',
                'done',
                'failed'
            ])->default('pending');
            $table->json('meta')->nullable(); // credential secret ref, panel username, domain, limits
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('status');
            $table->index('next_renewal_at');
            $table->index(['customer_id', 'status', 'next_renewal_at']);
            
            $table->foreign('customer_id')->references('id')->on('customers')->restrictOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
            $table->foreign('plan_id')->references('id')->on('plans')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
