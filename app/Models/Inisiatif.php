<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inisiatif extends Model
{
    use HasFactory;

protected $fillable = [
   'program_kerja_id',
   'judul',
   'pic',
   'target',
   'realisasi',
   'nilai',
   'tanggal_mulai',
   'tanggal_selesai',
   'status',
   'deskripsi'
];


    /**
     * Relasi ke tabel ProgramKerja (setiap inisiatif milik satu program kerja)
     */
    public function programKerja()
    {
        return $this->belongsTo(ProgramKerja::class, 'program_kerja_id');
    }
}
