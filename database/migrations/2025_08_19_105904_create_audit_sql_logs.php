<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit_sql_logs', function (Blueprint $t) {
            $t->id();
            $t->longText('sql');
            $t->json('bindings')->nullable();
            $t->integer('time_ms')->nullable();
            $t->string('user_id', 20)->nullable();
            $t->string('ip', 45)->nullable();
            $t->string('url', 2048)->nullable();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audit_sql_logs');
    }
};
