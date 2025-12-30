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
            
            // GLOBAL override if menu is disabled globally
            if (!$menu->is_active) {
                return false;
            }

        // Role based check
        if (auth()->check()) {
            $role = strtolower(trim(auth()->user()->role));
            $roleMenu = \App\Models\RoleMenu::where('role', $role)
                        ->where('menu_id', $menu->id)
                        ->first();
            if ($roleMenu) {
                return $roleMenu->can_access;
            }
        }

        // Fallback to global setting
        return $menu->is_active;
        }
        return true; // Default true if not found in menus table
    }

    public static function hasRoleAccess($menuName, $role) {
        $menu = self::where('name', $menuName)->first();
        if (!$menu) return true;

        $roleMenu = \App\Models\RoleMenu::where('role', $role)
                    ->where('menu_id', $menu->id)
                    ->first();
        
        return $roleMenu ? $roleMenu->can_access : true; // Default allow if not set
    }
}
