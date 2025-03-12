<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferensiMobilejknBpjs extends Model
{
    use HasFactory;

    protected $table = 'referensi_mobilejkn_bpjs';
    protected $primaryKey = 'nobooking';
    public $timestamps = false;
    public $incrementing = false;
}
