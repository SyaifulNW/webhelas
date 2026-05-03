<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingPerformance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_name',
        'tema',
        'pemateri',
        'tanggal',
        'lokasi',
        'jenis_event',
        'target_peserta',
        'peserta_hadir',
        'target_closing',
        'real_closing',
        'selisih',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
