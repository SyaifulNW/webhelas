<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramKerja extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'deskripsi',
        'created_by',
            'created_by_role',
    ];

    /**
     * Relasi ke user pembuat program kerja
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke tabel inisiatif (1 program bisa punya banyak inisiatif)
     */
    public function inisiatifs()
    {
        return $this->hasMany(Inisiatif::class);
    }
}
