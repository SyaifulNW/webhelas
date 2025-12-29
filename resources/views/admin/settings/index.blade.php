@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid">
    <h3 class="fw-bold mb-4">Pengaturan Administrator</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
        </div>
    @endif

    <ul class="nav nav-tabs" id="settingTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="users-tab" data-toggle="tab" href="#users" role="tab">Users & Roles</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="target-tab" data-toggle="tab" href="#target" role="tab">Target Omset</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="menus-tab" data-toggle="tab" href="#menus" role="tab">Akses Menu</a>
        </li>
    </ul>

    <div class="tab-content" id="settingTabContent">
        
        {{-- TAB 1: USER MANAGEMENT --}}
        <div class="tab-pane fade show active p-3 bg-white border border-top-0" id="users" role="tabpanel">
            <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addUserModal">
                <i class="fas fa-plus"></i> Tambah User Baru
            </button>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>ID</th> <th>Nama</th> <th>Email</th> <th>Role</th> <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                        <tr>
                            <td>{{ $u->id }}</td>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td><span class="badge badge-info">{{ ucfirst($u->role) }}</span></td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editUserModal{{ $u->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.settings.users.destroy', $u->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus user ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editUserModal{{ $u->id }}" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit User - {{ $u->name }}</h5>
                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                    </div>
                                    <form action="{{ route('admin.settings.users.update', $u->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Nama</label>
                                                <input type="text" name="name" class="form-control" value="{{ $u->name }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" name="email" class="form-control" value="{{ $u->email }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Role</label>
                                                <select name="role" class="form-control" required>
                                                    @foreach(['administrator','marketing','cs-smi','manager','hrd','user'] as $r)
                                                        <option value="{{ $r }}" {{ $u->role == $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Password (Kosongkan jika tidak diganti)</label>
                                                <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB 2: TARGET OMSET --}}
        <div class="tab-pane fade p-3 bg-white border border-top-0" id="target" role="tabpanel">
            <form action="{{ route('admin.settings.target.update') }}" method="POST" class="col-md-6">
                @csrf
                <div class="form-group">
                    <label class="fw-bold">Target Omset Saat Ini (Rp)</label>
                    <input type="number" name="target_omset" class="form-control" value="{{ $targetOmset }}" required>
                    <small class="text-muted">Target ini akan digunakan untuk perhitungan bonus semua CS secara default kecuali diatur lain.</small>
                </div>
                
                <div class="form-group mt-3">
                    <label class="fw-bold">Target Omset Start-Up Muda Indonesia (Rp)</label>
                    <input type="number" name="target_omset_smi" class="form-control" value="{{ $targetOmsetSmi ?? 0 }}" required>
                    <small class="text-muted">Target khusus untuk Start-Up Muda Indonesia (SMI).</small>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Target</button>
            </form>
        </div>

        {{-- TAB 3: MENUS MANAGEMENT --}}
        <div class="tab-pane fade p-3 bg-white border border-top-0" id="menus" role="tabpanel">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Matikan toggle untuk menyembunyikan menu dari sidebar.
            </div>
            <table class="table table-bordered">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>Label Menu</th> <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($menus as $m)
                    <tr>
                        <td>{{ $m->label }}</td>
                        <td>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input menu-toggle" 
                                    id="switch{{ $m->id }}" 
                                    data-id="{{ $m->id }}" 
                                    {{ $m->is_active ? 'checked' : '' }}>
                                <label class="custom-control-label" for="switch{{ $m->id }}">
                                    {{ $m->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </label>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

{{-- Add User Modal --}}
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah User Baru</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('admin.settings.users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="user">User</option>
                            <option value="marketing">Marketing</option>
                            <option value="cs-smi">CS SMI</option>
                            <option value="manager">Manager</option>
                            <option value="hrd">HRD</option>
                            <option value="administrator">Administrator</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan User</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // AJAX Toggle Menu
    document.querySelectorAll('.menu-toggle').forEach(item => {
        item.addEventListener('change', event => {
            const id = event.target.dataset.id;
            const active = event.target.checked ? 1 : 0;
            const label = event.target.nextElementSibling;
            
            label.textContent = active ? 'Aktif' : 'Non-Aktif';

            fetch('{{ route('admin.settings.menus.toggle') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id: id, active: active })
            })
            .then(response => response.json())
            .then(data => {
                if(!data.success) alert('Gagal mengubah status menu');
            })
            .catch(error => console.error('Error:', error));
        });
    });
</script>
@endsection
