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
        Schema::create('servers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->enum('type', ['cpanel', 'directadmin', 'proxmox']);
            $table->string('endpoint');
            $table->string('auth_secret_ref'); // reference ke secret manager/env
            $table->enum('status', ['active', 'maintenance', 'disabled'])->default('active');
            $table->json('meta')->nullable(); // limit account, packages mapping
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
