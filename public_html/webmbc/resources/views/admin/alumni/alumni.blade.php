@extends('layouts.masteradmin')
@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Database Alumni</h1>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item active">Database Alumni</li>
        </ol>
    </div>
</div>
<div class="content">
    <div class="card card-info card-outline">
        <div class="card-header">
            <div class="card-tools d-flex justify-content-between w-100">
                <button class="btn btn-success" data-toggle="modal" data-target="#createAlumniModal">
                    Tambah Alumni &nbsp;<i class="fa-solid fa-plus"></i>
                </button>
                <input type="text" id="tableSearch" class="form-control w-auto" placeholder="Cari...">
            </div>
        </div>

   <script>
                document.getElementById('tableSearch').addEventListener('keyup', function() {
                    let filter = this.value.toLowerCase(); // kata kunci pencarian
                    let rows = document.querySelectorAll('#myTable tbody tr'); // semua baris tabel

                    rows.forEach(function(row) {
                        let text = row.textContent.toLowerCase(); // gabungan semua teks dalam baris
                        row.style.display = text.includes(filter) ? '' : 'none'; // tampilkan / sembunyikan
                    });
                });
            </script>

        <div class="card-body">
            <div style="overflow-x: auto; overflow-y: auto; max-height: 500px;">
                <table id="myTable" class="table table-bordered table-striped nowrap" style="width:max-content;">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Peserta</th>
                            <th>Sumber Leads</th>
                            <th>Provinsi</th>
                            <th>Kota</th>
                            <th>Nama Bisnis</th>
                            <th>No.WA</th>
                            <th>Kendala</th>
                            <th>Sudah Pernah Ikut Kelas</th>
                            <th>Kelas Yang Belum Ikut</th>
                            <th>Potensi Kelas Selanjutnya</th>
               
                            @if(auth()->user()->email == 'mbchamasah@gmail.com')
                            <th>Input Oleh</th>
                            @endif
                                 
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alumni as $item)
                        <tr data-id="{{ $item->id }}">
                            <td>{{ $loop->iteration }}</td>
                            <td contenteditable="true" class="editable" data-field="nama">{{ $item->nama }}</td>
                            <td contenteditable="true" class="editable" data-field="leads">{{ $item->leads }}</td>
                            <td contenteditable="true" class="editable" data-field="provinsi_nama">{{ $item->provinsi_nama }}</td>
                            <td contenteditable="true" class="editable" data-field="kota_nama">{{ $item->kota_nama }}</td>
                            <td contenteditable="true" class="editable" data-field="nama_bisnis">{{ $item->nama_bisnis }}</td>
                            <td contenteditable="true" class="editable" data-field="no_wa">{{ $item->no_wa }}</td>
                            <td contenteditable="true" class="editable" data-field="kendala">{{ $item->kendala }}</td>

                            <td>
                                <select multiple class="form-control form-control-sm select-kelas" data-id="{{ $item->id }}" data-field="sudah_pernah_ikut_kelas_apa_saja[]">
                                    @foreach($kelas as $k)
                                    <option value="{{ $k->id }}" {{ in_array($k->id, (array)($item->sudah_pernah_ikut_kelas_apa_saja ?? [])) ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>

                            <td>
                                <select multiple class="form-control form-control-sm select-kelas" data-id="{{ $item->id }}" data-field="kelas_yang_belum_diikuti_apa_saja[]">
                                    @foreach($kelas as $k)
                                    <option value="{{ $k->id }}" {{ in_array($k->id, (array)($item->kelas_yang_belum_diikuti_apa_saja ?? [])) ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>Kelas Selanjutnya</td>
                            @if(auth()->user()->email == 'mbchamasah@gmail.com')
                            <td>{{ $item->created_by }}</td>
                            @endif

                            <td>
                                <a href="{{ route('admin.alumni.show', $item->id) }}" class="btn btn-info btn-sm">
                                    <i class="fa-solid fa-eye" style="color:white;"></i>
                                </a>
                                <form action="{{ route('delete-alumni', $item->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fa-solid fa-trash" style="color:white;"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <style>
                    .editable {
                        cursor: pointer;
                    }

                    .editing {
                        background-color: #fff3cd !important;
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
                        $('.editable').on('focus', function() {
                            $(this).addClass('editing');
                        }).on('blur', function() {
                            let $this = $(this);
                            let value = $this.text();
                            let field = $this.data('field');
                            let id = $this.closest('tr').data('id');
                            $this.removeClass('editing');

                            $.post('/admin/alumni/update-inline', {
                                    _token: '{{ csrf_token() }}',
                                    id: id,
                                    field: field,
                                    value: value
                                }).done(() => showStatusIcon($this, true))
                                .fail(() => showStatusIcon($this, false));
                        });

                        $('.select-kelas').on('change', function() {
                            let $this = $(this);
                            let id = $this.data('id');
                            let field = $this.data('field');
                            let value = $this.val();
                            $.post('/admin/alumni/update-kelas', {
                                    _token: '{{ csrf_token() }}',
                                    id: id,
                                    field: field,
                                    value: value
                                }).done(() => showStatusIcon($this, true))
                                .fail(() => showStatusIcon($this, false));
                        });

                        function showStatusIcon($element, success) {
                            let iconHtml = success ?
                                '<i class="fa fa-check status-success"></i>' :
                                '<i class="fa fa-times status-error"></i>';
                            let iconSpan = $('<span class="status-icon">' + iconHtml + '</span>');
                            $element.after(iconSpan);
                            setTimeout(() => iconSpan.fadeOut(300, () => iconSpan.remove()), 2000);
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




<!-- End Modal Create -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Optional: Bootstrap 4 Theme -->
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />