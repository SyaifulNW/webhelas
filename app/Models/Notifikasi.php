<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $fillable = ['user_id', 'pesan', 'is_read','sender_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function sender()
{
    return $this->belongsTo(User::class, 'sender_id');
}
}



