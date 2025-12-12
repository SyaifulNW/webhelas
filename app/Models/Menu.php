<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'label', 'is_active'];

    // Helper to check if active
    public static function isActive($name) {
        $menu = self::where('name', $name)->first();
        return $menu ? $menu->is_active : true; // Default true if not found, or false? Let's say default true to avoid breaking if not seeded.
    }
}
