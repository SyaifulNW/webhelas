<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleMenu extends Model
{
    use HasFactory;

    protected $fillable = ['role', 'menu_id', 'can_access'];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
