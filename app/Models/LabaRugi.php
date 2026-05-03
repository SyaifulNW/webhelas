<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabaRugi extends Model
{
    use HasFactory;

    protected $table = 'laba_rugis';

    protected $fillable = [
        'bulan',
        'tahun',
        'tanggal',
        'type',
        'parent_keterangan',
        'keterangan',
        'jumlah',
        'created_by'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
