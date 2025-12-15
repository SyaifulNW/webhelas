@extends('layouts.masteradmin')
@section('content')

<style>
 thead {
        background-color: #25799E;
        color: white;
        position: sticky;
        top: 0;
        z-index: 1;
    }
    
  
</style>
@if(auth()->user()->role !== 'administrator')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Database Calon Peserta</h1>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item active">Database Calon Peserta</li>
        </ol>
    </div>
</div>
@endif





        </div>
    </form>





    {{-- ALERT MODE READ ONLY (ADMIN) --}}
    @if(isset($user) && $readonly)
        <div class="alert alert-info d-flex align-items-center justify-content-between mb-4 shadow-sm" role="alert">
            <div>
                <strong>Database CS:</strong> <strong>{{ $user->name }} </strong> <br>
                <span class="text-muted small">Email: {{ $user->email }} | Role: {{ ucfirst($user->role) }}</span>
            </div>
            <div>
                <span class="text-white badge bg-primary p-2">Mode Read-Only</span>
            </div>
        </div>
        
        
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
                <input type="text" name="pesan" class="form-control" placeholder="Tulis komentar untuk CS ini..." required>
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

<div class="modal fade" id="modalKomentar" tabindex="-1" role="dialog" aria-labelledby="modalKomentarLabel" aria-hidden="true">
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
@endif

    



<div class="content">
    <div class="card card-info card-outline">
@php
use Carbon\Carbon;
use App\Models\Data;

$bulanLabel = Carbon::now()->isoFormat('MMMM YYYY');
$user = auth()->user();
$request = request();

$filterUser  = $request->get('user');
$filterBulan = $request->get('bulan');

// ✅ Daftar auth id admin MBC
$adminMbcIds = [2, 3, 6, 10];

// =============================
// 🔹 Buat query dasar
// =============================
$query = Data::query();

// =============================
// 🔹 ROLE ADMIN / MANAGER
// =============================
if (in_array(strtolower($user->role), ['administrator', 'manager'])) {

    // 🔸 Jika filter user dipilih → ambil data CS yang dipilih
    if (!empty($filterUser)) {
        $query->where('created_by', $filterUser);
    }

} else {
    // CS biasa → hanya bisa lihat datanya sendiri
    $query->where('created_by', $user->name);
}

// =============================
// 🔹 Filter Bulan
// =============================
if (!empty($filterBulan)) {
    $query->whereMonth('created_at', $filterBulan);
}

// =============================
// 🔹 Eksekusi Query
// =============================
$data = $query->orderBy('created_at', 'desc')->get();

$now = Carbon::now();

$databaseBaru = (clone $query)
    ->whereYear('created_at', $now->year)
    ->whereMonth('created_at', $now->month)
    ->count();

$totalDatabase = (clone $query)->count();

$target = 50;
$kurang = max($target - $databaseBaru, 0);
@endphp









<div class="card-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <!-- Kiri: Tombol & Statistik -->
        <div class="d-flex align-items-center gap-2 flex-wrap">

            {{-- Tombol Tambah (hanya non-admin/manager) --}}
            @if(!in_array(strtolower(auth()->user()->role), ['administrator', 'manager']))
                <a href="#" class="btn btn-success" onclick="create()">
                    <i class="fa-solid fa-plus"></i> Tambah
                </a>
            @endif

            &nbsp;
            <!-- Badge Statistik -->
            <span class="badge bg-info px-3 py-2 text-white">
                Database Baru ({{ $bulanLabel }}): {{ $databaseBaru }}
            </span>
            &nbsp;
            <span class="badge bg-primary px-3 py-2 text-white">
                Total Database: {{ $totalDatabase }}
            </span>
            &nbsp;
            <span class="badge bg-warning px-3 py-2 text-white">
                Target: {{ $target }}
            </span>
            &nbsp;
            <span class="badge bg-danger px-3 py-2 text-white">
                Kurang: {{ $kurang }}
            </span>
        </div>

<!-- Kanan: Toolbar Filter & Search -->
<div class="d-flex align-items-center gap-2">
@php
    use App\Models\User;

    $user = auth()->user();
    $csList = collect();

    // Daftar CS hanya untuk admin/manager
    if (in_array(strtolower($user->role), ['administrator', 'manager'])) {
        $csList = User::whereIn('role', ['cs', 'CS', 'customer_service'])
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }
@endphp

    {{-- ðŸ”¹ Administrator / Manager: Filter Input Oleh --}}
    @if(in_array(strtolower(auth()->user()->role), ['administrator', 'manager']))
        <select id="filterUser" class="form-select form-select-sm">
            <option value="">-- Semua CS --</option>
            @foreach($csList as $cs)
                  <option value="{{ $cs->name }}">{{ $cs->name }}</option>
            @endforeach
        </select>
    @endif
    &nbsp;
    {{-- ðŸ”¹ CS Biasa: Filter Bulan --}}
    @if(!in_array(strtolower(auth()->user()->role), ['administrator', 'manager']))
        <select id="filterBulan" class="form-select form-select-sm">
            <option value="">-- Semua Bulan --</option>
            @foreach(range(1,12) as $m)
                <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>
    @endif

    {{-- ðŸ”¹ Search --}}
    <div class="input-group input-group-sm">
        <input type="text" id="tableSearch" class="form-control" placeholder="Cari...">
    </div>
</div>
</div>
</div>

<!--get statistik-->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const userSelect = document.getElementById('filterUser');
    if (!userSelect) return;

    // Jalankan pertama kali (saat halaman load)
    getStatistik(userSelect.value);

    // Jalankan ulang setiap kali user ganti CS
    userSelect.addEventListener('change', function() {
        getStatistik(this.value);
    });
});

function getStatistik(user) {
    fetch(`/get-statistik?user=${encodeURIComponent(user || '')}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('databaseBaru').textContent = data.databaseBaru;
            document.getElementById('totalDatabase').textContent = data.totalDatabase;
            document.getElementById('targetDatabase').textContent = data.target;
            document.getElementById('kurangDatabase').textContent = data.kurang;
            
            document.getElementById('bulanLabel').textContent = data.bulanLabel;
        })
        .catch(err => console.error('Gagal mengambil statistik:', err));
}
</script>




<!-- ðŸ”¹ Script Filter -->
<script>
$(document).ready(function() {

    function applyFilters() {
        let userRole = "{{ strtolower(auth()->user()->role) }}";
        let user = $('#filterUser').val() ? $('#filterUser').val().toLowerCase() : "";
        let selectedBulan = $('#filterBulan').val();
        let search = $('#tableSearch').val().toLowerCase();

        $('#myTable tbody tr').each(function() {
            let createdBy = $(this).data('created-by');
            let bulan = $(this).data('bulan');
            let text = $(this).text().toLowerCase();

            let matchUser = (user === "" || createdBy === user);
            let matchBulan = (selectedBulan === "" || bulan == selectedBulan);
            let matchSearch = (text.includes(search));

            // Admin filter by CS, CS filter by bulan
            let visible = false;
            if (userRole === "administrator" || userRole === "manager") {
                visible = matchUser && matchSearch;
            } else {
                visible = matchBulan && matchSearch;
            }

            $(this).toggle(visible);
        });

        // ðŸ”¹ Jika admin ganti filter CS â†’ update statistik via AJAX
        if (userRole === "administrator" || userRole === "manager") {
            let selectedUser = $('#filterUser').val();

            $.ajax({
                url: "{{ route('admin.database.statistik') }}",
                type: "GET",
                data: { user: selectedUser },
                success: function(res) {
                    $('.badge-info').text(`Database Baru (${res.bulanLabel}): ${res.databaseBaru}`);
                    $('.badge-primary').text(`Total Database: ${res.totalDatabase}`);
                    $('.badge-warning').text(`Target: ${res.target}`);
                    $('.badge-danger').text(`Kurang: ${res.kurang}`);
                }
            });
        }
    }

    $('#filterUser, #filterBulan, #tableSearch').on('change keyup', applyFilters);
});
</script>


        <div class="card-body">
            <div style="overflow-x: auto; overflow-y: auto; width: 100%; max-height: 500px;">

                <table id="myTable" class="table table-bordered table-striped nowrap" style="width: max-content;">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Peserta</th>
                         <th>
                                Sumber Leads <br>
                                <select id="filterSumber" class="form-control form-control-sm">
                                    <option value="">-- Semua Sumber --</option>
                                    <option value="Iklan">Iklan</option>
                                    <option value="Marketing">Marketing (IG,FB,Event)</option>
                                    <option value="Alumni">Alumni</option>
                                    <option value="Lain-lain">Lain-lain</option>
                                </select>
                            </th>

                            <script>
                                $(document).ready(function() {
                                    // Pencarian global tetap jalan
                                    $('#tableSearch').on('keyup', function() {
                                        let filter = $(this).val().toLowerCase();
                                        $('#myTable tbody tr').each(function() {
                                            let text = $(this).text().toLowerCase();
                                            $(this).toggle(text.includes(filter));
                                        });
                                    });

                                    // Filter Sumber Leads
                                    $('#filterSumber').on('change', function() {
                                        let selected = $(this).val().toLowerCase();
                                        $('#myTable tbody tr').each(function() {
                                            let sumber = $(this).find('td:eq(2) select option:selected').text().toLowerCase();
                                            // kolom ke-2 (No=0, Nama=1, Leads=2)

                                            if (selected === "" || sumber === selected) {
                                                $(this).show();
                                            } else {
                                                $(this).hide();
                                            }
                                        });
                                    });
                                });
                            </script>

                            @if(strtolower(auth()->user()->role) !== 'administrator')
            <th>Provinsi</th>
        @endif
                            <th>Kota</th>
                            <th>Nama Bisnis</th>
                            <th>Jenis Bisnis</th>
                            <th>No.WA</th>
                            <th>CTA</th>
                            <th>Situasi Bisnis</th>
                            <th>Kendala</th>
                            
                        {{-- Hanya tampil jika bukan marketing --}}
@if(strtolower(auth()->user()->role) !== 'marketing')
    <th>
        Potensi Kelas Pertama
        <div style="min-width: 200px;">
            <select id="filterKelas" class="form-control-sm">
                <option value="">-- Semua Potensi Kelas --</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->nama_kelas }}">{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
        </div>
    </th>
@endif

                                
                                  @if(Auth::user()->email !== "mbchamasah@gmail.com"  && Auth::user()->role !== 'marketing')    
                            <th>Sales Plan</th>
                                @endif
                            @if(Auth::user()->email == "mbchamasah@gmail.com")
                            <th>Input Oleh</th>

                            <th>Role</th>
                            @endif
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($data as $item)
   <tr 
    data-created-by="{{ strtolower($item->created_by) }}"
    data-bulan="{{ \Carbon\Carbon::parse($item->created_at)->month }}"
>

                            <td>{{ $loop->iteration }}</td>
                            <td contenteditable="true" class="editable" data-field="nama">{{ $item->nama }}</td>
                     <td>
                         <select class="form-control form-control-sm select-sumber" data-id="{{ $item->id }}">
    <option value="">- Pilih Sumber Leads -</option>

                                    <option value="Iklan" {{ $item->leads == 'Iklan' ? 'selected' : '' }}>Iklan</option>
                                    <option value="Marketing" {{ $item->leads == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                                    <option value="Alumni" {{ $item->leads == 'Alumni' ? 'selected' : '' }}>Alumni</option>
                                    <option value="Lain-lain" {{ $item->leads == 'Lain-lain' ? 'selected' : '' }}>Lain-lain</option>
                                </select>


                            </td>
                         <script>
$(document).ready(function() {

    $('.select-sumber').change(function() {
        let id = $(this).data('id');
        let value = $(this).val();

        $.ajax({
            url: '/admin/database/update-inline',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
                field: 'leads',
                value: value
            }
        });
    });

});
</script>

        
    @if(strtolower(auth()->user()->role) !== 'administrator')
        <td contenteditable="true" class="editable" data-field="provinsi_nama">{{ $item->provinsi_nama }}</td>
    @endif
                            <td contenteditable="true" class="editable" data-field="kota_nama">{{ $item->kota_nama }}</td>
                            <td contenteditable="true" class="editable" data-field="nama_bisnis">{{ $item->nama_bisnis }}</td>
                            <td contenteditable="true" class="editable" data-field="jenisbisnis">{{ $item->jenisbisnis }}</td>
                            <td contenteditable="true" class="editable" data-field="no_wa">{{ $item->no_wa }}</td>
                            <td>
                                @php $waNumber = preg_replace('/^0/', '62', $item->no_wa); @endphp
                                <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="btn btn-success btn-sm wa-button">
                                    <i class="bi bi-whatsapp" style="color:#fff;font-size:1.5rem;"></i>
                                </a>
                            </td>
                            <td contenteditable="true" class="editable" data-field="situasi_bisnis">{{ $item->situasi_bisnis }}</td>
                            <td contenteditable="true" class="editable" data-field="kendala">{{ $item->kendala }}</td>
                            @if(strtolower(auth()->user()->role) !== 'marketing')
                            <td>
                                <select class="form-control form-control-sm select-potensi" data-id="{{ $item->id }}">
                                    <option value="">- Pilih Kelas -</option>
                                    @foreach($kelas as $k)
                                    <option value="{{ $k->id }}" {{ $item->kelas_id == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            @endif
                                @if(strtolower(auth()->user()->role) !== 'administrator'  && Auth::user()->role !== 'marketing')
                            <td>
                                <form action="{{ route('data.pindahKeSalesPlan', $item->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-arrow-right"></i></button>
                                </form>
                            </td>
                            @endif
                            @if(Auth::user()->email == "mbchamasah@gmail.com")
                            <td>{{ $item->created_by }}</td>
                            <td>{{ $item->created_by_role }}</td>
                            @endif
                            
                            <td>
                                <a href="{{ route('admin.database.show', $item->id) }}" class="btn btn-info btn-sm">
                                    <i class="fa-solid fa-eye" style="color:#fff;"></i>
                                </a>
                                <form action="{{ route('delete-database', $item->id) }}" method="POST" style="display:inline;" class="delete-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-delete">
                                        <i class="fa-solid fa-trash" style="color:#fff;"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
                
                <!-- Script FIlter -->
                <script>
                    $(document).ready(function() {
                        $('#filterLeads, #filterProvinsi, #filterKota, #filterJenisBisnis, #filterInputOleh').on('change', function() {
                            let filters = {
                                leads: $('#filterLeads').val(),
                                provinsi: $('#filterProvinsi').val(),
                                kota: $('#filterKota').val(),
                                jenisbisnis: $('#filterJenisBisnis').val(),
                                created_by: $('#filterInputOleh').val(),
                            };

                            $.ajax({
                                url: "{{ route('admin.database.filter') }}",
                                type: "GET",
                                data: filters,
                                success: function(response) {
                                    $('#tableData').html(response);
                                },
                                error: function() {
                                    alert('Gagal memuat data filter');
                                }
                            });
                        });
                    });
                </script>


                <!-- Script JQuery -->
                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script>
                    $(document).ready(function() {

                        // Untuk kolom text
                        $('.editable').on('blur', function() {
                            let value = $(this).text();
                            let field = $(this).data('field');
                            let id = $(this).closest('tr').data('id');

                            $.ajax({
                                url: '/admin/database/update-inline',
                                method: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    id: id,
                                    field: field,
                                    value: value
                                },
                                success: function(res) {
                                    console.log('Updated:', field);
                                },
                                // error: function() {
                                //     alert('Gagal update data');
                                // }
                            });
                        });

                        // Untuk dropdown Potensi Kelas
                        $('.select-potensi').on('change', function() {
                            let id = $(this).data('id');
                            let kelas_id = $(this).val();

                            $.ajax({
                                url: `/admin/database/update-potensi/${id}`,
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    kelas_id: kelas_id
                                },
                                success: function(response) {
                                    console.log('Potensi kelas updated');
                                },
                                error: function() {
                                    alert('Gagal update potensi kelas');
                                }
                            });
                        });

                    });
                </script>
                <style>
                    .editable {
                        cursor: pointer;
                    }

                    .editing {
                        background-color: #fff3cd !important;
                        /* kuning saat edit */
                    }

                    .status-icon {
                        margin-left: 5px;
                        font-size: 14px;
                    }

                    .status-success {
                        color: green;
                    }

                    .status-error {
                        color: red;
                    }
                </style>
                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script>
                    $(document).ready(function() {

                        // Untuk kolom text
                        $('.editable').on('focus', function() {
                            $(this).addClass('editing');
                        });

                        $('.editable').on('blur', function() {
                               let $this = $(this);
                            let value = $this.text();
                            let field = $this.data('field');
                            let id = $this.closest('tr').data('id');

                            $this.removeClass('editing');

                            $.ajax({
                                url: '/admin/database/update-inline',
                                method: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    id: id,
                                    field: field,
                                    value: value
                                },
                                success: function() {
                                    showStatusIcon($this, true);
                                },
                                error: function() {
                                    showStatusIcon($this, false);
                                }
                            });
                        });

                        // Untuk dropdown Potensi Kelas
                        $('.select-potensi').on('change', function() {
                            let $this = $(this);
                            let id = $this.data('id');
                            let kelas_id = $this.val();
                            let iconSpan = $this.next('.status-icon');

                            $.ajax({
                                url: `/admin/database/update-potensi/${id}`,
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    kelas_id: kelas_id
                                },
                                success: function() {
                                    iconSpan.html('<i class="fa fa-check status-success"></i>');
                                    setTimeout(() => iconSpan.html(''), 2000);
                                },
                                error: function() {
                                    iconSpan.html('<i class="fa fa-times status-error"></i>');
                                    setTimeout(() => iconSpan.html(''), 2000);
                                }
                            });
                        });

                        // Fungsi tampil icon centang atau silang
                        function showStatusIcon($element, success) {
                            let iconHtml = success ?
                                '<i class="fa fa-check status-success"></i>' :
                                '<i class="fa fa-times status-error"></i>';

                            let iconSpan = $('<span class="status-icon">' + iconHtml + '</span>');
                            $element.after(iconSpan);

                            setTimeout(() => {
                                iconSpan.fadeOut(300, function() {
                                    $(this).remove();
                                });
                            }, 2000);
                        }

                    });
                </script>



            </div>
        </div>
        

    </div>
</div>
@endsection
<!-- Modal Create -->


<script>
    $('#createForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert('Berhasil disimpan!');
                $('#createPesertaModal').modal('hide');
                location.reload(); // atau refresh tabel data
            },
            error: function(err) {
                alert('Gagal menyimpan.');
            }
        });
    });
</script>

<script>
    function create() {
        $('#createPesertaModal').modal('show');
    }

    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        // Add your AJAX call here to save the data
        alert('Data saved successfully!');
        $('#createPesertaModal').modal('hide');
    });
</script>
<script>
    $(document).ready(function() {
        $('#myTable').DataTable({
            responsive: true,
            autoWidth: false,
        });
    });
</script>



<!-- Modal Create -->
<div class="modal fade" id="createPesertaModal" tabindex="-1" role="dialog" aria-labelledby="createPesertaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createPesertaModalLabel">Tambah Peserta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="createForm" action="{{ route('admin.database.store') }}" method="POST">
                @csrf
                <div class="modal-body">

                    {{-- Nama Peserta --}}
                    <div class="form-group">
                        <label for="nama">Nama Peserta</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>

                    {{-- Status Peserta --}}
      

             {{-- Potensi Kelas --}}
<div class="form-group">
    <label for="kelas_id">Potensi Kelas</label>
    <select name="kelas_id" id="kelas_id" class="form-control" required>
        <option value="">Pilih Potensi Kelas</option>

        @forelse($kelas as $item)
            <option value="{{ $item->id }}">{{ $item->nama_kelas }}</option>
        @empty
            <option disabled>Tidak ada kelas tersedia</option>
        @endforelse
    </select>
</div>


                    {{-- Sumber Leads --}}
                    <div class="form-group">
                        <label for="leads">Sumber Leads</label>
                        <select name="leads" id="leads" class="form-control">
                            <option value="Iklan">Iklan</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Alumni">Alumni</option>
                            <option value="Lain-lain">Lain-lain</option>
                        </select>
                        <input type="text" name="leads_custom" class="form-control mt-2" placeholder="Isi jika Lain-Lain">
                    </div>

                    {{-- Provinsi --}}
                    <div class="form-group">
                        <label for="provinsi">Provinsi</label>
                        <select id="provinsi" class="form-control" name="provinsi_id" required>
                            <option value="">Pilih Provinsi</option>
                        </select>
                        <input type="hidden" name="provinsi_nama" id="provinsi_nama">
                    </div>

                    {{-- Kota --}}
                    <div class="form-group">
                        <label for="kota">Kota</label>
                        <select id="kota" class="form-control" name="kota_id" required>
                            <option value="">Pilih Kota</option>
                        </select>
                        <input type="hidden" name="kota_nama" id="kota_nama">
                    </div>

                    {{-- Script Ambil Wilayah --}}
                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                    <script>
                        fetch('/wilayah/provinsi')
                            .then(res => res.json())
                            .then(data => {
                                data.forEach(prov => {
                                    $('#provinsi').append(`<option value="${prov.id}" data-nama="${prov.name}">${prov.name}</option>`);
                                });
                            });

                        $('#provinsi').on('change', function() {
                            const id = $(this).val();
                            const nama = $(this).find('option:selected').text();
                            $('#provinsi_nama').val(nama);

                            fetch(`/wilayah/kota/${id}`)
                                .then(res => res.json())
                                .then(data => {
                                    $('#kota').html('<option value="">Pilih Kota</option>');
                                    data.forEach(kota => {
                                        $('#kota').append(`<option value="${kota.id}" data-nama="${kota.name}">${kota.name}</option>`);
                                    });
                                });
                        });

                        $('#kota').on('change', function() {
                            const nama = $(this).find('option:selected').text();
                            $('#kota_nama').val(nama);
                        });
                    </script>

                    {{-- Nama Bisnis --}}
                    <div class="form-group">
                        <label for="nama_bisnis">Nama Bisnis</label>
                        <input type="text" class="form-control" id="nama_bisnis" name="nama_bisnis" required>
                    </div>

                    {{-- Jenis Bisnis --}}
                    <div class="form-group">
                        <label for="jenisbisnis">Jenis Bisnis</label>
                        <select name="jenisbisnis" id="jenisbisnis" class="form-control">
                            <option value="Bisnis Properti">Bisnis Properti</option>
                            <option value="Bisnis Manufaktur">Bisnis Manufaktur</option>
                            <option value="Bisnis F&B (Food & Beverage)">Bisnis F&B (Food & Beverage)</option>
                            <option value="Bisnis Jasa">Bisnis Jasa</option>
                            <option value="Bisnis Digital">Bisnis Digital</option>
                            <option value="Bisnis Online">Bisnis Online</option>
                            <option value="Bisnis Franchise">Bisnis Franchise</option>
                            <option value="Bisnis Edukasi & Pelatihan">Bisnis Edukasi & Pelatihan</option>
                            <option value="Bisnis Kreatif">Bisnis Kreatif</option>
                            <option value="Bisnis Agribisnis">Bisnis Agribisnis</option>
                            <option value="Bisnis Kesehatan & Kecantikan">Bisnis Kesehatan & Kecantikan</option>
                            <option value="Bisnis Keuangan">Bisnis Keuangan</option>
                            <option value="Bisnis Transportasi & Logistik">Bisnis Transportasi & Logistik</option>
                            <option value="Bisnis Pariwisata & Hospitality">Bisnis Pariwisata & Hospitality</option>
                            <option value="Bisnis Sosial (Social Enterprise)">Bisnis Sosial (Social Enterprise)</option>
                        </select>
                    </div>

                    {{-- No WA --}}
                    <div class="form-group">
                        <label for="no_wa">No. WA</label>
                        <input type="text" class="form-control" id="no_wa" name="no_wa" required>
                    </div>

                    {{-- Situasi Bisnis --}}
                    <div class="form-group">
                        <label for="situasi_bisnis">Situasi Bisnis</label>
                        <textarea class="form-control" id="situasi_bisnis" name="situasi_bisnis" rows="3"></textarea>
                    </div>

                    {{-- Kendala --}}
                    <div class="form-group">
                        <label for="kendala">Kendala</label>
                        <textarea class="form-control" id="kendala" name="kendala" rows="3"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>




<!-- End Modal Create -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Event listener untuk dropdown filter kelas
        $('#filterKelas').on('change', function() {
            // Ambil nama kelas yang dipilih dari dropdown filter
            let selectedKelas = $(this).val();

            // Loop melalui setiap baris (tr) di dalam tbody tabel
            $('#myTable tbody tr').each(function() {
                let row = $(this);

                // Jika tidak ada kelas yang dipilih (opsi "-- Semua --"), tampilkan semua baris
                if (selectedKelas === "") {
                    row.show();
                } else {
                    // Ambil teks dari opsi yang terpilih di dropdown 'Potensi Kelas' dalam baris ini
                    let kelasDiBaris = row.find('.select-potensi option:selected').text().trim();

                    // Bandingkan dan tampilkan/sembunyikan baris
                    if (kelasDiBaris === selectedKelas) {
                        row.show(); // Tampilkan baris jika kelasnya cocok
                    } else {
                        row.hide(); // Sembunyikan baris jika tidak cocok
                    }
                }
            });
        });
    });
</script>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<!-- ✅ Tambahkan ini di atas tabel kamu -->
<link rel="stylesheet" 
      href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
