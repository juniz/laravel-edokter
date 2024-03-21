<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BacaanEcho extends Model
{
    use HasFactory;
    protected $table = 'bacaan_echo';
    protected $primaryKey = 'no_rawat';
    protected $fillable = ['no_rawat', 'kd_dokter', 'dokter_pengirim', 'hasil_bacaan'];
    public $incrementing = false;
}
