<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengajuanAnggaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'tanggal_pengajuan' => 'datetime',
        'recurring_end_date' => 'date',
    ];  

    protected $fillable = [
        'tanggal_pengajuan',
        'nama_pengajuan',
        'jumlah_biaya',
        'user_id',
        'diajukan_oleh',
        'status',
        'keterangan',
        'catatan_admin',
        'biaya_disetujui',
        'bukti_transfer',
        'is_recurring',
        'recurring_interval',
        'recurring_end_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
