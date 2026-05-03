@extends('layouts.masteradmin')

@section('content')
<style>
    .table-reseller {
        border-collapse: separate !important;
        border-spacing: 0;
        border: 1px solid #dee2e6 !important;
        border-radius: 8px;
        overflow: hidden;
    }
    .table-reseller thead th {
        background-color: #f8f9fc;
        border-bottom: 2px solid #e3e6f0 !important;
        color: #4e73df;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 15px !important;
    }
    .table-reseller td {
        padding: 12px 15px !important;
        border-color: #edf0f5 !important;
        vertical-align: middle !important;
    }
    .table-reseller tbody tr:hover {
        background-color: rgba(78, 115, 223, 0.05) !important;
    }
    .badge-closing {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        box-shadow: 0 4px 6px rgba(78, 115, 223, 0.2);
    }
    .btn-action {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .btn-action:hover {
        transform: translateY(-2px);
    }
</style>

<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-3">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">Manajemen Agen</h1>
            <p class="text-muted small mb-0"><i class="fas fa-map-marker-alt mr-1"></i> Wilayah Chapter: <strong>{{ $chapter }}</strong></p>
        </div>
        <button class="btn btn-primary shadow-sm fw-bold" data-toggle="modal" data-target="#modalTambahReseller">
            <i class="fas fa-user-plus mr-2"></i> Tambah Agen
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header py-3 bg-white d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-users mr-2"></i> Daftar Agen Terdaftar</h6>
                    <span class="badge bg-light text-primary border px-2 py-1">{{ count($downlines ?? []) }} Agen</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle table-reseller">
                            <thead>
                                <tr>
                                    <th width="50" class="text-center">No</th>
                                    <th>Nama Agen</th>
                                    <th>Informasi Kontak</th>
                                    <th class="text-center">Performa</th>
                                    <th class="text-end">Kontribusi Omset</th>
                                    <th class="text-center" width="180">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($downlines as $reseller)
                                <tr>
                                    <td class="text-center font-weight-bold text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $reseller->name }}</div>
                                        <div class="small text-muted">{{ $reseller->email }}</div>
                                    </td>
                                    <td>
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $reseller->wa) }}" target="_blank" class="text-success fw-bold text-decoration-none">
                                            <i class="fab fa-whatsapp mr-1"></i> {{ $reseller->wa }}
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-closing text-white px-3 py-2 rounded-pill">
                                            {{ $reseller->total_closing ?? 0 }} Closing
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-success font-monospace">
                                        Rp {{ number_format($reseller->total_omset ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center">
                                            <a href="{{ route('reseller.setting.show', $reseller->id) }}" class="btn btn-info btn-sm btn-action mr-2" title="Detail Performa">
                                                <i class="fas fa-eye text-white"></i>
                                            </a>
                                            <button type="button" class="btn btn-warning btn-sm btn-action mr-2" data-toggle="modal" data-target="#modalEditReseller{{ $reseller->id }}" title="Ubah Data">
                                                <i class="fas fa-edit text-white"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm btn-action" onclick="confirmDelete('{{ $reseller->id }}', '{{ $reseller->name }}')">
                                                <i class="fas fa-trash text-white"></i>
                                            </button>
                                        </div>
                                        <form id="delete-form-{{ $reseller->id }}" action="{{ route('reseller.setting.destroy-reseller', $reseller->id) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                
                                <!-- Modal Edit Reseller -->
                                <div class="modal fade" id="modalEditReseller{{ $reseller->id }}" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel{{ $reseller->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg shadow" role="document">
                                        <div class="modal-content border-0">
                                            <div class="modal-header bg-warning text-dark">
                                                <h5 class="modal-title font-weight-bold" id="modalEditLabel{{ $reseller->id }}"><i class="fas fa-edit mr-2"></i> Ubah Data Agen: {{ $reseller->name }}</h5>
                                                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('reseller.setting.update-reseller', $reseller->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body p-4">
                                                    <div class="row g-3">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label fw-bold small text-muted">Nama Lengkap</label>
                                                            <input type="text" name="name" class="form-control bg-light border-0" value="{{ $reseller->name }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label fw-bold small text-muted">Nomor WhatsApp</label>
                                                            <input type="text" name="wa" class="form-control bg-light border-0" value="{{ $reseller->wa }}" required>
                                                        </div>
                                                        <div class="col-md-12 mb-3">
                                                            <label class="form-label fw-bold small text-muted">Email</label>
                                                            <input type="email" name="email" class="form-control bg-light border-0" value="{{ $reseller->email }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label fw-bold small text-muted">Password Baru (Opsional)</label>
                                                            <div class="input-group">
                                                                <input type="password" name="password" id="edit_password{{ $reseller->id }}" class="form-control bg-light border-0" placeholder="Kosongkan jika tidak ingin diubah">
                                                                <button class="btn btn-light border-0" type="button" onclick="togglePassword('edit_password{{ $reseller->id }}')">
                                                                    <i class="fas fa-eye" id="eye-edit_password{{ $reseller->id }}"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light border-0">
                                                    <button type="button" class="btn btn-secondary px-4 fw-bold" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-warning px-5 fw-bold shadow-sm">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-user-slash fa-3x mb-3 opacity-25"></i>
                                            <p>Belum ada agen yang Anda rekrut di Chapter {{ $chapter }}.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Agen -->
<div class="modal fade" id="modalTambahReseller" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg shadow" role="document">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="modalLabel"><i class="fas fa-user-plus mr-2"></i> Registrasi Agen Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('reseller.setting.store-reseller') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control bg-light border-0" placeholder="Contoh: Budi Santoso" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Nomor WhatsApp</label>
                            <input type="text" name="wa" class="form-control bg-light border-0" placeholder="Contoh: 081234567890" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold small text-muted">Email</label>
                            <input type="email" name="email" class="form-control bg-light border-0" placeholder="budi@example.com" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control bg-light border-0" placeholder="Min. 8 karakter" required>
                                <button class="btn btn-light border-0" type="button" onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="eye-password"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">Konfirmasi Password</label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control bg-light border-0" placeholder="Ulangi password" required>
                                <button class="btn btn-light border-0" type="button" onclick="togglePassword('password_confirmation')">
                                    <i class="fas fa-eye" id="eye-password_confirmation"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary px-4 fw-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">Simpan & Daftarkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Agen?',
            text: "Anda yakin ingin menghapus " + name + "? Data performa akan tetap ada namun akun agen tidak bisa diakses.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            backdrop: `rgba(0,0,123,0.4)`
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }

    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const eyeIcon = document.getElementById('eye-' + inputId);
        if (input.type === "password") {
            input.type = "text";
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }
</script>
@endsection