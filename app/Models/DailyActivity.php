<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class DailyActivity extends Model
{
    use HasFactory;
      
      protected $fillable = ['tanggal','created_by'];

    public function items()
    {
        return $this->hasMany(DailyActivityitem::class);
    }
}
