@extends('layouts.masteradmin')

@section('content')
<div class="container py-4">
    {{-- Judul --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary">
            <i class="bi bi-person-circle me-2"></i> Dashboard Monitoring CS: {{ $user->name }}
        </h2>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Card Ringkasan --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white rounded-3">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill fs-3 mb-2"></i>
                    <h6>Total Calon Peserta</h6>
                    <h2 class="fw-bold mb-0">128</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white rounded-3">
                <div class="card-body text-center">
                    <i class="bi bi-journal-check fs-3 mb-2"></i>
                    <h6>Total Sales Plan</h6>
                    <h2 class="fw-bold mb-0">42</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info text-white rounded-3">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up fs-3 mb-2"></i>
                    <h6>Total Leads</h6>
                    <h2 class="fw-bold mb-0">75</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Grafik Leads --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom-0">
            <h5 class="mb-0"><i class="bi bi-bar-chart-line"></i> Grafik Leads per Bulan</h5>
        </div>
        <div class="card-body">
            <canvas id="leadsChart" height="100"></canvas>
        </div>
    </div>

    {{-- Tabel Calon Peserta --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-people"></i> Database Calon Peserta</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-bordered table-striped">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>No</th>
                            <th>Nama Peserta</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $pesertaDummy = [
                                ['nama'=>'Ayu Permata','email'=>'ayu@mail.com','hp'=>'08123456789','status'=>'Prospek'],
                                ['nama'=>'Budi Santoso','email'=>'budi@mail.com','hp'=>'08561234567','status'=>'Follow Up'],
                                ['nama'=>'Citra Dewi','email'=>'citra@mail.com','hp'=>'08211234567','status'=>'Closing'],
                            ];
                        @endphp
                        @foreach ($pesertaDummy as $i => $p)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $p['nama'] }}</td>
                            <td>{{ $p['email'] }}</td>
                            <td>{{ $p['hp'] }}</td>
                            <td><span class="badge bg-secondary">{{ $p['status'] }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-muted small mt-2 mb-0">* Hanya tampilan, tidak dapat diubah.</p>
        </div>
    </div>

    {{-- Tabel Sales Plan --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-list-task"></i> Sales Plan</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-bordered table-striped">
                    <thead class="table-success text-center">
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Target</th>
                            <th>Deadline</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $salesPlanDummy = [
                                ['judul'=>'Follow Up Batch 1','target'=>'20 Leads','deadline'=>'20 Okt 2025','status'=>'On Progress'],
                                ['judul'=>'Promo Oktober','target'=>'10 Closing','deadline'=>'31 Okt 2025','status'=>'Done'],
                                ['judul'=>'Outbound Campaign','target'=>'50 Leads','deadline'=>'10 Nov 2025','status'=>'Pending'],
                            ];
                        @endphp
                        @foreach ($salesPlanDummy as $i => $sp)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $sp['judul'] }}</td>
                            <td>{{ $sp['target'] }}</td>
                            <td>{{ $sp['deadline'] }}</td>
                            <td><span class="badge bg-secondary">{{ $sp['status'] }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-muted small mt-2 mb-0">* Data ini hanya untuk monitoring, tidak bisa diedit.</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('leadsChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt'],
        datasets: [{
            label: 'Jumlah Leads',
            data: [5, 10, 7, 12, 8, 14, 9, 15, 11, 13],
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13,110,253,0.15)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointRadius: 5,
            pointHoverRadius: 6,
        }]
    },
    options: {
        plugins: {
            legend: { display: false },
        },
        scales: {
            y: { beginAtZero: true },
        }
    }
});
</script>
@endpush
