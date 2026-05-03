<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingParticipant extends Model
{
    use HasFactory;

    protected $table = 'marketing_participants';

    protected $fillable = [
        'nama',
        'no_wa',
        'provinsi_id',
        'provinsi_nama',
        'kota_id',
        'kota_nama',
        'nama_bisnis',
        'jenis_bisnis',
        'omset',
        'potensi',
        'created_by',
        'is_transferred',
        'assigned_cs'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
