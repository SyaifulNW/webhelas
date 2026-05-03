<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdsPerformance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kelas_id',
        'manual_name',
        'is_running',
        'bulan',
        'tahun',
        'tanggal_kelas',
        'tanggal_set',
        'ctr',
        'cpl',
        'conv_rate',
        'cpa',
        'budget_iklan',
        'pengajuan_budget',
        'total_leads',
        'jumlah_closing',
        'omset',
        'roas',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
}
