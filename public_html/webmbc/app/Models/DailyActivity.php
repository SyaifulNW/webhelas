<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyActiviti extends Model
{
    use HasFactory;
     protected $fillable = ['user_id','activity_id','tanggal','realisasi'];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function updateAutomated($userId, $tanggal)
    {
        $user = User::find($userId);
        if (!$user) return;

        // 1. List Building / Database (Data baru yang dibuat hari ini)
        $act1 = Activity::where('nama', 'LIKE', '%List Building / Database%')->first();
        if ($act1) {
            $count = Data::where('created_by', $user->name)
                ->whereDate('created_at', $tanggal)
                ->count();
            self::updateOrCreate(
                ['user_id' => $userId, 'activity_id' => $act1->id, 'tanggal' => $tanggal],
                ['realisasi' => $count]
            );
        }

        // 2. Edukasi & Membangun Hubungan (WA) - Menggunakan SUM untuk efisiensi
        $act2 = Activity::where('nama', 'LIKE', '%Edukasi % Membangun Hubungan%')->first();
        if ($act2) {
            $query = Data::where('created_by', $user->name);
            
            $selects = [];
            for ($i = 1; $i <= 10; $i++) {
                $selects[] = "SUM(CASE WHEN fu{$i}_wa = 1 AND DATE(fu{$i}_at) = '{$tanggal}' THEN 1 ELSE 0 END)";
            }
            
            $count = $query->selectRaw(implode(' + ', $selects) . ' as total')->first()->total ?? 0;

            self::updateOrCreate(
                ['user_id' => $userId, 'activity_id' => $act2->id, 'tanggal' => $tanggal],
                ['realisasi' => $count]
            );
        }

        // 3. Telepon Database Prospek
        $act3 = Activity::where(function($q) {
            $q->where('nama', 'LIKE', '%Telepon database%')
              ->orWhere('nama', 'LIKE', '%Telepon Database Prospek%');
        })->first();

        if ($act3) {
            $query = Data::where('created_by', $user->name);
            
            $selects = [];
            for ($i = 1; $i <= 10; $i++) {
                $selects[] = "SUM(CASE WHEN fu{$i}_telp = 1 AND DATE(fu{$i}_at) = '{$tanggal}' THEN 1 ELSE 0 END)";
            }
            
            $count = $query->selectRaw(implode(' + ', $selects) . ' as total')->first()->total ?? 0;

            self::updateOrCreate(
                ['user_id' => $userId, 'activity_id' => $act3->id, 'tanggal' => $tanggal],
                ['realisasi' => $count]
            );
        }
    }
}
