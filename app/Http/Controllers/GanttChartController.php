<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProgramKerja;
use Illuminate\Support\Facades\Auth;
use App\Models\Inisiatif;

use Carbon\Carbon;

class GanttChartController extends Controller
{
public function index()
{
    $user = Auth::user();
    $role = $user->role;

    // === ADMIN BISA LIHAT SEMUA ===
    if ($role === 'admin' || $role === 'administrator') {
        $programs = ProgramKerja::with('inisiatifs')->get();
    } elseif ($user->name === 'Linda') {
        // Linda bisa lihat punya sendiri + Felmi + Nisa
        $programs = ProgramKerja::with('inisiatifs')
            ->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('user', function($subQ) {
                      $subQ->whereIn('name', ['Felmi', 'Nisa', 'Eko Sulis', 'Arifa']);
                  });
            })
            ->get();
    } else {
        // Selain admin → hanya lihat miliknya sendiri
        $programs = ProgramKerja::with('inisiatifs')
            ->where('created_by_role', $role)
            ->where('created_by', $user->id)
            ->get();
    }

    $ganttData = [];

    foreach ($programs as $program) {
        foreach ($program->inisiatifs as $inisiatif) {

            $start = $inisiatif->tanggal_mulai
                ? Carbon::parse($inisiatif->tanggal_mulai)
                : Carbon::now();

            $end = $inisiatif->tanggal_selesai
                ? Carbon::parse($inisiatif->tanggal_selesai)
                : Carbon::now()->addDay();

            $status = $inisiatif->status ?? 'progress';

            if ($status !== 'done' && $end->lt(Carbon::now())) {
                $status = 'overdue';
            }

            $ganttData[] = [
                'id'       => $inisiatif->id,
                'name'     => $inisiatif->judul,
                'program'  => $program->judul,
                'start'    => $start->format('Y-m-d'),
                'end'      => $end->format('Y-m-d'),
                'pic'      => $inisiatif->pic ?? '-',
                'status'   => $status,
                'progress' => $inisiatif->progress ?? 0,
            ];
        }
    }

    return view('marketing.gantt.index', compact('ganttData'));
}

public function markDone($id)
{
    $inisiatif = Inisiatif::findOrFail($id);
    $inisiatif->status = 'done';
    $inisiatif->save();

    return redirect()->back()->with('success', 'Status updated');
}







}
