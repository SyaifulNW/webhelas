<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class PesertaSmi extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'sales_plan_id',
        'level',
        'nama',
        'nama_asli',
        'nama_2',
        'nama_asli_2',
        'status',
        'one_on_one_coaching',
        'tanggal_masuk',
        'tanggal_selesai',
        'biaya_pendaftaran',
        'spp_awal',
        'pembayaran_spp',
        'total_pembayaran',
        'closing_cs_id',
        'cs_name',
        'is_lunas',
        'approval_status',
        'created_by',
        'spp_custom_schedule',
        'spp_1',
        'spp_2',
        'spp_3',
        'spp_4',
        'spp_5',
        'spp_6',
        'spp_7',
        'spp_8',
        'spp_9',
        'spp_10',
        'spp_11',
        'spp_12',
        'tanggal_spp_1',
        'tanggal_spp_2',
        'tanggal_spp_3',
        'tanggal_spp_4',
        'tanggal_spp_5',
        'tanggal_spp_6',
        'tanggal_spp_7',
        'tanggal_spp_8',
        'tanggal_spp_9',
        'tanggal_spp_10',
        'tanggal_spp_11',
        'tanggal_spp_12',
        'bukti_transfer'
    ];

    public function salesPlan()
    {
        return $this->belongsTo(SalesPlan::class, 'sales_plan_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closingCs()
    {
        return $this->belongsTo(User::class, 'closing_cs_id');
    }

    protected $casts = [
        'spp_custom_schedule' => 'array',
    ];
}
