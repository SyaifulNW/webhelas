<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;
    protected $fillable = ['categories_id','nama','target_daily','target_bulanan','bobot'];

    public function kategori()
    {
        return $this->belongsTo(Category::class, 'categories_id');
    }

    public function dailyActivities()
    {
        return $this->hasMany(DailyActivity::class);
    }
}
