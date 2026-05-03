@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mb-4"><i class="fa-solid fa-list-check me-2"></i> Manajemen Kelas</h1>

    {{-- ✅ Alert sukses --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ✅ Tombol tambah --}}
    <div class="mb-3 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKelas">
            <i class="fa-solid fa-plus"></i> Tambah Kelas
        </button>
    </div>

    {{-- ✅ Tabel kelas --}}
    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Nama Kelas</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelas as $index => $k)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="fw-semibold">{{ $k->nama_kelas }}</td>
                            <td>{{ \Carbon\Carbon::parse($k->tanggal_mulai)->format('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($k->tanggal_selesai)->format('d M Y') }}</td>
                            <td class="text-start">{{ $k->deskripsi ?? '-' }}</td>
                            <td>
                                <button 
                                    class="btn btn-warning btn-sm btn-edit"
                                    data-id="{{ $k->id }}"
                                    data-nama="{{ $k->nama_kelas }}"
                                    data-mulai="{{ $k->tanggal_mulai }}"
                                    data-selesai="{{ $k->tanggal_selesai }}"
                                    data-deskripsi="{{ $k->deskripsi }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditKelas"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted fst-italic">Belum ada data kelas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ✅ Modal Tambah Kelas --}}
<div class="modal fade" id="modalTambahKelas" tabindex="-1" aria-labelledby="modalTambahKelasLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="{{ route('admin.kelas.store') }}">
      @csrf
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="fa-solid fa-plus-circle me-2"></i> Tambah Kelas</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Kelas</label>
            <input type="text" name="nama_kelas" class="form-control" required>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tanggal Mulai</label>
              <input type="date" name="tanggal_mulai" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tanggal Selesai</label>
              <input type="date" name="tanggal_selesai" class="form-control" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success"><i class="fa-solid fa-save me-1"></i> Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- ✅ Modal Edit Kelas --}}
<div class="modal fade" id="modalEditKelas" tabindex="-1" aria-labelledby="modalEditKelasLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" id="formEditKelas">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i> Edit Kelas</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Kelas</label>
            <input type="text" name="nama_kelas" id="edit_nama_kelas" class="form-control" required>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tanggal Mulai</label>
              <input type="date" name="tanggal_mulai" id="edit_tanggal_mulai" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tanggal Selesai</label>
              <input type="date" name="tanggal_selesai" id="edit_tanggal_selesai" class="form-control" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Deskripsi</label>
            <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-warning"><i class="fa-solid fa-save me-1"></i> Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- ✅ Script handle edit modal --}}
<script>


document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.btn-edit');
    buttons.forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            document.getElementById('edit_nama_kelas').value = this.dataset.nama;
            document.getElementById('edit_tanggal_mulai').value = this.dataset.mulai;
            document.getElementById('edit_tanggal_selesai').value = this.dataset.selesai;
            document.getElementById('edit_deskripsi').value = this.dataset.deskripsi;
      document.getElementById('formEditKelas').action = `/admin/kelas/${id}`;
        });
    });
});
</script>
@endsection
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

