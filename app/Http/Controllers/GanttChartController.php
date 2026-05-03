<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProgramKerja;
use Illuminate\Support\Facades\Auth;
use App\Models\Inisiatif;
use App\Models\User; // Added this line

use Carbon\Carbon;

class GanttChartController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user->role;
        $viewRole = $request->query('view_role');
        $targetUserId = $request->query('user_id');

        // Fetch Chapter users for the filter dropdown if admin
        $chapters = [];
        if ($role === 'admin' || $role === 'administrator') {
            $chapters = User::where('role', 'chapter')->orderBy('name')->get();
        }

        // Determine who the target user is (either the person being viewed, or the logged in user)
        $targetUser = $user;
        if ($targetUserId) {
            $foundUser = \App\Models\User::find($targetUserId);
            if ($foundUser) {
                // Determine if the logged-in user is allowed to view this person (usually yes, based on previous reqs)
                $targetUser = $foundUser;
            }
        }

        // === ADMIN BISA LIHAT SEMUA ATAU FILTER BY ROLE ===
        if ($role === 'admin' || $role === 'administrator') {
            $query = ProgramKerja::with([
                'inisiatifs.requester',
                'inisiatifs' => function ($sub) use ($targetUser, $targetUserId) {
                    if ($targetUserId) {
                        $sub->where('pic', 'like', '%' . trim($targetUser->name) . '%')
                            ->orWhereHas('programKerja', function ($prog) use ($targetUser) {
                                $prog->where('created_by', $targetUser->id);
                            });
                    }
                }
            ]);

            if ($viewRole) {
                $query->where('created_by_role', $viewRole);
            } else {
                // By default see 'produksi' in Gantt
                $query->where('created_by_role', 'produksi');
            }
            
            if ($targetUserId) {
                $query->where(function ($q) use ($targetUser) {
                    $q->where('created_by', $targetUser->id)
                        ->orWhereHas('inisiatifs', function ($sub) use ($targetUser) {
                            $sub->where('pic', 'like', '%' . trim($targetUser->name) . '%');
                        });
                });
            }
            $programs = $query->get();
        } elseif ($user->name === 'Linda' || stripos($user->name, 'Yasmin') !== false) {
            // Linda / Yasmin bisa lihat punya sendiri + orang lain yang diijinkan
            $programs = ProgramKerja::with([
                'inisiatifs.requester',
                'inisiatifs' => function ($sub) use ($user, $targetUser, $targetUserId) {
                    $sub->where(function ($q) use ($user, $targetUser) {
                        $q->where('pic', 'like', '%' . trim($targetUser->name) . '%')
                          ->orWhere('pic', $user->name);
                    })
                    ->orWhereHas('programKerja', function ($prog) use ($user, $targetUser) {
                        $prog->where('created_by', $user->id)
                             ->orWhere('created_by', $targetUser->id);
                    });
                }
            ])
            ->where(function ($q) use ($user, $targetUser) {
                $q->where('created_by', $user->id)
                    ->orWhere('created_by', $targetUser->id)
                    ->orWhereHas('user', function ($subQ) {
                        $subQ->whereIn('name', ['Felmi', 'Nisa', 'Eko Sulis', 'Arifa']);
                    })
                    ->orWhereHas('inisiatifs', function ($sub) use ($targetUser) {
                        $sub->where('pic', 'like', '%' . trim($targetUser->name) . '%');
                    });
            })
            ->get();
        } else {
            // Selain admin & Linda/Yasmin -> lihat miliknya sendiri ATAU yang ditugaskan ke dia (PIC)
            $isFelmi = stripos($user->name, 'Felmi') !== false;

            if ($isFelmi) {
                // Felmi: KHUSUS yang dia jadi PIC saja
                $programs = ProgramKerja::with([
                    'inisiatifs.requester',
                    'inisiatifs' => function ($sub) use ($user) {
                        $sub->where('pic', 'like', '%' . trim($user->name) . '%');
                    }
                ])
                    ->whereHas('inisiatifs', function ($sub) use ($user) {
                        $sub->where('pic', 'like', '%' . trim($user->name) . '%');
                    })
                    ->get();
            } else {
                // Yang lain: Yang dia buat ATAU yang dia jadi PIC (berdasarkan targetUser)
                // Termasuk Chapter & Reseller
                $programs = ProgramKerja::with([
                    'inisiatifs.requester',
                    'inisiatifs' => function ($sub) use ($targetUser) {
                        $sub->where('pic', 'like', '%' . trim($targetUser->name) . '%')
                            ->orWhereHas('programKerja', function ($prog) use ($targetUser) {
                            $prog->where('created_by', $targetUser->id);
                        });
                    }
                ])
                    ->where(function ($q) use ($targetUser) {
                        $q->where('created_by', $targetUser->id)
                            ->orWhereHas('inisiatifs', function ($sub) use ($targetUser) {
                                $sub->where('pic', 'like', '%' . trim($targetUser->name) . '%');
                            });
                    })
                    ->get();
            }
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
                    'id' => $inisiatif->id,
                    'name' => $inisiatif->judul,
                    'program' => $program->judul,
                    'start' => $start->format('Y-m-d'),
                    'end' => $end->format('Y-m-d'),
                    'pic' => $inisiatif->pic ?? '-',
                    'requester' => $inisiatif->requester->name ?? '-',
                    'status' => $status,
                    'progress' => $inisiatif->progress ?? 0,
                ];
            }
        }

        return view('marketing.gantt.index', compact('ganttData', 'viewRole', 'targetUser', 'chapters'));
    }

    public function markDone($id)
    {
        $inisiatif = Inisiatif::findOrFail($id);
        $inisiatif->status = 'done';
        $inisiatif->save();

        return redirect()->back()->with('success', 'Status updated');
    }







}
