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
        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->char('ticket_id', 26);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // agent
            $table->char('customer_id', 26)->nullable();
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->timestamps();

            $table->index('ticket_id');
            
            $table->foreign('ticket_id')->references('id')->on('tickets')->restrictOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_replies');
    }
};
