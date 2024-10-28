<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanRadiologi extends Model
{
    use HasFactory;

    protected $table = 'permintaan_radiologi';
    protected $primaryKey = 'noorder';
    public $incrementing = false;
    public $timestamps = false;

    public function regPeriksa()
    {
        return $this->belongsTo(RegPeriksa::class, 'no_rawat', 'no_rawat');
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'no_rkm_medis', 'no_rkm_medis');
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'dokter_perujuk', 'kd_dokter');
    }

    public function permintaanPemeriksaanRadiologi()
    {
        return $this->hasMany(PermintaanPemeriksaanRadiologi::class, 'noorder', 'noorder');
    }
}
