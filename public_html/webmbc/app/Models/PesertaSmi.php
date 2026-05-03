<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesertaSmi extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'sales_plan_id',
        'nama', 
        'nama_asli',
        'nama_2',
        'nama_asli_2',
        'status', 
        'one_on_one_coaching', 
        'tanggal_masuk', 
        'tanggal_selesai', 
        'biaya_pendaftaran', 
        'closing_cs_id', 
        'cs_name',
        'is_lunas',
        'created_by',
        'spp_1', 'spp_2', 'spp_3', 'spp_4', 'spp_5', 'spp_6', 
        'spp_7', 'spp_8', 'spp_9', 'spp_10', 'spp_11', 'spp_12'
    ];

    public function salesPlan()
    {
        return $this->belongsTo(SalesPlan::class, 'sales_plan_id');
    }
}
