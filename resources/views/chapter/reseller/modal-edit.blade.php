<!-- Modal Edit Reseller -->
<div class="modal fade" id="modalEditReseller{{ $reseller->id }}" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel{{ $reseller->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg shadow" role="document">
        <div class="modal-content border-0">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalEditLabel{{ $reseller->id }}"><i class="fas fa-edit mr-2"></i> Ubah Data Reseller: {{ $reseller->name }}</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('chapter.reseller.update', $reseller->id) }}" method="POST">
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
                            <input type="password" name="password" class="form-control bg-light border-0" placeholder="Kosongkan jika tidak ingin diubah">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning px-5 fw-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
