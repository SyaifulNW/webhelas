<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    use HasFactory;
    protected $table = 'data'; // Specify the table name if it doesn't follow Laravel's naming convention
    protected $fillable = [
        'nama',
        'leads',
        'leads_custom',
        'provinsi_id',
        'provinsi_nama',
        'kota_id',
        'kota_nama',
        'jenis_bisnis',
        'jenisbisnis',
        'nama_bisnis',
        'no_wa',
        'situasi_bisnis',
        'kendala',
        'kelas_id', 'created_by', 'created_by_role',
        'berhasil_spin',
        'ikut_zoom',
        'bant_budget',
        'bant_authority',
        'bant_time',
        'bant',
        'keterangan_spin',
        'ikut_kelas',
        'potensi',
        'pindah_salesplan_at',
        'fu1', 'fu1_wa', 'fu1_telp', 'fu1_at', 'fu1_hasil', 'fu1_tindak_lanjut',
        'fu2', 'fu2_wa', 'fu2_telp', 'fu2_at', 'fu2_hasil', 'fu2_tindak_lanjut',
        'fu3', 'fu3_wa', 'fu3_telp', 'fu3_at', 'fu3_hasil', 'fu3_tindak_lanjut',
        'fu4', 'fu4_wa', 'fu4_telp', 'fu4_at', 'fu4_hasil', 'fu4_tindak_lanjut',
        'fu5', 'fu5_wa', 'fu5_telp', 'fu5_at', 'fu5_hasil', 'fu5_tindak_lanjut',
        'fu6', 'fu6_wa', 'fu6_telp', 'fu6_at', 'fu6_hasil', 'fu6_tindak_lanjut',
        'fu7', 'fu7_wa', 'fu7_telp', 'fu7_at', 'fu7_hasil', 'fu7_tindak_lanjut',
        'fu8', 'fu8_wa', 'fu8_telp', 'fu8_at', 'fu8_hasil', 'fu8_tindak_lanjut',
        'fu9', 'fu9_wa', 'fu9_telp', 'fu9_at', 'fu9_hasil', 'fu9_tindak_lanjut',
        'fu10', 'fu10_wa', 'fu10_telp', 'fu10_at', 'fu10_hasil', 'fu10_tindak_lanjut',
        'is_no_potensi',
    ];

    protected $casts = [
        'fu1_at' => 'datetime',
        'fu2_at' => 'datetime',
        'fu3_at' => 'datetime',
        'fu4_at' => 'datetime',
        'fu5_at' => 'datetime',
        'fu6_at' => 'datetime',
        'fu7_at' => 'datetime',
        'fu8_at' => 'datetime',
        'fu9_at' => 'datetime',
        'fu10_at' => 'datetime',
    ];
    public function kelas()
    {
       return $this->belongsTo(Kelas::class, 'kelas_id');
    }
    public function provinsi()
    {
        return $this->belongsTo('App\Models\Provinsi', 'provinsi_id');
    }
    public function kota()
    {
        return $this->belongsTo('App\Models\Kota', 'kota_id');
    
}
    public function getLeadsAttribute($value)
    {
        return ucfirst($value);
    }
    /*
    public function jenisBisnis()
    {
        return $this->belongsTo('App\Models\jenisbisnis', 'jenis_bisnis');
    }
    */
    public function salesplan()
    {
        return $this->hasMany('App\Models\SalesPlan', 'data_id');
    }
    
    public function createdBy()
{
    return $this->belongsTo(User::class, 'created_by');
}
}

