<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'balance',
        'pending_balance',
        'status',
        'bank_name',
        'account_number',
        'account_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function addIncome($amount, $source, $description = null)
    {
        return DB::transaction(function () use ($amount, $source, $description) {
            $this->balance += $amount;
            $this->save();

            return $this->transactions()->create([
                'amount' => $amount,
                'type' => 'income',
                'source' => $source,
                'status' => 'success',
                'description' => $description,
                'reference_no' => 'INC-' . strtoupper(substr(uniqid(), -6)),
            ]);
        });
    }
}
