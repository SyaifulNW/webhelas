<div class="modal fade" id="editUserModal{{ $u->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content text-left">
            <div class="modal-header bg-warning">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i>Edit User - {{ $u->name }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('admin.settings.users.update', $u->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group font-weight-bold">
                        <label>Nama</label>
                        <input type="text" name="name" class="form-control" value="{{ $u->name }}" required>
                    </div>
                    <div class="form-group font-weight-bold">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $u->email }}" required>
                    </div>
                    <div class="form-group font-weight-bold">
                        <label>Role</label>
                        <select name="role" class="form-control role-select" required>
                            @foreach($roles as $r)
                                <option value="{{ $r }}" {{ $u->role == $r ? 'selected' : '' }}>
                                    @if($r == 'cs-mbc') CS MBC
                                    @elseif($r == 'cs-smi') CS SMI
                                    @else {{ ucfirst($r) }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group chapter-field-container font-weight-bold"
                        style="display: {{ in_array($u->role, ['chapter', 'reseller']) ? 'block' : 'none' }};">
                        <label>Pilih Chapter</label>
                        <select name="chapter" class="form-control chapter-select" data-current="{{ $u->chapter }}">
                            <option value="">-- Pilih Chapter --</option>
                            @foreach(['Cirebon', 'Kalimantan Timur', 'Depok', 'Jakarta', 'Makassar', 'Tangerang', 'Lampung', 'Kediri'] as $chap)
                                <option value="{{ $chap }}" {{ ($u->chapter ?? '') == $chap ? 'selected' : '' }}>
                                    {{ $chap }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group font-weight-bold">
                        <label>Password (Kosongkan jika tidak diganti)</label>
                        <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning font-weight-bold">Simpan Perubahan</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>