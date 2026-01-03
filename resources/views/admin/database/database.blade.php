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
        
    @if(auth()->user()->name !== 'Agus Setyo')
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
</div>
</div>
@endif
@endif

    



<div class="content">
    <div class="card card-info card-outline">
@php
use Carbon\Carbon;
use App\Models\Data;

$bulanLabel = Carbon::now()->isoFormat('MMMM YYYY');
// Preserve passed $user if in readonly mode, otherwise get auth user
$currentUser = auth()->user(); 
$request = request();

// Only run default query logic if NOT in readonly mode (Koordinasi)
if (!isset($readonly)) {
    // Standard Database View
    $user = $currentUser; // Use logged in user for permission checks
    
    $filterUser  = $request->get('user');
    $filterBulan = $request->get('bulan');

    $query = Data::query();

    // ROLE ADMIN / MANAGER / SPECIAL
    if (in_array(strtolower($user->role), ['administrator', 'manager']) || $user->name === 'Agus Setyo') {
        if (!empty($filterUser)) {
            $query->where('created_by', $filterUser);
        }
    } else {
        $query->where('created_by', $user->name);
    }

    // Khusus Agus Setyo
    if ($user->name === 'Agus Setyo') {
        $query->whereHas('kelas', function($q) {
             $q->where('nama_kelas', 'Start-Up Muda Indonesia')
               ->orWhere('nama_kelas', 'Start-Up Muslim Indonesia');
        });
    }

    if (!empty($filterBulan)) {
        $query->whereMonth('created_at', $filterBulan);
    }

    $data = $query->orderBy('created_at', 'desc')->get();
} else {
    // READONLY MODE (Koordinasi)
    // $data is already passed from controller. 
    // $user is passed from controller (target user).
    // We just need to ensure stats variables are set.
}

$now = Carbon::now();

// Calculate Stats based on the resulting $data collection
// This applies to both modes (Filtered Query or Controller Data)
$databaseBaru = $data->filter(function($item) use ($now) {
    return $item->created_at && $item->created_at->year == $now->year && $item->created_at->month == $now->month;
})->count();

$totalDatabase = $data->count();

$target = 50;
$kurang = max($target - $databaseBaru, 0);
@endphp









<div class="card-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <!-- Kiri: Tombol & Statistik -->
        <div class="d-flex align-items-center gap-2 flex-wrap">

            {{-- Tombol Tambah (hanya non-admin/manager) --}}
            @if(!in_array(strtolower(auth()->user()->role), ['administrator', 'manager']))
                <a href="#" class="btn btn-success" id="btnAddRow" onclick="createNewRow(event)">
                    <i class="fa-solid fa-plus"></i> Tambah
                </a>
            @endif

            &nbsp;
            <!-- Badge Statistik -->
            <span id="badge-database-baru" class="badge bg-info px-3 py-2 text-white">
                Database Baru ({{ $bulanLabel }}): {{ $databaseBaru }}
            </span>
            &nbsp;
            <span id="badge-total" class="badge bg-primary px-3 py-2 text-white">
                Total Database: {{ $totalDatabase }}
            </span>
            &nbsp;
            <span id="badge-target" class="badge bg-warning px-3 py-2 text-white">
                Target: {{ $target }}
            </span>
            &nbsp;
            <span id="badge-kurang" class="badge bg-danger px-3 py-2 text-white">
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
    if (in_array(strtolower($user->role), ['administrator', 'manager']) || $user->name === 'Agus Setyo') {
        $csList = User::whereIn('role', ['cs', 'CS', 'customer_service', 'cs-mbc', 'cs-smi'])
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }
@endphp

    {{-- ðŸ”¹ Administrator / Manager: Filter Input Oleh --}}
    @if(in_array(strtolower(auth()->user()->role), ['administrator', 'manager']) && auth()->user()->name !== 'Agus Setyo')
        <select id="filterUser" class="form-select form-select-sm" onchange="updateFilterUser(this.value)">
            {{-- Option Semua CS removed as requested --}}
            <option value="" disabled selected>-- Pilih CS --</option>
            @foreach($csList as $cs)
                  <option value="{{ $cs->name }}" {{ request('cs_name') == $cs->name ? 'selected' : '' }}>{{ $cs->name }}</option>
            @endforeach
        </select>
        <script>
            function updateFilterUser(val) {
                var url = new URL(window.location.href);
                if (val) {
                    url.searchParams.set('cs_name', val);
                } else {
                    url.searchParams.delete('cs_name'); 
                }
                url.searchParams.delete('page'); // Reset pagination
                window.location.href = url.toString();
            }
        </script>
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
        var userRole = "{{ strtolower(auth()->user()->role) }}";
        
        // Get values
        var fUser = $('#filterUser').val() ? $('#filterUser').val().toLowerCase() : '';
        var fBulan = $('#filterBulan').val();
        var fSumber = $('#filterSumber').val();
        var fKelas = $('#filterKelas').val();
        var search = $('#tableSearch').val() ? $('#tableSearch').val().toLowerCase() : '';

        $('#myTable tbody tr').each(function() {
            var $tr = $(this);
            var trUser = $tr.data('created-by'); // assume lowercase in data attr
            var trBulan = $tr.data('bulan');
            var trText = $tr.text().toLowerCase();
            
            // Get dynamic values from row inputs
            var trSumber = $tr.find('.select-sumber').val();
            // For kelas, get text of selected option to match filter which uses text
            var trKelas = '';
            var $kelasSelect = $tr.find('.select-potensi');
            if ($kelasSelect.length > 0) {
                trKelas = $kelasSelect.find('option:selected').text().trim();
            }
            
            var trYear = $tr.data('year'); // Get year from data attribute
            var currentYear = new Date().getFullYear();
            
            var show = true;

            // Filter User (Admin/Manager only typically)
            if (fUser && trUser !== fUser) show = false;
            
            // Filter Bulan
            if (show && fBulan) {
                // Check Month
                if (trBulan != fBulan) {
                    show = false;
                }
                // Also Check Year (Match Current Year to align with 'Database Baru' badge)
                // Use loose equality in case types differ
                else if (trYear != currentYear) {
                    show = false;
                }
            }
            
            // Filter Sumber
            if (show && fSumber && trSumber !== fSumber) show = false;
            
            // Filter Kelas
            if (show && fKelas && trKelas !== fKelas) show = false;
            
            // Search
            if (show && search && !trText.includes(search)) show = false;
            
            $tr.toggle(show);
        });

        // Admin Stats Update
        if ((userRole === "administrator" || userRole === "manager")) {
             // If we want detailed stats update based on filterUser
             var statUser = fUser || '';
             $.ajax({
                url: "{{ route('admin.database.statistik') }}", // Ensure this route exists and returns JSON
                type: "GET",
                data: { user: statUser },
                success: function(res) {
                    $('#badge-database-baru').text(`Database Baru (${res.bulanLabel}): ${res.databaseBaru}`);
                    $('#badge-total').text(`Total Database: ${res.totalDatabase}`);
                    $('#badge-target').text(`Target: ${res.target}`);
                    $('#badge-kurang').text(`Kurang: ${res.kurang}`);
                }
            });
        }
    }

    // Bind Events
    $('#filterUser, #filterBulan, #filterSumber, #filterKelas').on('change', applyFilters);
    $('#tableSearch').on('keyup input', applyFilters);

    // Run on load
    applyFilters();
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
                                    <option value="Marketing">Marketing</option>
                                    <option value="Iklan">Iklan</option>
                                    <option value="Alumni">Alumni</option>
                                    <option value="Mandiri">Mandiri</option>
                                </select>
                            </th>

                            <script>
                                // Script filter dipindahkan ke master filter di atas
                            </script>

                            @if(strtolower(auth()->user()->role) !== 'administrator')
            <th>
                Provinsi <br>
                <select id="filterProvinsi" class="form-control form-control-sm" style="min-width: 150px;">
                    <option value="">-- Semua Provinsi --</option>
                </select>
            </th>
        @endif
                            <th>
                                Kota <br>
                                <select id="filterKota" class="form-control form-control-sm" style="min-width: 150px;">
                                    <option value="">-- Semua Kota --</option>
                                </select>
                            </th>
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
                            @if(in_array(strtolower(auth()->user()->role), ['administrator', 'manager']) || auth()->user()->name === 'Agus Setyo')
                            <th>
                                <div class="d-flex flex-column">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_by', 'order' => (request('sort_by') == 'created_by' && request('order') == 'asc') ? 'desc' : 'asc']) }}" class="text-white text-decoration-none d-flex align-items-center justify-content-between mb-1">
                                        <span>Input Oleh</span>
                                        <span>
                                            @if(request('sort_by') == 'created_by')
                                                <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fas fa-sort text-white-50"></i>
                                            @endif
                                        </span>
                                    </a>
                                    <select class="form-control form-control-sm text-dark" onchange="updateFilterUser(this.value)" style="min-width: 100px;">
                                        <option value="">-- Semua --</option>
                                        @foreach($csList as $cs)
                                            <option value="{{ $cs->name }}" {{ request('cs_name') == $cs->name ? 'selected' : '' }}>
                                                {{ $cs->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </th>

                            <th>Role</th>
                            @endif
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($data as $item)
                            @include('admin.database.partials.row', ['item' => $item, 'loop' => $loop, 'kelas' => $kelas])
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
                                // error: function() {
                                //     alert('Gagal memuat data filter');
                                // }
                            });
                        });
                    });
                </script>


                <!-- Script JQuery -->
                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script>
                    $(document).ready(function() {

                        // Untuk kolom text
                        $(document).on('blur', '.editable', function() {
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
                        $(document).on('change', '.select-potensi', function() {
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

<script>
// Delegated event for Sumber Leads Select
$(document).on('change', '.select-sumber', function() {
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

function createNewRow(e) {
    if(e) e.preventDefault();
    
    $.ajax({
        url: '{{ route("admin.database.createDraft") }}',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(response) {
            if(response.success) {
                // Prepend to tbody
                $('#myTable tbody').prepend(response.html);
                
                let $newRow = $('#myTable tbody tr:first');
                
                // Populate Provinces for the new row
                if(window.populateProvinceRow) {
                    window.populateProvinceRow($newRow);
                }
                
                // Optional: Highlight row or focus name
                $newRow.css('background-color', '#d4edda').animate({backgroundColor: '#fff'}, 2000);
            }
        },
        error: function(xhr) {
            let msg = 'Gagal menambah baris baru.';
            if(xhr.responseJSON && xhr.responseJSON.message) {
                msg += '\n' + xhr.responseJSON.message;
            }
            alert(msg);
        }
    });
}
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

<script>
$(document).ready(function() {
    // Global variables to cache default province list
    let cachedProvinces = [];

    // Helper: Populate specific select elements
    function populateProvinceSelect($elements) {
        if(cachedProvinces.length === 0) return;

        $elements.each(function() {
            let $select = $(this);
            // check if already populated to avoid potential overwrite issues if logic changes
            if($select.children('option').length > 1) return; 

            let currentNama = $select.data('nama');
            
            // Keep existing "Pilih" if exists
            let $default = $select.find('option:first');
            $select.empty().append($default);

            cachedProvinces.forEach(function(prov) {
                let isSelected = (currentNama && currentNama.toUpperCase() === prov.name.toUpperCase()) ? 'selected' : '';
                $select.append(`<option value="${prov.id}" data-name="${prov.name}" ${isSelected}>${prov.name}</option>`);
            });
        });
    }

    // 1. Fetch Provinces & Populate
    $.getJSON('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json', function(provinces) {
        // Sort: Alphabetical
        provinces.sort((a, b) => a.name.localeCompare(b.name));
        cachedProvinces = provinces;

        // Populate existing rows
        populateProvinceSelect($('.select-provinsi'));
        
        // Also populate Header Filter
        let $filterProv = $('#filterProvinsi');
        cachedProvinces.forEach(function(prov) {
             // Avoid duplicate append if run multiple times
             if($filterProv.find(`option[value="${prov.name}"]`).length === 0) {
                 $filterProv.append(`<option value="${prov.name}" data-id="${prov.id}">${prov.name}</option>`);
             }
        });
    });

    // Expose populate function purely for local usage pattern if needed, 
    // but better to attach a listener or just call it from createNewRow.
    
    // We attach it to window so createNewRow can access it if defined outside (though it is defined outside doc.ready)
    window.populateProvinceRow = function($row) {
         if(cachedProvinces.length > 0) {
             populateProvinceSelect($row.find('.select-provinsi'));
         } else {
             // retry if not yet loaded? usually loaded by the time user clicks add
         }
    };

    // 2. Change Province -> Find Cities & Save
    $(document).on('change', '.select-provinsi', function() {
        let $select = $(this);
        let id = $select.data('id');
        let provId = $select.val();
        let provName = $select.find(':selected').data('name');
        
        let $kotaSelect = $select.closest('tr').find('.select-kota');
        
        // Save to DB
        if(provId) {
             $.post('/admin/database/update-location', {
                _token: '{{ csrf_token() }}',
                id: id,
                provinsi_id: provId,
                provinsi_nama: provName
            }).done(function() {
                 console.log('Provinsi saved');
            });
            
            // Load Cities
            loadCities(provId, $kotaSelect);
        } else {
            $kotaSelect.empty().append('<option value="">-- Pilih Kota --</option>');
        }
    });

    // 3. Change City -> Save
    $(document).on('change', '.select-kota', function() {
        let $select = $(this);
        let id = $select.data('id');
        let kotaId = $select.val();
        let kotaName = $select.find(':selected').data('name');

        if(kotaId) {
            $.post('/admin/database/update-location', {
                 _token: '{{ csrf_token() }}',
                 id: id,
                 kota_id: kotaId,
                 kota_nama: kotaName
            }).done(function() {
                 console.log('Kota saved');
            });
        }
    });

    // 4. Lazy Load Cities on Click (if not populated)
    $(document).on('click', '.select-kota', function() {
        let $kotaSelect = $(this);
        // Only load if we haven't loaded options yet (length <= 1 means only default option)
        // And ensure we have a province selected
        if($kotaSelect.children('option').length <= 1) {
             let $provSelect = $kotaSelect.closest('tr').find('.select-provinsi');
             let provId = $provSelect.val();
             
             if(provId) {
                 loadCities(provId, $kotaSelect);
             } else {
                 // Try to resolve province ID from its text if user hasn't touched it? 
                 // Difficult because we haven't mapped ID to the initial text unless content matched.
                 if($provSelect.find('option:selected').val()) {
                     loadCities($provSelect.find('option:selected').val(), $kotaSelect);
                 }
             }
        }
    });

    function loadCities(provId, $targetSelect) {
        $targetSelect.empty().append('<option value="">Loading...</option>');
        
        $.getJSON(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provId}.json`, function(cities) {
             cities.sort((a, b) => a.name.localeCompare(b.name));
             
             $targetSelect.empty().append('<option value="">-- Pilih Kota --</option>');
             
             let currentKota = $targetSelect.data('nama');
             
             cities.forEach(function(city) {
                 let isSelected = (currentKota && currentKota.toUpperCase() === city.name.toUpperCase()) ? 'selected' : '';
                 $targetSelect.append(`<option value="${city.id}" data-name="${city.name}" ${isSelected}>${city.name}</option>`);
             });
        });
    }

    // ==========================================
    // FILTER HEADER (Baru)
    // ==========================================
    
    // A. Populate Header Filter Provinsi
    $.getJSON('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json', function(provinces) {
        provinces.sort((a, b) => a.name.localeCompare(b.name));
        let $filterProv = $('#filterProvinsi');
        
        // Prevent duplicates (in case other scripts populated it)
        $filterProv.find('option:not(:first)').remove();

        provinces.forEach(function(prov) {
            $filterProv.append(`<option value="${prov.name}" data-id="${prov.id}">${prov.name}</option>`);
        });

        // Initialize Select2 with search
        $filterProv.select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: "-- Semua Provinsi --",
            allowClear: true
        });
    });

    // B. Event Listener Filter Provinsi
    $('#filterProvinsi').on('change', function() {
        let selectedProvName = $(this).val();
        let selectedProvId = $(this).find(':selected').data('id');
        let $filterKota = $('#filterKota');
        
        // 1. Reset & Reload Kota Filter
        $filterKota.empty().append('<option value="">-- Semua Kota --</option>');
        
        if(selectedProvId) {
            $.getJSON(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${selectedProvId}.json`, function(cities) {
                 cities.sort((a, b) => a.name.localeCompare(b.name));
                 cities.forEach(function(city) {
                     $filterKota.append(`<option value="${city.name}">${city.name}</option>`);
                 });
            });
        }
        
        // 2. Trigger Main Filter
        applyTableFilters();
    });

    // C. Event Listener Filter Kota
    $('#filterKota').on('change', function() {
        applyTableFilters();
    });

    // D. Main Filtering Logic (Combines existing logic)
    function applyTableFilters() {
         var userRole = "{{ strtolower(auth()->user()->role) }}";
         
         // Get values
         var fUser = $('#filterUser').val() ? $('#filterUser').val().toLowerCase() : '';
         var fBulan = $('#filterBulan').val();
         var fSumber = $('#filterSumber').val();
         var fKelas = $('#filterKelas').val();
         // Header Filters
         var fProv = $('#filterProvinsi').val();
         var fKota = $('#filterKota').val();
         
         var search = $('#tableSearch').val() ? $('#tableSearch').val().toLowerCase() : '';
 
         $('#myTable tbody tr').each(function() {
             var $tr = $(this);
             var trUser = $tr.data('created-by'); 
             var trBulan = $tr.data('bulan');
             var trYear = $tr.data('year');
             var currentYear = new Date().getFullYear();
             
             // Row Values
             var trText = $tr.text().toLowerCase();
             var trSumber = $tr.find('.select-sumber').val();
             
             // Get Province/City from the dropdown data (most accurate) or text if fallback
             var $trProvSelect = $tr.find('.select-provinsi');
             var trProvinsi = $trProvSelect.length ? $trProvSelect.data('nama') : $tr.find('td[data-field="provinsi_nama"]').text();
             
             var $trKotaSelect = $tr.find('.select-kota');
             var trKota = $trKotaSelect.length ? $trKotaSelect.data('nama') : $tr.find('td[data-field="kota_nama"]').text();

             // Normalizing string for comparison (uppercase/trim)
             if(trProvinsi) trProvinsi = trProvinsi.trim();
             if(trKota) trKota = trKota.trim();
             
             var show = true;
 
             // Filter User
             if (fUser && trUser !== fUser) show = false;
             
             // Filter Bulan
             if (show && fBulan) {
                 if (trBulan != fBulan) show = false;
                 else if (trYear != currentYear) show = false;
             }
             
             // Filter Sumber
             if (show && fSumber && trSumber !== fSumber) show = false;
             
             // Filter Kelas (existing logic already covers this or we add it if not)
             var trKelas = '';
             var $kelasSelect = $tr.find('.select-potensi');
             if ($kelasSelect.length > 0) {
                 trKelas = $kelasSelect.find('option:selected').text().trim();
             }
             if (show && fKelas && trKelas !== fKelas) show = false;
             
             // --- NEW FILTERS ---
             // Filter Provinsi
             if (show && fProv && trProvinsi !== fProv) show = false;
             
             // Filter Kota
             if (show && fKota && trKota !== fKota) show = false;
             
             // Search
             if (show && search && !trText.includes(search)) show = false;
             
             $tr.toggle(show);
         });
    }
    
    // Hook into existing events to also call our unified filter
    $('#filterSumber, #filterKelas').on('change', applyTableFilters);
    
    // Note: older applyFilters function defined in document.ready above might conflict if not careful.
    // We are overriding or extending functionality. The previous script block used "applyFilters" name. 
    // Since we are inside the same doc.ready (effectively), we should be careful. 
    // To be safe, we'll assume the previous separate scripts might need consolidation, 
    // but typically later script specific listeners will run.
    // We explicitly attach applyTableFilters to the new inputs.
});
</script>
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
{{--    <script>
        $(document).ready(function() {
            $('#myTable').DataTable({
                responsive: true,
                autoWidth: false,
            });
        });
    </script> --}}



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
                            <option value="Marketing">Marketing</option>
                            <option value="Iklan">Iklan</option>
                            <option value="Alumni">Alumni</option>
                            <option value="Mandiri">Mandiri</option>
                        </select>
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
    // Logic filter kelas sudah digabung di applyFilters()
</script>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<!-- ✅ Tambahkan ini di atas tabel kamu -->
<link rel="stylesheet" 
      href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
