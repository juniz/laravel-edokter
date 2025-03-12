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
        Schema::create('finger_bpjs', function (Blueprint $table) {
            $table->string('no_rawat', 17)->primary()->charset('latin1')->collation('latin1_swedish_ci');
            $table->string('no_kartu', 17)->nullable()->charset('latin1')->collation('latin1_swedish_ci');
            $table->date('tanggal')->nullable();
            $table->string('kode', 3)->nullable();
            $table->string('status', 150)->nullable();
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
        Schema::dropIfExists('finger_bpjs');
    }
};
