<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class AdminWalletController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if ($user && ($user->role === 'administrator' || $user->name === 'Linda')) {
                return $next($request);
            }
            return redirect('/home')->with('error', 'Akses ditolak.');
        });
    }

    public function index()
    {
        $wallets = Wallet::with('user')->get();

        // Sync each wallet balance to ensure Admin sees the same data as the User
        foreach ($wallets as $wallet) {
            if ($wallet->user) {
                $totalEarningsAllTime = \App\Services\EarningsService::calculateTotalEarnings($wallet->user_id);
                $totalWithdrawnAllTime = $wallet->transactions()
                    ->where('type', 'withdrawal')
                    ->whereIn('status', ['success', 'pending', 'rejected'])
                    ->sum('amount');
                
                $wallet->balance = $totalEarningsAllTime - $totalWithdrawnAllTime;

                $wallet->pending_balance = $wallet->transactions()
                    ->where('type', 'withdrawal')
                    ->whereIn('status', ['pending', 'rejected'])
                    ->sum('amount');
                
                $wallet->save();
            }
        }

        $pendingWithdrawals = WalletTransaction::where('type', 'withdrawal')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('admin.wallet.index', compact('wallets', 'pendingWithdrawals'));
    }

    public function processWithdrawal(Request $request, $id)
    {
        $transaction = WalletTransaction::findOrFail($id);
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaksi sudah diproses.');
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'admin_note' => 'nullable|string',
            'proof_of_transfer' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $wallet = $transaction->wallet;
            
            if ($request->action === 'approve') {
                if ($wallet->balance < $transaction->amount) {
                    return back()->with('error', 'Saldo user tidak mencukupi untuk penarikan ini.');
                }
                
                $wallet->balance -= $transaction->amount;
                $wallet->save();
                
                $transaction->status = 'success';

                // Handle Proof of Transfer Upload
                if ($request->hasFile('proof_of_transfer')) {
                    $file = $request->file('proof_of_transfer');
                    $filename = 'WD_PROOF_' . time() . '_' . $transaction->id . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/wallet'), $filename);
                    $transaction->proof_of_transfer = 'uploads/wallet/' . $filename;
                }
            } else {
                $transaction->status = 'rejected';
            }

            $transaction->admin_note = $request->admin_note;
            $transaction->save();

            DB::commit();
            return back()->with('success', 'Transaksi berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function transactions()
    {
        $transactions = WalletTransaction::with('wallet.user')->latest()->paginate(50);
        return view('admin.wallet.transactions', compact('transactions'));
    }

    public function destroyTransaction($id)
    {
        $transaction = WalletTransaction::findOrFail($id);
        $transaction->delete();
        return back()->with('success', 'Data transaksi berhasil dihapus.');
    }
}
