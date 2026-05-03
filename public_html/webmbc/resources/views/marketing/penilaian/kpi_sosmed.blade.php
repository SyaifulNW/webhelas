@extends('layouts.masteradmin')

@section('content')
<div class="container my-4">
    <h4 class="mb-3 text-center text-primary">📅 KPI MARKETING SOSMED SPESIALIS</h4>

    <!-- Filter Bulan/Tahun -->
    <form action="{{ route('marketing.penilaian.kpi_sosmed') }}" method="GET" class="row mb-4 justify-content-center">
        <div class="col-md-6 d-flex gap-2">
            <select name="bulan" class="form-control form-control-sm">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ ($bulanNum ?? date('n')) == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
            <select name="tahun" class="form-control form-control-sm">
                @foreach(range(date('Y')-1, date('Y')+1) as $y)
                    <option value="{{ $y }}" {{ ($tahun ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fas fa-filter"></i> Filter</button>
        </div>
    </form>

    <!-- Form -->
    <form id="kpiForm"> 
        @csrf
        
        <!-- Combined KPI Section -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white fw-bold">
                INDIKATOR KINERJA
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0 align-middle">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width: 35%;">Indikator</th>
                            <th style="width: 25%;">Target / Standar</th>
                            <th style="width: 25%;">Realisasi</th>
                            <th style="width: 15%;">Skor (0-100)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- KPI Indicators -->
                        <tr>
                            <td class="px-3">Followers</td>
                            <td class="text-center">Naik 50%</td>
                            <td class="px-3">
                                <div class="input-group input-group-sm">
                                    <input type="number" id="real_followers" name="followers_real" class="form-control" placeholder="Isi angka..." value="{{ optional($savedKpi)->followers_real }}" oninput="hitungDanSimpan()">
                                    <span class="input-group-text">%</span>
                                </div>
                            </td>
                            <td class="px-3">
                                <input type="number" id="skor_followers" name="followers_skor" class="form-control form-control-sm text-center skor-kpi" min="0" placeholder="0" value="{{ optional($savedKpi)->followers_skor }}" oninput="hitungDanSimpan()">
                            </td>
                        </tr>
                        <tr>
                            <td class="px-3">Respons DM</td>
                            <td class="text-center">&lt; 1 jam</td>
                            <td class="px-3">
                                <input type="text" id="real_respons_dm" name="respons_dm_real" class="form-control form-control-sm" placeholder="Contoh: 30 menit" value="{{ optional($savedKpi)->respons_dm_real }}" oninput="hitungDanSimpan()">
                            </td>
                            <td class="px-3">
                                <input type="number" id="skor_respons_dm" name="respons_dm_skor" class="form-control form-control-sm text-center skor-kpi" min="0" placeholder="0" value="{{ optional($savedKpi)->respons_dm_skor }}" oninput="hitungDanSimpan()">
                            </td>
                        </tr>
                        <tr>
                            <td class="px-3">DM Masuk</td>
                            <td class="text-center">≥ 100 / bulan</td>
                            <td class="px-3">
                                <div class="input-group input-group-sm">
                                    <input type="number" id="real_dm_masuk" name="dm_masuk_real" class="form-control" placeholder="0" value="{{ optional($savedKpi)->dm_masuk_real }}" oninput="hitungDanSimpan()">
                                    <span class="input-group-text">/bulan</span>
                                </div>
                            </td>
                            <td class="px-3">
                                <input type="number" id="skor_dm_masuk" name="dm_masuk_skor" class="form-control form-control-sm text-center skor-kpi" min="0" placeholder="0" value="{{ optional($savedKpi)->dm_masuk_skor }}" oninput="hitungDanSimpan()">
                            </td>
                        </tr>
                        <tr>
                            <td class="px-3">Klik Link WA</td>
                            <td class="text-center">≥ 50 / bulan</td>
                            <td class="px-3">
                                <div class="input-group input-group-sm">
                                    <input type="number" id="real_link_wa" name="link_wa_real" class="form-control" placeholder="0" value="{{ optional($savedKpi)->link_wa_real }}" oninput="hitungDanSimpan()">
                                    <span class="input-group-text">/bulan</span>
                                </div>
                            </td>
                            <td class="px-3">
                                <input type="number" id="skor_link_wa" name="link_wa_skor" class="form-control form-control-sm text-center skor-kpi" min="0" placeholder="0" value="{{ optional($savedKpi)->link_wa_skor }}" oninput="hitungDanSimpan()">
                            </td>
                        </tr>
                        <tr>
                            <td class="px-3">Pendaftar Zoom</td>
                            <td class="text-center">≥ 30 / bulan</td>
                            <td class="px-3">
                                <div class="input-group input-group-sm">
                                    <input type="number" id="real_zoom" name="zoom_real" class="form-control" placeholder="0" value="{{ optional($savedKpi)->zoom_real }}" oninput="hitungDanSimpan()">
                                    <span class="input-group-text">/bulan</span>
                                </div>
                            </td>
                            <td class="px-3">
                                <input type="number" id="skor_zoom" name="zoom_skor" class="form-control form-control-sm text-center skor-kpi" min="0" placeholder="0" value="{{ optional($savedKpi)->zoom_skor }}" oninput="hitungDanSimpan()">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bobot Penilaian Table -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body bg-light rounded">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-secondary">📊 Bobot Penilaian</h5>
                    <span id="save-status" class="badge bg-secondary opacity-75">Ready</span>
                </div>
                <table class="table table-bordered mb-0">
                    <thead class="table-white">
                        <tr class="text-center">
                            <th>Kategori</th>
                            <th style="width: 20%;">Skor Rata-rata</th>
                            <th style="width: 20%;">Bobot</th>
                            <th style="width: 20%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-3">Aktivitas (Disiplin Kerja)</td>
                            <td class="text-center">
                                <input type="number" id="avg_disiplin" name="skor_disiplin" class="form-control form-control-sm text-center fw-bold" value="{{ round($skorDisiplin ?? 0, 1) }}" readonly>
                            </td>
                            <td class="text-center">30%</td>
                            <td class="text-center">
                                <input type="number" id="total_disiplin" class="form-control form-control-sm text-center fw-bold text-primary" readonly value="0">
                            </td>
                        </tr>
                        <tr>
                            <td class="px-3">INTAKE (Leads & DM)</td>
                            <td class="text-center">
                                <input type="number" id="avg_intake" class="form-control form-control-sm text-center fw-bold" readonly value="0">
                            </td>
                            <td class="text-center">70%</td>
                            <td class="text-center">
                                <input type="number" id="total_intake" class="form-control form-control-sm text-center fw-bold text-primary" readonly value="0">
                            </td>
                        </tr>
                        <tr class="table-warning fw-bold">
                            <td colspan="3" class="text-end px-3">✨ NILAI AKHIR</td>
                            <td class="text-center">
                                <input type="number" name="nilai_akhir" id="nilai_akhir" class="form-control form-control-sm text-center fw-bold text-success" readonly value="0">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Keterangan Skala Nilai Table -->
        <div class="card mb-4 shadow-sm border-0 mt-4">
            <h5 class="fw-bold mb-3 text-secondary px-3 pt-3">Keterangan Skala Nilai</h5>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0 align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 30%;">Nilai</th>
                            <th style="width: 70%;">Makna</th>
                        </tr>
                    </thead>
                    <tbody class="fw-bold">
                        <tr style="background-color: #e53935; color: white;">
                            <td>&lt; 60</td>
                            <td>Sibuk tapi tidak berdampak</td>
                        </tr>
                        <tr style="background-color: #ffe75c; color: black;">
                            <td>60 – 74</td>
                            <td>Cukup, tapi belum efektif</td>
                        </tr>
                        <tr style="background-color: #22b122; color: white;">
                            <td>75 – 84</td>
                            <td>Sudah kerja benar</td>
                        </tr>
                        <tr style="background-color: #009300; color: white;">
                            <td>85 – 100</td>
                            <td>Siap diskalakan & dibesarkan</td>
                        </tr>
                        <tr style="background-color: #004d00; color: white;">
                            <td>> 100</td>
                            <td>Luar Biasa</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
            // Run calculation on page load
            document.addEventListener('DOMContentLoaded', hitungSkor);

            let timeoutId;

            function hitungDanSimpan() {
                // 0. Auto-calculate Individual Scores if Realisation is numeric
                calculateIndividualScores();

                // 1. Calculate Summary
                hitungSkor();

                // 2. Debounce Save
                clearTimeout(timeoutId);
                let statusBadge = document.getElementById('save-status');
                statusBadge.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Typing...';
                statusBadge.className = 'badge bg-warning text-dark';

                timeoutId = setTimeout(() => {
                    simpanData();
                }, 1000); // Wait 1 second after last input
            }

            function calculateIndividualScores() {
                // Followers
                let fReal = document.getElementById('real_followers').value;
                if(fReal !== "") document.getElementById('skor_followers').value = Math.round((parseFloat(fReal) / 50) * 100);

                // Respons DM (Heuristic)
                let rReal = document.getElementById('real_respons_dm').value;
                let match = rReal.match(/\d+/);
                if (match) {
                    let mins = parseInt(match[0]);
                    document.getElementById('skor_respons_dm').value = mins <= 60 ? 100 : Math.round((60 / mins) * 100);
                }

                // DM Masuk
                let dmReal = document.getElementById('real_dm_masuk').value;
                if(dmReal !== "") document.getElementById('skor_dm_masuk').value = Math.round((parseFloat(dmReal) / 100) * 100);

                // Link WA
                let lReal = document.getElementById('real_link_wa').value;
                if(lReal !== "") document.getElementById('skor_link_wa').value = Math.round((parseFloat(lReal) / 50) * 100);

                // Zoom
                let zReal = document.getElementById('real_zoom').value;
                if(zReal !== "") document.getElementById('skor_zoom').value = Math.round((parseFloat(zReal) / 30) * 100);
            }

            function hitungSkor() {
                // 1. Hitung Rata-rata INTAKE (dari 5 indikator di atas)
                let intakeInputs = document.querySelectorAll('.skor-kpi');
                let sumIntake = 0;
                let countIntake = 0;
                
                intakeInputs.forEach(input => {
                    let val = parseFloat(input.value) || 0;
                    sumIntake += val;
                    countIntake++;
                });
                
                let avgIntake = countIntake > 0 ? (sumIntake / countIntake) : 0;
                let weightedIntake = avgIntake * 0.70;

                // 2. Ambil Skor Aktivitas (Disiplin Kerja) - Automatis dari Backend
                let avgDisiplin = parseFloat(document.getElementById('avg_disiplin').value) || 0;
                let weightedDisiplin = avgDisiplin * 0.30;

                // Update summary table
                document.getElementById('avg_intake').value = avgIntake.toFixed(1);
                document.getElementById('total_intake').value = weightedIntake.toFixed(1);
                
                document.getElementById('total_disiplin').value = weightedDisiplin.toFixed(1);
                
                // Final Score
                let finalScore = weightedIntake + weightedDisiplin;
                document.getElementById('nilai_akhir').value = finalScore.toFixed(1);
            }

            function simpanData() {
                let statusBadge = document.getElementById('save-status');
                statusBadge.innerHTML = '<i class="fas fa-sync fa-spin"></i> Saving...';

                // Collect Data
                let formData = {
                    _token: '{{ csrf_token() }}',
                    bulan: '{{ $bulanNum }}',
                    tahun: '{{ $tahun }}',
                    followers_real: document.getElementById('real_followers').value,
                    followers_skor: document.getElementById('skor_followers').value,
                    respons_dm_real: document.getElementById('real_respons_dm').value,
                    respons_dm_skor: document.getElementById('skor_respons_dm').value,
                    dm_masuk_real: document.getElementById('real_dm_masuk').value,
                    dm_masuk_skor: document.getElementById('skor_dm_masuk').value,
                    link_wa_real: document.getElementById('real_link_wa').value,
                    link_wa_skor: document.getElementById('skor_link_wa').value,
                    zoom_real: document.getElementById('real_zoom').value,
                    zoom_skor: document.getElementById('skor_zoom').value,
                    skor_disiplin: document.getElementById('avg_disiplin').value,
                    nilai_akhir: document.getElementById('nilai_akhir').value,
                };

                fetch('{{ route("marketing.penilaian.kpi_sosmed.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        statusBadge.innerHTML = '<i class="fas fa-check"></i> Saved';
                        statusBadge.className = 'badge bg-success';
                        setTimeout(() => {
                            statusBadge.className = 'badge bg-secondary opacity-75';
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    statusBadge.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error';
                    statusBadge.className = 'badge bg-danger';
                });
            }
        </script>

    </form>
</div>
@endsection
