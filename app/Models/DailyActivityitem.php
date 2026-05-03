<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyActivityitem extends Model
{
    use HasFactory;
       protected $fillable = [
        'daily_activity_id', 'kategori', 'aktivitas', 'deskripsi', 'target', 'real'
    ];

    public function dailyActivity()
    {
        return $this->belongsTo(DailyActivity::class);
    }
}
