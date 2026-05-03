<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'amount',
        'type',
        'source',
        'status',
        'bank_name',
        'account_number',
        'account_name',
        'description',
        'admin_note',
        'reference_no',
        'proof_of_transfer'
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
