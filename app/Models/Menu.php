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
        if ($menu) {
            // Special case for settings menu for Yasmin
            if ($name === 'settings' && auth()->check() && auth()->user()->name === 'Yasmin') {
                return true;
            }
            return $menu->is_active;
        }
        return true; // Default true if not found
    }
}
