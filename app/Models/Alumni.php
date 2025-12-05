<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumni extends Model
{
    use HasFactory;
    protected $table = 'alumni'; // Specify the table name if it differs from the model name
    protected $fillable = [
        'nama',
        'leads',
        'leads_custom',
        'provinsi_id',
        'provinsi_nama',
        'kota_id',
        'kota_nama',
        'jenis_bisnis',
        'nama_bisnis',
        'no_wa',
        'kendala',
        'ikut_kelas',
        'kelas_id',
        'sudah_pernah_ikut_kelas_apa_saja',
        'kelas_yang_belum_diikuti_apa_saja',
        'created_by'

    ];
    protected $casts = [
        'sudah_pernah_ikut_kelas_apa_saja' => 'array',
        'kelas_yang_belum_diikuti_apa_saja' => 'array',
    ];
    public function kelas()
    {
        return $this->belongsTo('App\Models\Kelas', 'kelas_id');
    }

    public function data()
    {
        return $this->belongsTo(Data::class, 'data_id');
    }
    public function salesplan()
    {
        return $this->hasMany(SalesPlan::class, 'data_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
