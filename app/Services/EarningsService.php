<?php

namespace App\Services;

use App\Models\User;
use App\Models\SalesPlan;
use Illuminate\Support\Facades\DB;

class EarningsService
{
    /**
     * Calculate total earnings for a user (Chapter/Reseller/Agen) based on approved sales.
     * If no year/month is provided, it calculates for all time.
     */
    public static function calculateTotalEarnings($userId, $year = null, $month = null)
    {
        $user = User::find($userId);
        if (!$user) return 0;

        $role = strtolower($user->role);
        $chapterName = $user->chapter;
        
        $isChapter = ($role === 'chapter');
        $cleanChapterName = trim(str_ireplace('CHAPTER', '', $chapterName));
        
        // Identify Team Members
        $resellerMembersIds = User::where('role', 'reseller')
            ->where('created_by', $userId)
            ->pluck('id');
        
        $allTeamIds = $resellerMembersIds->merge([$userId])->unique();

        // Regional IDs for Chapter
        $regionalTeamIds = $allTeamIds;
        if ($isChapter) {
            $regionalMemberIds = User::where('role', 'reseller')
                ->where('chapter', 'LIKE', '%' . $cleanChapterName . '%')
                ->pluck('id');
            $regionalTeamIds = $regionalMemberIds->merge([$userId])->unique();
        }

        // Base Query for Approved Sales
        $baseQuery = SalesPlan::join('peserta_smis', 'salesplans.id', '=', 'peserta_smis.sales_plan_id')
            ->where('salesplans.status', 'sudah_transfer')
            ->where('peserta_smis.approval_status', 'Approved')
            ->when($year, function ($q) use ($year) {
                $q->whereYear('salesplans.updated_at', $year);
            })
            ->when($month, function ($q) use ($month) {
                $q->whereMonth('salesplans.updated_at', $month);
            });

        // 1. Omset Pribadi
        $omsetPribadi = (clone $baseQuery)
            ->where('salesplans.created_by', $userId)
            ->sum(DB::raw('CAST(COALESCE(peserta_smis.pembayaran_spp, salesplans.nominal, 0) AS DECIMAL(15,2))'));

        // 2. Omset Reseller (Direct Downline)
        $omsetReseller = 0;
        if ($resellerMembersIds->isNotEmpty()) {
            $omsetReseller = (clone $baseQuery)
                ->whereIn('salesplans.created_by', $resellerMembersIds)
                ->sum(DB::raw('CAST(COALESCE(peserta_smis.pembayaran_spp, salesplans.nominal, 0) AS DECIMAL(15,2))'));
        }

        // 3. Komisi (10% of Personal Sales)
        $komisi = $omsetPribadi * 0.10;

        // 4. Direct Fee (Rp 500.000 per Approved regional participant for Chapter)
        $directFee = 0;
        if ($isChapter) {
            $totalParticipantsCount = (clone $baseQuery)
                ->whereIn('salesplans.created_by', $regionalTeamIds)
                ->count();
            $directFee = $totalParticipantsCount * 500000;
        }

        // 5. Royalty (5% of Reseller Sales)
        $royalti = $omsetReseller * 0.05;

        // 6. Bonus Pribadi (Tiered: 10M -> 5%, >20M -> 10%)
        $bonusPribadi = 0;
        if ($omsetPribadi >= 20000000) {
            $bonusPribadi = $omsetPribadi * 0.10;
        } elseif ($omsetPribadi >= 10000000) {
            $bonusPribadi = $omsetPribadi * 0.05;
        }

        // 7. Bonus Tim (10% if Team Sales >= 30,000,000)
        $bonusTim = 0;
        $totalTeamSales = $omsetPribadi + $omsetReseller;
        if ($resellerMembersIds->isNotEmpty() && $totalTeamSales >= 30000000) {
            $bonusTim = $totalTeamSales * 0.10;
        }

        return (float) ($komisi + $directFee + $royalti + $bonusPribadi + $bonusTim);
    }
}
