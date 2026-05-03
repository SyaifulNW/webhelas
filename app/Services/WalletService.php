<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Credit a user's wallet automatically when a payment is approved.
     */
    public static function creditCommission($userId, $amount, $source, $description = null)
    {
        $user = User::find($userId);
        if (!$user) return null;

        if (!in_array(strtolower($user->role), ['chapter', 'reseller', 'agen'])) {
            return null;
        }

        $wallet = $user->ensureWalletExists();
        return $wallet->addIncome($amount, $source, $description);
    }
}
