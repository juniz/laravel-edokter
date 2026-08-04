<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penandaan_area_operasi', function (Blueprint $table) {
            $table->id();
            $table->string('no_rawat')->unique();
            $table->string('no_rm');
            $table->string('nama_pasien');
            $table->date('tgl_lahir')->nullable();
            $table->text('prosedur_operasi');
            $table->string('operator_id')->nullable();
            $table->string('nama_operator');
            $table->dateTime('tanggal_penandaan');
            $table->longText('gambar_penandaan_base64');
            $table->string('format_gambar')->default('image/png');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penandaan_area_operasi');
    }
};
