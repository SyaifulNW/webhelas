@extends('layouts.masteradmin')

@section('content')
    @php
        use App\Models\Data;
    @endphp

    {{-- Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        .badge-lg {
            font-size: 1.1rem;
            padding: 0.8rem 1.4rem;
        }

        .card-header {
            font-size: 1rem;
        }

        .progress-bar {
            font-size: 0.9rem;
        }

        /* 🔵 Efek berdenyut lembut (pulse) */
        @keyframes pulseGlow {
            0% {
                box-shadow: 0 0 0 rgba(0, 123, 255, 0.4);
                transform: scale(1);
            }

            50% {
                box-shadow: 0 0 15px rgba(0, 123, 255, 0.5);
                transform: scale(1.03);
            }

            100% {
                box-shadow: 0 0 0 rgba(0, 123, 255, 0.4);
                transform: scale(1);
            }
        }

        /* 🎨 Tampilan cell reminder */
        .reminder-cell {
            background: linear-gradient(90deg, #e3f2fd, #bbdefb);
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 600;
            color: #0d47a1;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: pulseGlow 2s infinite ease-in-out;
            transition: transform 0.3s ease;
        }

        /* 🔔 Ikon lonceng bergetar ringan */
        .reminder-icon {
            color: #2196f3;
            animation: ring 2s infinite;
            font-size: 1.3rem;
        }

        @keyframes ring {
            0% {
                transform: rotate(0);
            }

            10% {
                transform: rotate(15deg);
            }

            20% {
                transform: rotate(-10deg);
            }

            30% {
                transform: rotate(5deg);
            }

            40% {
                transform: rotate(-5deg);
            }

            50%,
            100% {
                transform: rotate(0);
            }
        }

        /* Popup Motivasi */
        @keyframes popIn {
            from {
                transform: translate(-50%, -40%) scale(0.5);
                opacity: 0;
            }

            to {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }
        }

        #popupOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 9998;
        }

        #motivasiPopup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
            width: 90%;
            max-width: 400px;
            animation: popIn 0.5s ease-out;
        }

        /* Premium Colored Tabs Styling (Horizontal - Attached) */
        .premium-tab {
            border-radius: 10px 10px 0 0 !important;
            /* Round top only to "stick" to content */
            padding: 12px 25px !important;
            font-size: 0.95rem !important;
            font-weight: 700 !important;
            background: #f1f3f9;
            color: #4e73df;
            border: 1px solid #e3e6f0 !important;
            border-bottom: none !important;
            /* Remove bottom border to merge with content */
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-right: 5px;
            position: relative;
            z-index: 1;
        }

        .premium-tab i {
            font-size: 1.1rem;
        }

        /* Active State & Specific Colors - "Melekat" Fixed */
        .premium-tab.active {
            color: white !important;
            border-bottom: none !important;
            transform: none !important;
            /* Don't float */
            box-shadow: none !important;
            margin-bottom: -2px !important;
            /* Overlap with divider line */
            z-index: 10;
        }

        .premium-tab.active.color-primary {
            background-color: #4e73df !important;
            border-color: #4e73df !important;
            margin-bottom: -2px !important;
            /* Overlap the baseline exactly */
            padding-bottom: 12px !important;
            z-index: 2;
        }

        .premium-tab.active.color-success {
            background-color: #1cc88a !important;
            color: white !important;
            border-color: #1cc88a !important;
            margin-bottom: -2px !important;
            padding-bottom: 12px !important;
            z-index: 2;
        }

        .premium-tab:hover:not(.active) {
            background-color: #e2e6f0;
        }

        .nav-tabs {
            border-bottom: 2px solid #e3e6f0;
            /* The line they should stick to */
            margin-bottom: 0px !important;
        }

        .nav-item {
            margin-bottom: 10px;
        }

        /* 💊 Editable Pill Style */
        .editable-pill-container {
            display: inline-flex;
            align-items: center;
            background: #fff;
            border: 1.5px solid #e3e6f0;
            border-radius: 50px;
            padding: 2px 2px 2px 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            width: 160px;
        }

        .editable-pill-container:focus-within {
            border-color: #4e73df;
            box-shadow: 0 4px 8px rgba(78, 115, 223, 0.15);
        }

        .editable-pill-label {
            color: #b7b9cc;
            font-size: 0.85rem;
            font-weight: 600;
            margin-right: 8px;
            user-select: none;
        }

        .editable-pill-input {
            border: none;
            background: transparent;
            color: #1cc88a;
            font-weight: 700;
            font-size: 1rem;
            width: 100%;
            outline: none;
            padding: 0;
            text-align: left;
        }

        /* Hide arrows/spinners in number input */
        .editable-pill-input::-webkit-outer-spin-button,
        .editable-pill-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .editable-pill-input[type=number] {
            -moz-appearance: textfield;
        }

        .editable-pill-submit {
            background: #4e73df;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 5px;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .editable-pill-submit:hover {
            background: #2e59d9;
            transform: scale(1.05);
        }

        .editable-pill-submit:active {
            transform: scale(0.95);
        }

        /* 📊 Performance Card Styling - Colorful Premium Edition */
        .perf-card {
            border-radius: 18px;
            border: none;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }
        .perf-card:hover {
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
        }
        .perf-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            opacity: 0.4;
            z-index: -1;
        }
        .perf-icon-box {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 12px;
            background: rgba(255,255,255,0.25);
            color: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .perf-value {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 3px;
            color: white;
            letter-spacing: -0.5px;
        }
        .perf-label {
            font-size: 0.72rem;
            font-weight: 700;
            color: rgba(255,255,255,0.85);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 8px;
        }
        .perf-target {
            font-size: 0.65rem;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 20px;
            background: rgba(0,0,0,0.15);
            color: white;
            display: inline-block;
            margin-top: 5px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .perf-progress {
            height: 5px;
            border-radius: 10px;
            margin-top: 15px;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.05);
        }
        .perf-progress .progress-bar {
            background-color: white !important;
            box-shadow: 0 0 8px rgba(255,255,255,0.4);
        }

        /* Color Themes */
        .perf-indigo { background: linear-gradient(135deg, #6610f2 0%, #6f42c1 100%); box-shadow: 0 8px 20px rgba(102, 16, 242, 0.25); }
        .perf-emerald { background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25); }
        .perf-cyan { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); box-shadow: 0 8px 20px rgba(6, 182, 212, 0.25); }
        .perf-amber { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 8px 20px rgba(245, 158, 11, 0.25); }
        .perf-rose { background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); box-shadow: 0 8px 20px rgba(244, 63, 94, 0.25); }
        .perf-slate { background: linear-gradient(135deg, #475569 0%, #334155 100%); box-shadow: 0 8px 20px rgba(71, 85, 105, 0.25); }
        .perf-violet { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); box-shadow: 0 8px 20px rgba(139, 92, 246, 0.25); }
        .perf-blue { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); box-shadow: 0 8px 20px rgba(59, 130, 246, 0.25); }
    </style>

    <div class="container-fluid px-4">

        @if(isset($role) && in_array($role, ['chapter', 'reseller']))
            <!-- ======================= 📊 DASHBOARD AGENT (CHAPTER & RESELLER) ======================= -->
            <h1 class="h3 mb-4 text-gray-800">Dashboard</h1>

            <!-- Month Filter -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('home') }}" class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label for="bulan" class="form-label fw-semibold">
                                Pilih Bulan Data:
                            </label>
                            <input type="month" id="bulan" name="bulan" class="form-control"
                                value="{{ $bulanStr ?? now()->format('Y-m') }}">
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-search me-1"></i> Tampilkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Navigation Tabs for Agent -->
            <ul class="nav nav-tabs mb-4 px-2" id="agentDashboardTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active premium-tab color-primary" id="penghasilan-tab-link" data-toggle="tab"
                        data-target="#penghasilan-tab" type="button" role="tab" aria-controls="penghasilan-tab"
                        aria-selected="true">
                        <i class="fas fa-chart-line"></i> Dashboard Penghasilan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link premium-tab color-success" id="dompet-tab-link" data-toggle="tab"
                        data-target="#dompet-tab" type="button" role="tab" aria-controls="dompet-tab"
                        aria-selected="false">
                        <i class="fas fa-wallet"></i> Dompet Digital
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link premium-tab color-info" id="performance-tab-link" data-toggle="tab"
                        data-target="#performance-tab" type="button" role="tab" aria-controls="performance-tab"
                        aria-selected="false">
                        <i class="fas fa-chart-bar"></i> Performance Chapter
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="agentDashboardTabsContent">
                <!-- ================== TAB 1: PENGHASILAN ================== -->
                <div class="tab-pane fade show active" id="penghasilan-tab" role="tabpanel" aria-labelledby="penghasilan-tab-link">
                    <div class="mb-5">

                <div class="row">
                    <!-- Komisi -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2 border-0 bg-white" style="border-radius: 12px; border-left: 4px solid #f6c23e !important;">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Komisi (10%)</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($komisi, 0, ',', '.') }}</div>
                                        <div class="text-xs text-muted">Closing Pribadi</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($role === 'chapter')
                        <!-- Direct Fee -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2 border-0 bg-white" style="border-radius: 12px; border-left: 4px solid #4e73df !important;">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Direct Fee</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($directFee, 0, ',', '.') }}</div>
                                            <div class="text-xs text-muted">Closing Peserta</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Royalti -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2 border-0 bg-white" style="border-radius: 12px; border-left: 4px solid #36b9cc !important;">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Royalti (5%)</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($royalti, 0, ',', '.') }}</div>
                                        <div class="text-xs text-muted">Closing Tim Reseller</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bonus Pribadi -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2 border-0 bg-white" style="border-radius: 12px; border-left: 4px solid #1cc88a !important;">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Bonus Pribadi</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($bonusPribadi, 0, ',', '.') }}</div>
                                        <div class="text-xs text-muted">Target Individu</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bonus Tim -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2 border-0 bg-white" style="border-radius: 12px; border-left: 4px solid #fd7e14 !important;">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-orange text-uppercase mb-1" style="color: #fd7e14;">Bonus Tim (10%)</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($bonusTim, 0, ',', '.') }}</div>
                                        <div class="text-xs text-muted">Target Team</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estimasi Total Penghasilan (Moved here) -->
                    <div class="{{ $role === 'chapter' ? 'col-xl-9' : 'col-xl-9' }} col-md-6 mb-4">
                        <div class="card shadow h-100 py-3 border-0 bg-primary text-white" style="border-radius: 15px; background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                            <div class="card-body d-flex align-items-center">
                                <div class="row no-gutters align-items-center w-100">
                                    <div class="col mr-2 text-center text-xl-left pl-xl-4">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: rgba(255,255,255,0.85); letter-spacing: 1px;">
                                            Estimasi Total Penghasilan ({{ $bulanLabel }})
                                        </div>
                                        <div class="h2 mb-0 font-weight-bold">
                                            Rp {{ number_format($totalPenghasilan, 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <div class="col-auto d-none d-xl-block pr-4">
                                        <i class="fas fa-coins fa-3x" style="color: rgba(255,255,255,0.25)"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                </div> <!-- End TAB 1 -->

                <!-- ================== TAB 2: DOMPET DIGITAL ================== -->
                <div class="tab-pane fade" id="dompet-tab" role="tabpanel" aria-labelledby="dompet-tab-link">
                    <div class="mb-4">

                <div class="row">
                    <!-- Saldo Tersedia -->
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card bg-gradient-success text-white shadow h-100 py-2 border-0" style="border-radius: 15px; background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: rgba(255,255,255,0.9);">Saldo Tersedia</div>
                                        <div class="h2 mb-0 font-weight-bold text-white">Rp {{ number_format($availableBalance ?? 0, 0, ',', '.') }}</div>
                                        <div class="mt-3">
                                            <button class="btn btn-light btn-sm font-weight-bold text-success px-4 shadow-sm" style="border-radius: 8px;" data-toggle="modal" data-target="#withdrawModalHome">
                                                <i class="fas fa-hand-holding-usd mr-1"></i> Tarik Saldo
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-wallet fa-3x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Saldo Dalam Proses -->
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-3 border-0 bg-white" style="border-radius: 15px; border-left: 4px solid #f6c23e !important;">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Saldo Dalam Proses Pencairan</div>
                                        <div class="h3 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($currentPending ?? 0, 0, ',', '.') }}</div>
                                        <div class="text-xs text-muted mt-2"><i class="fas fa-info-circle mr-1"></i> Menunggu persetujuan administrator</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-3x text-gray-200"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions Table -->
                <div class="card shadow mb-4" style="border-radius: 15px;">
                    <div class="card-header py-3 bg-white border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history mr-1"></i> Riwayat Transaksi</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr style="background-color: #f8faff;">
                                        <th class="border-0 py-3 text-gray-800">Tanggal</th>
                                        <th class="border-0 py-3 text-gray-800">Deskripsi</th>
                                        <th class="border-0 py-3 text-gray-800 text-center">Keterangan</th>
                                        <th class="border-0 py-3 text-gray-800 text-right">Nominal</th>
                                        <th class="border-0 py-3 text-gray-800 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($walletTransactions ?? [] as $tx)
                                    <tr>
                                        <td class="align-middle text-gray-700" style="font-size: 0.88rem;">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold text-gray-900" style="font-size: 0.88rem;">{{ $tx->source ?: ($tx->type == 'income' ? 'Pemasukan' : 'Penarikan Dana') }}</div>
                                            <div class="text-xs text-muted">{{ $tx->reference_no }}</div>
                                        </td>
                                        <td class="align-middle text-center">
                                            @if($tx->admin_note)
                                                <div class="font-weight-bold {{ $tx->status == 'rejected' ? 'text-danger' : ($tx->status == 'success' ? 'text-success' : 'text-primary') }}" style="font-size: 0.8rem; line-height: 1.3;">
                                                    {{ $tx->admin_note }}
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-right font-weight-bold {{ $tx->type == 'income' ? 'text-success' : 'text-danger' }}" style="font-size: 0.88rem;">
                                            {{ $tx->type == 'income' ? '+' : '-' }} Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="align-middle text-center">
                                            <div class="d-flex align-items-center justify-content-center">
                                                @if($tx->status == 'success')
                                                    <span class="badge badge-success px-3 py-1 shadow-sm">Berhasil</span>
                                                @elseif($tx->status == 'pending')
                                                    <span class="badge badge-warning px-3 py-1 shadow-sm">Pending</span>
                                                @else
                                                    <span class="badge badge-danger px-3 py-1 shadow-sm">Ditolak</span>
                                                @endif

                                                @if(($tx->status == 'pending' || $tx->status == 'rejected') && $tx->type == 'withdrawal')
                                                <form action="{{ route('wallet.transaction.destroy', $tx->id) }}" method="POST" class="ml-2">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-danger py-1 px-3 shadow-sm font-weight-bold btn-cancel-withdrawal" style="font-size: 0.65rem; border-radius: 20px;" title="Batalkan Pengajuan">
                                                        Batalkan
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-receipt fa-2x mb-3 d-block opacity-50"></i>
                                            Belum ada transaksi di bulan ini.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Withdraw Modal (Premium Redesign) -->
            <div class="modal fade" id="withdrawModalHome" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 650px;">
                    <form id="withdrawFormHome" action="{{ route('wallet.withdraw') }}" method="POST">
                        @csrf
                        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                            <div class="modal-header border-0 bg-primary text-white py-3 px-4">
                                <h5 class="modal-title font-weight-bold" style="font-size: 1.2rem; letter-spacing: 0.5px;">
                                    <i class="fas fa-hand-holding-usd mr-2"></i> Ajukan Penarikan Saldo
                                </h5>
                                <button type="button" class="close text-white opacity-100" data-dismiss="modal" aria-label="Close" style="text-shadow: none;">
                                    <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body p-4 bg-white">
                                <!-- Top Alert Warning -->
                                <div id="alertMinimalHome" class="alert alert-danger border-left-danger shadow-sm d-flex align-items-center mb-4 py-3 d-none" 
                                     style="border-radius: 10px; background-color: #fff5f5; border-left: 5px solid #e74a3b !important; color: #c0392b;">
                                    <i class="fas fa-exclamation-circle mr-3 fa-lg"></i>
                                    <span class="font-weight-bold small">Nominal minimal penarikan adalah Rp 100.000</span>
                                </div>

                                <div class="row">
                                    <div class="col-md-5 mb-3">
                                        <label class="font-weight-bold text-gray-700 small mb-2 d-flex align-items-center">
                                            <i class="fas fa-coins text-warning mr-2"></i> Nominal Penarikan
                                        </label>
                                        <div class="input-group mb-2 shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text font-weight-bold border-0" 
                                                    style="background: #f0f4ff; color: #4e73df; font-size: 1rem; width: 45px; justify-content: center;">Rp</span>
                                            </div>
                                            <input type="text" id="withdrawAmountDisplayHome" class="form-control font-weight-bold border-0" 
                                                placeholder="0" required 
                                                style="font-size: 1.1rem; height: 45px; background: #fff;">
                                            <input type="hidden" id="withdrawAmountHome" name="amount">
                                        </div>
                                        <div class="d-flex align-items-center text-secondary mt-1 ml-1">
                                            <i class="fas fa-wallet mr-2" style="font-size: 0.9rem; opacity: 0.7;"></i>
                                            <span class="small font-weight-bold">Tersedia: <span class="text-dark">Rp {{ number_format($availableBalance ?? 0, 0, ',', '.') }}</span></span>
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <label class="font-weight-bold text-gray-700 small mb-2 d-flex align-items-center">
                                            <i class="fas fa-university text-primary mr-2"></i> Informasi Rekening
                                        </label>
                                        <div class="form-group mb-3 shadow-sm rounded">
                                            <input type="text" name="bank_name" class="form-control form-control-sm border-light" 
                                                placeholder="Nama Bank (Contoh: BCA, Mandiri)" value="{{ $savedBankName ?? '' }}" 
                                                required style="height: 40px; border-radius: 8px;">
                                        </div>
                                        <div class="form-group mb-3 shadow-sm rounded">
                                            <input type="text" name="account_number" class="form-control form-control-sm border-light" 
                                                placeholder="Nomor Rekening" value="{{ $savedAccountNumber ?? '' }}" 
                                                required style="height: 40px; border-radius: 8px;">
                                        </div>
                                        <div class="form-group mb-0 shadow-sm rounded">
                                            <input type="text" name="account_name" class="form-control form-control-sm border-light" 
                                                placeholder="Nama Pemilik Rekening" value="{{ $savedAccountName ?? '' }}" 
                                                required style="height: 40px; border-radius: 8px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 bg-white p-4 pt-0 d-flex justify-content-end align-items-center">
                                <button type="button" class="btn btn-link text-dark font-weight-bold mr-4 text-decoration-none" data-dismiss="modal">
                                    <i class="fas fa-times mr-1"></i> Batal
                                </button>
                                <button type="submit" class="btn btn-primary px-4 py-2 font-weight-bold shadow" 
                                    style="border-radius: 12px; background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); border: none; height: 45px; min-width: 180px;">
                                    <i class="fas fa-paper-plane mr-2"></i> Kirim Pengajuan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            </div> <!-- End TAB 2 -->

            <!-- ================== TAB 3: PERFORMANCE CHAPTER ================== -->
            <div class="tab-pane fade p-4" id="performance-tab" role="tabpanel" aria-labelledby="performance-tab-link" 
                 style="background: #f6f9ff; border-radius: 25px; box-shadow: inset 0 0 20px rgba(0,0,0,0.03); border: 1px solid #eef2f8;">
                <div class="mb-4">
                    <h4 class="font-weight-bold text-gray-800"><i class="fas fa-chart-line mr-2 text-primary"></i> Analisis Performa Chapter</h4>
                    <p class="text-muted small">Pantau metrik utama pertumbuhan dan keberhasilan event chapter Anda secara real-time.</p>
                </div>
                <div class="row g-4 mb-5">
                    <!-- 1. Jumlah Peserta Open House (Traffic) -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card perf-card perf-indigo h-100 p-3">
                            <div class="perf-icon-box">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="perf-label">Traffic Open House</div>
                            <div class="perf-value">{{ number_format($trafficOH) }}</div>
                            <div class="perf-target">Target: 30-50 / Event</div>
                            <div class="perf-progress progress">
                                <div class="progress-bar" role="progressbar" style="width: {{ min(100, ($trafficOH/40)*100) }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Persentase Peserta Berkualitas -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card perf-card perf-emerald h-100 p-3">
                            <div class="perf-icon-box">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="perf-label">Qualified Leads</div>
                            <div class="perf-value">{{ $qualifiedRate }}%</div>
                            <div class="perf-target">Target: ≥ 60% (Hot+Warm)</div>
                            <div class="perf-progress progress">
                                <div class="progress-bar" role="progressbar" style="width: {{ $qualifiedRate }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Conversion Rate Open House -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card perf-card perf-cyan h-100 p-3">
                            <div class="perf-icon-box">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div class="perf-label">Closing Rate (OH)</div>
                            <div class="perf-value">{{ $closingRateOH }}%</div>
                            <div class="perf-target">Target: ≥ 30% On The Spot</div>
                            <div class="perf-progress progress">
                                <div class="progress-bar" role="progressbar" style="width: {{ $closingRateOH }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Total Closing per Event -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card perf-card perf-amber h-100 p-3">
                            <div class="perf-icon-box">
                                <i class="fas fa-cart-arrow-down"></i>
                            </div>
                            <div class="perf-label">Closing per Event</div>
                            <div class="perf-value">{{ $avgClosingPerEvent }}</div>
                            <div class="perf-target">Target: 8-15 / Event</div>
                            <div class="perf-progress progress">
                                <div class="progress-bar" role="progressbar" style="width: {{ min(100, ($avgClosingPerEvent/12)*100) }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Revenue per Event -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card perf-card perf-rose h-100 p-3">
                            <div class="perf-icon-box">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="perf-label">Revenue per Event</div>
                            <div class="perf-value" style="font-size: 1.4rem;">Rp {{ number_format($avgRevenuePerEvent/1000000, 1) }}M</div>
                            <div class="perf-target">Target: ≥ 12M - 20M</div>
                            <div class="perf-progress progress">
                                <div class="progress-bar" role="progressbar" style="width: {{ min(100, ($avgRevenuePerEvent/15000000)*100) }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- 6. Jumlah Member Aktif M1T -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card perf-card perf-slate h-100 p-3">
                            <div class="perf-icon-box">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="perf-label">Member Aktif M1T</div>
                            <div class="perf-value">{{ number_format($totalMemberAktif) }}</div>
                            <div class="perf-target">Target: Growth Stabil</div>
                            <div class="perf-progress progress">
                                <div class="progress-bar" role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- 7. Retention Rate -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card perf-card perf-violet h-100 p-3">
                            <div class="perf-icon-box">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="perf-label">Retention Rate</div>
                            <div class="perf-value">{{ $retentionRate }}%</div>
                            <div class="perf-target">Target: ≥ 70% (12 Mo)</div>
                            <div class="perf-progress progress">
                                <div class="progress-bar" role="progressbar" style="width: {{ $retentionRate }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- 8. Frekuensi Event -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card perf-card perf-blue h-100 p-3">
                            <div class="perf-icon-box">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="perf-label">Frekuensi Event</div>
                            <div class="perf-value">{{ $eventCount }}</div>
                            <div class="perf-target">Target: 2-4 / Bulan</div>
                            <div class="perf-progress progress">
                                <div class="progress-bar" role="progressbar" style="width: {{ min(100, ($eventCount/3)*100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- End TAB 3 -->
        </div> <!-- End tab-content -->

            </div>
        </div>



        @else
            {{-- ALERT MODE READ ONLY (ADMIN) --}}
            @if(isset($user) && ($readonly ?? false))
                <div class="alert alert-info d-flex align-items-center justify-content-between mb-4 shadow-sm" role="alert">
                    <div>
                        <strong>Dashboard CS:</strong> <strong>{{ $user->name }} </strong> <br>
                        <span class="text-muted small">Email: {{ $user->email }} | Role: {{ ucfirst($user->role) }}</span>
                    </div>
                    <div>
                        <span class="text-white badge bg-primary p-2">Mode Read-Only</span>
                    </div>
                </div>
            @endif

            {{-- ✨ KOMENTAR ADMIN KE CS ✨ --}}
            @if(isset($user) && $readonly)
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-warning text-dark fw-bold">
                        <i class="fas fa-comments me-2"></i> Komentar untuk {{ $user->name }}
                    </div>
                    <div class="card-body">
                        {{-- Form Kirim Komentar --}}
                        <form id="formKomentar" method="POST" action="{{ route('komentar.store') }}">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                            <div class="input-group mb-3">
                                <input type="text" name="pesan" class="form-control" placeholder="Tulis komentar untuk CS ini..."
                                    required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Kirim
                                </button>
                            </div>
                        </form>
                        @if(session('success'))
                            <script>
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: '{{ session('success') }}',
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            </script>
                        @endif


                        <button class="btn btn-outline-secondary btn-sm mb-2" data-toggle="modal" data-target="#modalKomentar">
                            <i class="fas fa-history"></i> Lihat Riwayat Komentar
                        </button>

                        <div class="modal fade" id="modalKomentar" tabindex="-1" role="dialog" aria-labelledby="modalKomentarLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-scrollable" role="document">
                                <div class="modal-content">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title" id="modalKomentarLabel">
                                            <i class="fas fa-comments me-2"></i> Riwayat Komentar
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        @foreach($komentar as $msg)
                                            <div class="alert alert-light border d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <strong>{{ $msg->admin->name ?? 'Admin' }}</strong><br>
                                                    <span class="text-dark">{{ $msg->pesan }}</span><br>
                                                    <small class="text-muted">{{ $msg->created_at->diffForHumans() }}</small>
                                                </div>
                                                <i class="fas fa-comment-dots text-warning"></i>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            @endif

            @php
                $bulanDipilih = request('bulan', now()->format('Y-m'));
                $bulanParse = \Carbon\Carbon::parse($bulanDipilih . '-01');
                $namaBulan = $bulanParse->translatedFormat('F');
                $tahun = $bulanParse->year;

                $jadwalKelas = \App\Models\Kelas::whereYear('tanggal_mulai', $tahun)
                    ->whereMonth('tanggal_mulai', $bulanParse->month)
                    ->pluck('tanggal_mulai', 'nama_kelas')
                    ->toArray();
            @endphp

            <!-- Popup Motivasi HTML -->
            <div id="popupOverlay" onclick="tutupMotivasi()"></div>
            <div id="motivasiPopup">
                <h4 class="fw-bold text-primary mb-3">🌟 Motivasi Hari Ini</h4>
                <p id="motivasiText" class="fs-5 text-dark" style="font-style: italic;"></p>
                <button class="btn btn-primary mt-3 px-4" onclick="tutupMotivasi()">Semangat! 🚀</button>
            </div>

            <!-- Month Filter Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('home') }}" class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label for="bulan" class="form-label fw-semibold">
                                Pilih Bulan Kelas:
                            </label>
                            <input type="month" id="bulan" name="bulan" class="form-control" value="{{ $bulanDipilih }}">
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-search me-1"></i> Tampilkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs mb-4 px-2" id="dashboardTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active premium-tab color-primary" id="dashboard-tab-link" data-toggle="tab"
                        data-target="#dashboard-tab" type="button" role="tab" aria-controls="dashboard-tab"
                        aria-selected="true">
                        <i class="fas fa-tachometer-alt"></i> Dashboard Panel 1
                    </button>
                </li>
                @if($role !== 'chapter' && $role !== 'reseller')
                    <li class="nav-item" role="presentation">
                        <button class="nav-link premium-tab color-success" id="performance-tab-link" data-toggle="tab"
                            data-target="#performance-tab" type="button" role="tab" aria-controls="performance-tab"
                            aria-selected="false">
                            <i class="fas fa-star"></i> Penilaian Kinerja Saya
                        </button>
                    </li>
                @endif
            </ul>

            <div class="tab-content" id="dashboardTabsContent">
                <!-- ================== TAB 1: DASHBOARD ================== -->
                <div class="tab-pane fade show active" id="dashboard-tab" role="tabpanel" aria-labelledby="dashboard-tab-link">

                    {{-- ================== OMSET PER KELAS ================== --}}
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-success text-white fw-bold">
                            OMSET KELAS ({{ strtoupper($namaBulan) }} {{ $tahun }})
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-bordered table-hover text-center align-middle">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Nama Kelas</th>
                                        @if($role !== 'reseller')
                                            <th>Tanggal</th>
                                        @endif
                                        <th>Omset</th>
                                        @if($role === 'reseller')
                                            <th>Royalti</th>
                                            <th>Komisi</th>
                                            <th>Bonus Pribadi</th>
                                            <th>Bonus Tim</th>
                                        @else
                                            <th>Target</th>
                                            <th>% Tercapai</th>
                                            <th>Insentif</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($kelasOmsetFiltered as $k)
                                        @php
                                            $persen = $k['target'] > 0 ? round(($k['omset'] / $k['target']) * 100, 2) : 0;
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold">
                                                {{ $k['nama_kelas'] == 'Start-Up Muslim Indonesia' ? 'M1T' : $k['nama_kelas'] }}
                                            </td>
                                            @if($role !== 'reseller')
                                                <td>{{ $jadwalKelas[$k['nama_kelas']] ?? '-' }}</td>
                                            @endif
                                            <td class="text-success font-weight-bold">
                                                @if(isset($k['is_manual']) && $k['is_manual'])
                                                    <form action="{{ route('pendapatan.lainnya.store') }}" method="POST" class="d-flex align-items-center justify-content-center">
                                                        @csrf
                                                        <input type="hidden" name="bulan" value="{{ $bulanNum }}">
                                                        <input type="hidden" name="tahun" value="{{ $tahun }}">
                                                        
                                                        <div class="editable-pill-container">
                                                            <span class="editable-pill-label">Rp</span>
                                                            <input type="number" name="omset" value="{{ (int)$k['omset'] }}" 
                                                                class="editable-pill-input" 
                                                                onfocus="this.select()"
                                                                onchange="this.form.submit()">
                                                            <button type="submit" class="editable-pill-submit" title="Simpan Perubahan">
                                                                <i class="fas fa-check fa-xs"></i>
                                                            </button>
                                                        </div>
                                                    </form>
                                                @else
                                                    Rp {{ number_format($k['omset'], 0, ',', '.') }}
                                                @endif
                                            </td>
                                            @if($role === 'reseller')
                                                <td class="text-info fw-bold">Rp {{ number_format($k['royalti'] ?? 0, 0, ',', '.') }}
                                                </td>
                                                <td class="text-primary fw-bold">Rp {{ number_format($k['komisi'] ?? 0, 0, ',', '.') }}
                                                </td>
                                                <td class="text-success fw-bold">Rp
                                                    {{ number_format($k['bonus_pribadi'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="text-warning fw-bold">Rp
                                                    {{ number_format($k['bonus_tim'] ?? 0, 0, ',', '.') }}</td>
                                            @else
                                                @php
                                                    $persen = $k['target'] > 0 ? round(($k['omset'] / $k['target']) * 100, 2) : 0;
                                                @endphp
                                                <td>Rp {{ number_format($k['target'], 0, ',', '.') }}</td>
                                                <td
                                                    class="text-{{ $persen >= 100 ? 'success' : ($persen >= 75 ? 'warning' : 'danger') }} fw-bold">
                                                    {{ $persen }}%
                                                </td>
                                                <td class="text-primary fw-bold">
                                                    Rp {{ number_format($k['komisi'] ?? 0, 0, ',', '.') }}
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-muted fst-italic">Tidak ada data untuk bulan ini</td>
                                        </tr>
                                    @endforelse
                                </tbody>

                                @php
                                    $totalOmset = $kelasOmsetFiltered->sum('omset');
                                    $targetBulanan = \App\Models\Setting::where('key', 'target_omset')->value('value') ?? 50000000;
                                    $persenTercapai = $targetBulanan > 0 ? round(($totalOmset / $targetBulanan) * 100, 2) : 0;

                                    // 🔹 Logika Reward Bulanan (nilai numerik + teks)
                                    if ($persenTercapai >= 100) {
                                        $rewardBulanan = 600000;
                                        $reward = "Rp " . number_format($rewardBulanan, 0, ',', '.');
                                        $keterangan = "🏆 Luar biasa! Anda mencapai 100%! Terus pertahankan performa hebat ini!";
                                    } elseif ($persenTercapai >= 90) {
                                        $rewardBulanan = 500000;
                                        $reward = "Rp " . number_format($rewardBulanan, 0, ',', '.');
                                        $keterangan = "🔥 Hampir sempurna! Tingkatkan performa sedikit lagi untuk mencapai 100%!";
                                    } elseif ($persenTercapai >= 50) {
                                        $rewardBulanan = 300000;
                                        $reward = "Rp " . number_format($rewardBulanan, 0, ',', '.');
                                        $keterangan = "💪 Performa Anda bagus! Ayo semangat, masih bisa ditingkatkan!";
                                    } else {
                                        $rewardBulanan = 0;
                                        $reward = "-";
                                        $keterangan = "😔 Mohon maaf, Anda belum mendapat reward. Tetap semangat untuk bulan depan!";
                                    }
                                @endphp

                                <tfoot>
                                    @if($role !== 'reseller')
                                        <tr class="bg-light fw-bold">
                                            <td colspan="3" class="text-end text-dark">Total Omset</td>
                                            <td colspan="3" class="text-start text-success">
                                                Rp {{ number_format($totalOmset, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        <tr class="bg-light fw-bold">
                                            <td colspan="3" class="text-end text-dark">Target Omset Bulanan</td>
                                            <td colspan="3" class="text-start text-dark">
                                                Rp {{ number_format($targetBulanan, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        <tr class="bg-light fw-bold">
                                            <td colspan="3" class="text-end text-dark">Persentase Tercapai</td>
                                            <td colspan="3"
                                                class="text-start {{ $persenTercapai >= 100 ? 'text-success' : ($persenTercapai >= 75 ? 'text-warning' : 'text-danger') }}">
                                                {{ $persenTercapai }}%
                                            </td>
                                        </tr>
                                        <tr class="bg-light fw-bold">
                                            <td colspan="3" class="text-end text-dark">Reward Bulanan</td>
                                            <td colspan="3" class="text-start text-success">
                                                {{ $reward }}
                                            </td>
                                        </tr>
                                        <tr class="bg-light fw-bold">
                                            <td colspan="3" class="text-end text-dark">Reminder</td>
                                            <td colspan="3" class="text-start">
                                                <div class="reminder-cell">
                                                    <i class="fa-solid fa-bell reminder-icon"></i>
                                                    {{ $keterangan }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- ================== CARD DATABASE & KOMISI ================== --}}
                    @if($role !== 'chapter' && $role !== 'reseller')
                        @php
                            $namaUserData = isset($user) && $readonly ? $user->name : auth()->user()->name;
                            $target = 50;
                        @endphp

                        <div class="row g-4 mb-4">
                            {{-- Card Database --}}
                            <div class="col-12 col-md-4">
                                <div class="card shadow-lg border-0 h-100">
                                    <div class="card-header bg-info text-white fw-bold py-2">
                                        <i class="fas fa-database me-2"></i> JUMLAH DATABASE
                                    </div>
                                    <div class="card-body text-center">
                                        <h2 class="fw-bold text-dark mb-2" style="font-size: 2.5rem;">{{ $databaseBaru }}</h2>
                                        <p class="text-muted mb-3">Periode: {{ $bulanParse->translatedFormat('F') }}
                                            {{ $bulanParse->year }}
                                        </p>
                                        <div class="progress mb-3" style="height: 18px; border-radius: 10px;">
                                            <div class="progress-bar bg-success fw-bold text-white" role="progressbar"
                                                style="width: {{ min(($databaseBaru / $target) * 100, 100) }}%">
                                                {{ number_format(($databaseBaru / $target) * 100, 0) }}%
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="badge bg-primary text-white px-3 py-2">Target: {{ $target }}</span>
                                            <span class="badge bg-secondary text-white px-3 py-2">Total: {{ $databaseTotal }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Card Pie Chart --}}
                            <div class="col-12 col-md-4">
                                <div class="card shadow-lg border-0 h-100">
                                    <div class="card-header bg-primary text-white fw-bold py-2">
                                        <i class="fas fa-chart-pie me-2"></i> SUMBER LEADS
                                    </div>
                                    <div class="card-body d-flex justify-content-center align-items-center">
                                        <canvas id="pieSumberDbSmall" width="200" height="200"></canvas>
                                    </div>
                                </div>
                            </div>

                            {{-- Card Total Komisi + Reward --}}
                            <div class="col-12 col-md-4">
                                <div class="card shadow-lg border-0 h-100">
                                    <div class="card-header bg-secondary text-white fw-bold py-2">
                                        <i class="fas fa-file-invoice-dollar me-2"></i> TOTAL KOMISI + REWARD
                                    </div>
                                    <div class="card-body text-center">
                                        @php
                                            $totalDenganReward = $totalKomisi + $rewardBulanan;
                                        @endphp

                                        <h2 class="fw-bold text-success mb-2" style="font-size: 2.5rem;">
                                            Rp {{ number_format($totalDenganReward, 0, ',', '.') }}
                                        </h2>

                                        <p class="text-muted mb-1">Komisi: Rp {{ number_format($totalKomisi, 0, ',', '.') }}</p>
                                        <p class="text-muted mb-1">Reward: Rp {{ number_format($rewardBulanan, 0, ',', '.') }}</p>

                                        <hr>
                                        <p class="text-muted mb-0">Periode: {{ $namaBulan }} {{ $tahun }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================== PIE CHART SCRIPT ================== --}}
                        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                        <script>
                            const ctxSmall = document.getElementById('pieSumberDbSmall').getContext('2d');
                            new Chart(ctxSmall, {
                                type: 'pie',
                                data: {
                                    labels: @json($labels),
                                    datasets: [{
                                        data: @json($values),
                                        backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#17a2b8', '#fd7e14', '#20c997', '#6610f2', '#e83e8c'],
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            position: 'bottom',
                                            labels: { font: { size: 12 } }
                                        }
                                    }
                                }
                            });
                        </script>
                    @endif


                    {{-- ================== KPI BULANAN ================== --}}
                    @if($role !== 'chapter' && $role !== 'reseller')
                        <div class="card shadow-lg border-0 mt-5 mb-5">
                            <div class="card-header bg-primary text-white text-center fw-bold fs-5">
                                PENILAIAN AKTIVITAS (CS MBC)
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-bordered table-striped table-hover mb-0 text-center align-middle">
                                    <thead class="table-info">
                                        <tr>
                                            <th>No</th>
                                            <th>Aktivitas</th>
                                            <th>Target</th>
                                            <th>Bobot</th>
                                            <th>Presentase</th>
                                            <th>Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($kpiData as $i => $row)
                                            <tr>
                                                <td class="fw-bold text-dark">{{ $i + 1 }}</td>
                                                <td class="fw-bold text-start text-dark">{{ $row['nama'] }}</td>
                                                <td class="fw-bold text-dark">{{ $row['target'] }}</td>
                                                <td class="fw-bold text-dark">{{ $row['bobot'] }}</td>
                                                <td class="fw-bold text-dark">{{ $row['persentase'] }}%</td>
                                                <td class="fw-bold text-dark">{{ number_format($row['nilai'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-success fw-bold fs-6">
                                            <td colspan="3" class="text-center text-dark fw-bold">TOTAL</td>
                                            <td class="text-dark fw-bold">{{ $totalBobot }}</td>
                                            <td>—</td>
                                            <td class="text-dark fw-bold">{{ number_format($totalNilai, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- ================== TAB 2: PENILAIAN KINERJA SAYA ================== -->
                @if($role !== 'chapter' && $role !== 'reseller')
                    <div class="tab-pane fade" id="performance-tab" role="tabpanel" aria-labelledby="performance-tab-link">


                        <div class="container-fluid mt-4">

                            {{-- JUDUL --}}
                            <div class="text-center mb-3">
                                <h3 class="fw-bold" style="color: #5a5c69;">Penilaian Hasil CS</h3>
                            </div>


                            {{-- PROGRESS BAR TOTAL PENCAPAIAN --}}
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-body">
                                    <h5 class="fw-bold text-secondary mb-2">Total Pencapaian: {{ $totalNilaiHasil ?? 0 }}/100</h5>
                                    <div class="progress" style="height: 25px; background-color: #e9ecef; border-radius: 5px;">
                                        <div class="progress-bar fw-bold" role="progressbar"
                                            style="width: {{ $totalNilaiHasil ?? 0 }}%; background-color: #dc3545; font-size: 14px;"
                                            aria-valuenow="{{ $totalNilaiHasil ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                                            {{ $totalNilaiHasil ?? 0 }}%
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- TABEL PENILAIAN UTAMA --}}
                            <div class="card shadow border-0 mb-4">
                                <div class="card-header text-white text-center fw-bold" style="background-color: #00c0ef;">
                                    PENILAIAN HASIL cs
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-bordered mb-0 text-center align-middle">
                                        <thead style="background-color: #ffed8b;">
                                            <tr>
                                                <th>No</th>
                                                <th>Aspek Kinerja</th>
                                                <th>Indikator</th>
                                                <th>Bobot</th>
                                                <th>Pencapaian</th>
                                                <th>Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- 1. Penjualan & Omset --}}
                                            <tr>
                                                <td>1</td>
                                                <td class="text-start">Penjualan & Omset</td>
                                                <td class="text-start">Target Rp 50 juta/bulan</td>
                                                <td>40%</td>
                                                <td>Rp {{ number_format($totalOmset ?? 0, 0, ',', '.') }}</td>
                                                <td>{{ $nilaiOmset ?? 0 }}</td>
                                            </tr>
                                            {{-- 2. Closing Paket --}}
                                            <tr>
                                                <td>2</td>
                                                <td class="text-start">Closing Paket</td>
                                                <td class="text-start">Target 1 closing paket per bulan</td>
                                                <td>10%</td>
                                                <td>{{ $closingPaket ?? 0 }} peserta</td>
                                                <td>{{ $nilaiClosingPaket ?? 0 }}</td>
                                            </tr>
                                            {{-- 3. Database Baru --}}
                                            <tr>
                                                <td>3</td>
                                                <td class="text-start">Database Baru</td>
                                                <td class="text-start">Target 50 database baru</td>
                                                <td>10%</td>
                                                <td>{{ $databaseBaru ?? 0 }}</td>
                                                <td>{{ $nilaiDatabaseBaru ?? 0 }}</td>
                                            </tr>
                                            {{-- 4. Penilaian Atasan --}}
                                            @php
                                                $manualSum = isset($manual) ? ($manual->kerajinan + $manual->kerjasama + $manual->tanggung_jawab + $manual->inisiatif + $manual->komunikasi) : 0;
                                            @endphp
                                            <tr>
                                                <td>4</td>
                                                <td class="text-start">Penilaian Atasan</td>
                                                <td class="text-start">Total Skor Kualitatif (Max 500)</td>
                                                <td>20%</td>
                                                <td>{{ $manualSum }}</td>
                                                <td>{{ $nilaiManualPart ?? 0 }}</td>
                                            </tr>
                                            {{-- 5. Intake --}}
                                            <tr>
                                                <td>5</td>
                                                <td class="text-start">Intake</td>
                                                <td class="text-start">Rekap Keseluruhan Daily Activity</td>
                                                <td>20%</td>
                                                <td>{{ number_format($skorDaily ?? 0, 2) }}%</td>
                                                <td>{{ $nilaiIntakePart ?? 0 }}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-success fw-bold">
                                                <td colspan="5" class="text-end">TOTAL NILAI</td>
                                                <td>{{ $totalNilaiHasil ?? 0 }}</td>
                                            </tr>
                                        </tfoot>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            {{-- STATUS BOX & LEGEND --}}
                            <div class="card shadow border-0 p-4 mb-4">

                                {{-- Dinamic Status Box --}}
                                <div id="statusBoxContainer" class="p-3 text-center text-white fw-bold fs-4 mb-3"
                                    style="border-radius: 5px; background-color: #dc3545;">
                                    Underperformance ({{ $totalNilaiHasil ?? 0 }})
                                </div>

                                {{-- Motivasi Text --}}
                                <div class="d-flex align-items-start mb-4">
                                    <i class="fas fa-comment-dots fa-lg me-2 mt-1" style="color: #aaa;"></i>
                                    <em id="motivasiTextInline" style="color: #555;">
                                        Ayo bangkit! Kamu belum terlambat untuk mengejar.
                                    </em>
                                </div>

                                <h5 class="fw-bold mb-3">Keterangan Skala Nilai</h5>
                                <div class="table-responsive">
                                    <table class="table text-center text-white fw-bold mb-0">
                                        <thead style="background-color: #2c3e50;">
                                            <tr>
                                                <th>Rentang Nilai</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr style="background-color: #008000;">
                                                <td>> 100</td>
                                                <td>Sangat Baik</td>
                                            </tr>
                                            <tr style="background-color: #00ca00;">
                                                <td>80 – 99</td>
                                                <td>Baik</td>
                                            </tr>
                                            <tr style="background-color: #ffe600; color: #333;">
                                                <td>60 – 79</td>
                                                <td>Cukup</td>
                                            </tr>
                                            <tr style="background-color: #ff9900;">
                                                <td>40 – 59</td>
                                                <td>Pembinaan</td>
                                            </tr>
                                            <tr style="background-color: #dc3545;">
                                                <td>
                                                    < 40</td>
                                                <td>Underperformance</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- HISTORY SECTION --}}
                            <h4 class="fw-bold text-secondary mb-3">G. HISTORY KINERJA PER BULAN</h4>

                            <div class="d-flex overflow-auto pb-3" style="gap: 15px;">
                                @foreach(range(1, 12) as $m)
                                    @php
                                        $hVal = $historyNilai[$m] ?? 0;
                                        // Tentukan warna bar kecil
                                        if ($hVal > 100)
                                            $cBar = '#008000';
                                        elseif ($hVal >= 80)
                                            $cBar = '#00ca00';
                                        elseif ($hVal >= 60)
                                            $cBar = '#ffe600';
                                        elseif ($hVal >= 40)
                                            $cBar = '#ff9900';
                                        elseif ($hVal > 0)
                                            $cBar = '#dc3545';
                                        else
                                            $cBar = '#e9ecef';
                                    @endphp
                                    <div class="card shadow-sm border text-center" style="min-width: 100px;">
                                        <div class="card-body p-2">
                                            <div class="fw-bold text-secondary mb-2" style="font-size: 14px;">
                                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('M') }}
                                            </div>
                                            <div class="w-100 rounded mb-2" style="height: 6px; background-color: #eee;">
                                                <div class="h-100 rounded" style="width: 100%; background-color: {{ $cBar }};"></div>
                                            </div>
                                            <div class="fw-bold text-dark">{{ $hVal }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        </div>

                        {{-- Script to update Status Box dynamically based on Total Nilai --}}
                        <script>
                            document.addEventListener("DOMContentLoaded", function () {
                                let total = {{ $totalNilaiHasil ?? 0 }};
                                let box = document.getElementById('statusBoxContainer');
                                let quote = document.getElementById('motivasiTextInline');
                                let bar = document.querySelector('.progress-bar');

                                let bg = '#dc3545'; // Default Red
                                let label = 'Underperformance';
                                let text = 'Ayo bangkit! Kamu belum terlambat untuk mengejar.';

                                if (total > 100) {
                                    bg = '#008000'; label = 'Sangat Baik';
                                    text = 'Luar biasa! Konsistensi kinerjamu sangat menginspirasi!';
                                } else if (total >= 80) {
                                    bg = '#00ca00'; label = 'Baik';
                                    text = 'Kerja bagus! Tinggal sedikit lagi untuk mencapai level terbaik.';
                                } else if (total >= 60) {
                                    bg = '#ffe600'; label = 'Cukup';
                                    text = 'Cukup baik, tapi masih banyak ruang untuk berkembang.';
                                } else if (total >= 40) {
                                    bg = '#ff9900'; label = 'Pembinaan';
                                    text = 'Jangan menyerah, ini saatnya bangkit!';
                                }

                                if (box) {
                                    box.style.backgroundColor = bg;
                                    box.innerText = label + ' (' + total + ')';
                                    if (total >= 60 && total < 80) box.style.color = '#333'; // Dark text for yellow
                                }
                                if (quote) quote.innerText = text;
                                if (bar) {
                                    bar.style.backgroundColor = bg;
                                    if (total >= 60 && total < 80) bar.style.color = '#333';
                                }
                            });
                        </script>
                    </div>
                @endif
            </div>

            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/css/bootstrap.min.css">
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>


            <style>
                #kategoriBox.pulse {
                    animation: pulseBox 1.2s infinite;
                }

                @keyframes pulseBox {
                    0% {
                        transform: scale(1);
                    }

                    50% {
                        transform: scale(1.04);
                    }

                    100% {
                        transform: scale(1);
                    }
                }
            </style>

            <script>
                // Ambil total nilai dari backend
                let totalNilaiHasil = {{ $totalNilaiHasil ?? 0 }};

                // Elemen target
                const box = document.getElementById("kategoriBox");
                const motivasi = document.getElementById("motivasiBox");

                // =============================
                // SKALA & MOTIVASI
                // =============================
                const kategori = [
                    {
                        min: 100, label: "Sangat Baik", bg: "#d1f7d3", border: "#8edb92", color: "#155724",
                        motivasi: ["Luar biasa! Konsistensi kinerjamu sangat menginspirasi!"]
                    },
                    {
                        min: 80, label: "Baik", bg: "#e9ffd6", border: "#c8eca2", color: "#35630a",
                        motivasi: ["Kerja bagus! Tinggal sedikit lagi untuk mencapai level terbaik."]
                    },
                    {
                        min: 60, label: "Cukup", bg: "#fff7d1", border: "#f0dc8a", color: "#8a6d00",
                        motivasi: ["Cukup baik, tapi masih banyak ruang untuk berkembang."]
                    },
                    {
                        min: 40, label: "Pembinaan", bg: "#ffe4d1", border: "#f3b693", color: "#7a2f00",
                        motivasi: ["Jangan menyerah, ini saatnya bangkit!"]
                    },
                    {
                        min: 0, label: "Underperformance", bg: "#fcd2d0", border: "#e39a96", color: "#811d1a",
                        motivasi: ["Ayo bangkit! Kamu belum terlambat untuk mengejar."]
                    }
                ];

                if (box && motivasi) {
                    let hasil = kategori.find(k => totalNilaiHasil >= k.min) || kategori[kategori.length - 1];

                    box.style.background = hasil.bg;
                    box.style.borderColor = hasil.border;
                    box.style.color = hasil.color;
                    box.innerHTML = `${hasil.label} (${totalNilaiHasil})`;

                    if (hasil.label === "Pembinaan" || hasil.label === "Underperformance") {
                        box.classList.add("pulse");
                    }

                    motivasi.innerHTML = `
                <p style="padding:12px; border-left:5px solid ${hasil.color}">
                    💬 <em>${hasil.motivasi[0]}</em>
                </p>
            `;
                }

                // === POPUP MOTIVASI LOGIC ===
                const motivasiQuotes = [
                    "Kerja kerasmu hari ini adalah kesuksesanmu besok!",
                    "Tetap fokus, kamu sudah sangat dekat dengan target!",
                    "Percaya proses, hasil terbaik sedang menunggumu!",
                    "Sedikit lagi! Kamu pasti bisa!",
                    "Lakukan yang terbaik, Tuhan yang menyempurnakan!",
                    "Jangan menyerah, kegagalan adalah awal dari keberhasilan!",
                    "Setiap langkah kecil membawamu lebih dekat ke tujuan.",
                    "Jadilah versi terbaik dari dirimu setiap hari.",
                    "Tantangan adalah peluang untuk tumbuh.",
                    "Sukses tidak datang dari apa yang kamu lakukan sesekali, tapi apa yang kamu lakukan secara konsisten."
                ];

                function tampilMotivasi() {
                    // Pilih quote acak
                    const quote = motivasiQuotes[Math.floor(Math.random() * motivasiQuotes.length)];
                    const motivasiTextElement = document.getElementById('motivasiText');

                    if (motivasiTextElement) {
                        motivasiTextElement.innerText = '"' + quote + '"';

                        document.getElementById('popupOverlay').style.display = 'block';
                        document.getElementById('motivasiPopup').style.display = 'block';
                    }
                }

                function tutupMotivasi() {
                    document.getElementById('popupOverlay').style.display = 'none';
                    document.getElementById('motivasiPopup').style.display = 'none';
                }

                // Muncul otomatis setelah 1.5 detik hanya jika belum muncul di sesi ini
                if (!sessionStorage.getItem('motivasi_shown')) {
                    setTimeout(tampilMotivasi, 1500);
                    sessionStorage.setItem('motivasi_shown', 'true');
                }

            </script>
        @endif
    </div>
@push('scripts')
<script>
    // SweetAlert untuk pembatalan penarikan
    $(document).on('click', '.btn-cancel-withdrawal', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        Swal.fire({
            title: 'Batalkan Pengajuan?',
            text: "Apakah Anda yakin ingin membatalkan pengajuan penarikan ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Kembali',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Formatting Ribuan (Thousand Separator) for Home Withdraw Modal
    function formatRibuanHome(val) {
        val = val.replace(/\D/g, '');
        return val.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    const displayInputHome = document.getElementById('withdrawAmountDisplayHome');
    const hiddenInputHome  = document.getElementById('withdrawAmountHome');

    if (displayInputHome) {
        displayInputHome.addEventListener('input', function () {
            const raw    = this.value.replace(/\./g, '');
            this.value   = formatRibuanHome(raw);
            hiddenInputHome.value = raw;

            // Tampilkan/sembunyikan alert merah
            const amount  = parseInt(raw) || 0;
            const alertEl = document.getElementById('alertMinimalHome');
            if (raw.length > 0 && amount < 100000) {
                alertEl.classList.remove('d-none');
            } else {
                alertEl.classList.add('d-none');
            }
        });
    }

    // Validation before submit for Home Withdraw Modal
    $(document).on('submit', '#withdrawFormHome', function(e) {
        const rawAmount = document.getElementById('withdrawAmountHome').value;
        const amount = parseInt(rawAmount) || 0;
        const available = {{ $availableBalance ?? 0 }};

        if (amount < 100000) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Minimal Penarikan',
                text: 'Jumlah penarikan minimal adalah Rp 100.000',
                confirmButtonColor: '#4e73df',
            });
            return false;
        }

        if (amount > available) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Saldo Tidak Mencukupi',
                text: 'Jumlah penarikan melebihi saldo tersedia Anda.',
                confirmButtonColor: '#4e73df',
            });
            return false;
        }
    });
</script>
@endpush
@endsection