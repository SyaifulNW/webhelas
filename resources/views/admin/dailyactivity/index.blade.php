@extends('layouts.masteradmin')

@section('content')
<div class="container my-4">
    <h4 class="mb-3 text-center text-primary">📅 DAILY ACTIVITY</h4>

    <form id="daily-activity-form" action="{{ route('admin.daily-activity.store') }}" method="POST">
        @csrf

   <div class="mb-3">
    <label class="form-label fw-bold">Tanggal:</label>
    <div style="max-width: 250px;">
        <input type="date" name="tanggal" class="form-control" 
               value="{{ $tanggal }}"
               onchange="window.location='?tanggal=' + this.value">
    </div>
      <!-- Export PDF -->
        <div class="mb-3 text-end">
            <a href="{{ route('admin.daily-activity.exportPdf', ['bulan' => \Carbon\Carbon::parse($tanggal)->format('Y-m')]) }}" class="btn btn-danger" target="_blank">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
    
    
</div>



        @foreach($activities as $kategoriId => $list)
            <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span>{{ $list->first()->kategori->nama ?? 'Tanpa Kategori' }}</span>

                @if(($list->first()->kategori->nama ?? '') === 'Aktivitas Merawat Customer')
                <small class="fst-italic">
                    🌟 Aktivitas ini fleksibel, tidak harus diinput setiap hari, yang penting target bulanan tercapai
                </small>
                @endif
            </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0 table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:5%">No</th>
                                <th style="width:25%">Aktivitas</th>
                                <th style="width:10%">Target Daily</th>
                                <th style="width:10%">Target Bulan</th>
                                <th style="width:10%">Bobot</th>
                                <th style="width:15%">Realisasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($list as $i => $act)
                                <tr>
                                    <td class="text-center">{{ $i+1 }}</td>
                                    <td>{{ $act->nama }}</td>
                                <td class="text-center">{{ number_format($act->target_daily, 0) }}</td>
                            <td class="text-center">{{ number_format($act->target_bulanan, 0) }}</td>

                                    <td class="text-center">{{ $act->bobot }}</td>
                                    <td>
                                        <input type="number" 
                                               name="realisasi[{{ $act->id }}]" 
                                               class="form-control form-control-sm"
                                               min="0"
                                               value="{{ $daily[$act->id] ?? 0 }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        <button type="submit" class="btn btn-success">
            💾 Simpan Aktivitas
        </button>
    </form>
<!--{{-- ================= KPI BULANAN ================= --}}-->
<!--<div class="card shadow-lg border-0 mt-5">-->
<!--    <div class="card-header bg-gradient bg-primary text-white text-center fw-bold fs-5">-->
<!--        📊 KEY PERFORMANCE INDEX (KPI) - CS MBC-->
<!--    </div>-->
<!--    <div class="card-body p-0">-->
<!--        <table class="table table-striped table-hover mb-0 text-center align-middle">-->
<!--            <thead class="table-dark">-->
<!--                <tr>-->
<!--                    <th style="width:5%">No</th>-->
<!--                    <th style="width:30%">Aktivitas</th>-->
<!--                    <th style="width:15%">🎯 Target</th>-->
<!--                    <th style="width:15%">⚖️ Bobot</th>-->
<!--                    <th style="width:15%">📈 Presentase</th>-->
<!--                    <th style="width:20%">⭐ Nilai</th>-->
<!--                </tr>-->
<!--            </thead>-->
<!--            <tbody>-->
<!--                @foreach($kpiData as $i => $row)-->
<!--                    <tr>-->
<!--                        <td>{{ $i+1 }}</td>-->
<!--                        <td class="text-start fw-semibold">{{ $row['nama'] }}</td>-->
<!--                        <td>-->
<!--                            <span class="badge bg-info text-dark px-3 py-2">-->
<!--                                {{ $row['target'] }}%-->
<!--                            </span>-->
<!--                        </td>-->
<!--                        <td>-->
<!--                            <span class="badge bg-warning text-dark px-3 py-2">-->
<!--                                {{ $row['bobot'] }}-->
<!--                            </span>-->
<!--                        </td>-->
<!--                        <td>-->
<!--                            <span class="badge bg-success px-3 py-2">-->
<!--                                {{ $row['persentase'] }}%-->
<!--                            </span>-->
<!--                        </td>-->
<!--                        <td>-->
<!--                            <span class="badge bg-primary px-3 py-2">-->
<!--                                {{ number_format($row['nilai'],2) }}-->
<!--                            </span>-->
<!--                        </td>-->
<!--                    </tr>-->
<!--                @endforeach-->
<!--                <tr class="table-success fw-bold">-->
<!--                    <td colspan="3" class="text-center">TOTAL</td>-->
<!--                    <td>-->
<!--                        <span class="badge bg-dark px-3 py-2">{{ $totalBobot }}</span>-->
<!--                    </td>-->
<!--                    <td>—</td>-->
<!--                    <td>-->
<!--                        <span class="badge bg-danger px-3 py-2">{{ number_format($totalNilai,2) }}</span>-->
<!--                    </td>-->
<!--                </tr>-->
<!--            </tbody>-->
<!--        </table>-->
<!--    </div>-->
<!--</div>-->
<!--{{-- ==================================================== --}}-->

</div>


@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
    $('#daily-activity-form').on('submit', function(e) {
        e.preventDefault();

        let form = $(this);
        let url = form.attr('action');
        let data = form.serialize();

        Swal.fire({
            title: 'Menyimpan...',
            text: 'Harap tunggu',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.post(url, data)
            .done(function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: response.message || 'Data berhasil disimpan',
                    timer: 2000,
                    showConfirmButton: false
                });
            })
            .fail(function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan',
                });
            });
    });
});
</script>
@endpush
