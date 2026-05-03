<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiSosmed extends Model
{
    protected $fillable = [
        'user_id', 'bulan', 'tahun',
        'followers_real', 'followers_skor',
        'respons_dm_real', 'respons_dm_skor',
        'dm_masuk_real', 'dm_masuk_skor',
        'link_wa_real', 'link_wa_skor',
        'zoom_real', 'zoom_skor',
        'skor_disiplin', 'nilai_akhir'
    ];
}
