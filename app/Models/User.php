<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'wa',
        'chapter',
        'id_no',
        'photo',
        'bio',
        'created_by',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // app/Models/User.php
    public function data()
    {
        return $this->hasMany(Data::class, 'created_by', 'name');
    }

    public function leads()
    {
        return $this->hasMany(Leads::class, 'created_by', 'name');
    }

    public function salesplans()
    {
        return $this->hasMany(SalesPlan::class, 'created_by');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function ensureWalletExists()
    {
        if (!$this->wallet) {
            $walletId = 'MBC-' . strtoupper(substr(uniqid(), -6));
            return Wallet::create([
                'user_id' => $this->id,
                'wallet_id' => $walletId,
                'balance' => 0,
                'status' => 'active'
            ]);
        }
        return $this->wallet;
    }
}
