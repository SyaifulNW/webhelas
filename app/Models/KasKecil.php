<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasKecil extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'keterangan',
        'masuk',
        'keluar',
        'sisa',
        'bukti_transfer',
        'created_by'
    ];
}
