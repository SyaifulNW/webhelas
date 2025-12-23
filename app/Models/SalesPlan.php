<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesPlan extends Model
{
    use HasFactory;
    use SoftDeletes; // Tambahkan ini
    protected $table = 'salesplans'; // Specify the table name if it differs from the model name
    protected $fillable = [
        'data_id',
        'fu1_hasil',
        'fu1_tindak_lanjut',
        'fu2_hasil',
        'fu2_tindak_lanjut',
        'fu3_hasil',
        'fu3_tindak_lanjut',
        'fu4_hasil',
        'fu4_tindak_lanjut',
        'fu5_hasil',
        'fu5_tindak_lanjut',
        'fu6_hasil',
        'fu6_tindak_lanjut',
        'fu7_hasil',
        'fu7_tindak_lanjut',
        'fu8_hasil',
        'fu8_tindak_lanjut',
        'keterangan',
        'nominal',
        'created_by',
        'komentar_atasan'
    ];
    protected $casts = [
        'status' => 'string', // Cast status to string
        'closing_paket' => 'boolean',
    ];


    /**
     * Relasi dengan model data.
     */
    public function data()
    {
        return $this->belongsTo(Data::class, 'data_id');
    }
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
    
    // app/Models/SalesPlan.php
public function createdBy()
{
    return $this->belongsTo(\App\Models\User::class, 'created_by');
}


    public function getCreatedByNameAttribute()
    {
        $names = [
            1 => 'Administrator',
            2 => 'Linda',
            3 => 'Yasmin',
            4 => 'Tursia',
            5 => 'Livia',
            6 => 'Shafa',
        ];

        return $names[$this->created_by] ?? '-';
    }
}
