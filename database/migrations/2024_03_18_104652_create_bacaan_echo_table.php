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
        Schema::create('bacaan_echo', function (Blueprint $table) {
            $table->string('no_rawat', 17)->primary();
            $table->string('kd_dokter', 20);
            $table->string('dokter_pengirim', 80);
            $table->text('hasil_bacaan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bacaan_echo');
    }
};
