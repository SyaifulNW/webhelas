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
                <a class="nav-link" id="menus-tab" data-toggle="tab" href="#menus" role="tab">Menu Global</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="rolemenu-tab" data-toggle="tab" href="#rolemenu" role="tab">Akses Menu Role</a>
            </li>
        </ul>

        <div class="tab-content" id="settingTabContent">

            {{-- TAB 1: USER MANAGEMENT --}}
            <div class="tab-pane fade show active p-3 bg-white border border-top-0" id="users" role="tabpanel">
                <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addUserModal">
                    <i class="fas fa-plus"></i> Tambah User Baru
                </button>

                {{-- Table Pusat Helas --}}
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold"><i class="fas fa-building mr-2"></i>Pusat Helas</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="bg-secondary text-white">
                                    <tr>
                                        <th style="width: 50px;">ID</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th class="text-center">Transfer Database</th>
                                        <th class="text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($usersPusat as $u)
                                        <tr>
                                            <td>{{ $u->id }}</td>
                                            <td class="font-weight-bold">{{ $u->name }}</td>
                                            <td>{{ $u->email }}</td>
                                            <td>
                                                <span class="badge badge-info shadow-sm">{{ ucfirst($u->role) }}</span>
                                            </td>
                                            <td>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input user-toggle" id="userSwitch{{ $u->id }}"
                                                        data-id="{{ $u->id }}" {{ $u->is_active ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="userSwitch{{ $u->id }}">
                                                        {{ $u->is_active ? 'Aktif' : 'Non-Aktif' }}
                                                    </label>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-indigo shadow-sm btn-transfer-db" 
                                                    data-id="{{ $u->id }}" data-name="{{ $u->name }}">
                                                    <i class="fas fa-exchange-alt mr-1"></i> Transfer
                                                </button>
                                            </td>
                                            <td class="text-right">
                                                <button class="btn btn-sm btn-warning shadow-sm" data-toggle="modal"
                                                    data-target="#editUserModal{{ $u->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('admin.settings.users.destroy', $u->id) }}" method="POST"
                                                    class="d-inline delete-form">
                                                    @csrf @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-danger shadow-sm delete-btn">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        {{-- Modal Edit (Pusat) --}}
                                        @include('admin.settings.partials.edit_modal', ['u' => $u, 'roles' => $roles])
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Table Cabang Helas --}}
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold"><i class="fas fa-store mr-2"></i>Cabang Helas (Chapter & Reseller)
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th style="width: 50px;">ID</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Role / Lokasi</th>
                                        <th>Status</th>
                                        <th class="text-center">Transfer Database</th>
                                        <th class="text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $groupedCabang = $usersCabang->groupBy('chapter');
                                    @endphp
                                    @foreach($groupedCabang as $chapterName => $members)
                                        @php
                                            $leader = $members->where('role', 'chapter')->first();
                                            $staff = $members->where('role', 'reseller');
                                            $chapterId = Str::slug($chapterName ?: 'no-chapter');
                                        @endphp
                                        
                                        {{-- Header Chapter / Leader Row --}}
                                        <tr class="bg-light @if($staff->count() > 0) clickable-row @endif" 
                                            @if($staff->count() > 0) data-toggle="collapse" data-target="#members-{{ $chapterId }}" @endif
                                            style="cursor: pointer; transition: all 0.2s;">
                                            <td class="text-center">
                                                @if($staff->count() > 0)
                                                    <span class="btn btn-xs btn-primary rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 22px; height: 22px; padding: 0; font-size: 10px;">
                                                        <i class="fas fa-plus toggle-icon"></i>
                                                    </span>
                                                @else
                                                    <span class="text-muted">{{ $leader ? $leader->id : '-' }}</span>
                                                @endif
                                            </td>
                                            <td class="font-weight-bold">
                                                {{ $leader ? $leader->name : 'N/A' }} 
                                                <small class="text-muted d-block">{{ $chapterName ?: 'Tanpa Lokasi' }}</small>
                                            </td>
                                            <td>{{ $leader ? $leader->email : '-' }}</td>
                                            <td>
                                                <span class="badge badge-primary shadow-sm">Chapter</span>
                                                <span class="badge bg-white text-dark border shadow-sm ml-1">
                                                    <i class="fas fa-users-cog mr-1"></i>{{ $staff->count() }} Agen
                                                </span>
                                            </td>
                                            <td>
                                                @if($leader)
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input user-toggle" id="userSwitch{{ $leader->id }}"
                                                            data-id="{{ $leader->id }}" {{ $leader->is_active ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="userSwitch{{ $leader->id }}">
                                                            {{ $leader->is_active ? 'Aktif' : 'Non-Aktif' }}
                                                        </label>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($leader)
                                                    <button class="btn btn-sm btn-indigo shadow-sm btn-transfer-db" 
                                                        data-id="{{ $leader->id }}" data-name="{{ $leader->name }}">
                                                        <i class="fas fa-exchange-alt mr-1"></i> Transfer
                                                    </button>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if($leader)
                                                    <button class="btn btn-sm btn-warning shadow-sm" data-toggle="modal"
                                                        data-target="#editUserModal{{ $leader->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('admin.settings.users.destroy', $leader->id) }}" method="POST"
                                                        class="d-inline delete-form">
                                                        @csrf @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-danger shadow-sm delete-btn">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>

                                        {{-- Staff / Reseller Rows (Collapsible) --}}
                                        @if($staff->count() > 0)
                                            <tr id="members-{{ $chapterId }}" class="collapse">
                                                <td colspan="7" class="p-0">
                                                    <table class="table table-sm mb-0 bg-white">
                                                        <tbody style="border-left: 5px solid #4e73df;">
                                                            @foreach($staff as $u)
                                                                <tr>
                                                                    <td style="width: 50px; padding-left: 30px;">{{ $u->id }}</td>
                                                                    <td style="width: 25%;">
                                                                        <i class="fas fa-level-up-alt fa-rotate-90 text-muted mr-2"></i>
                                                                        {{ $u->name }}
                                                                    </td>
                                                                    <td style="width: 25%;">{{ $u->email }}</td>
                                                                    <td>
                                                                        <span class="badge badge-info shadow-sm">{{ ucfirst($u->role) }}</span>
                                                                    </td>
                                                                    <td>
                                                                        <div class="custom-control custom-switch">
                                                                            <input type="checkbox" class="custom-control-input user-toggle" id="userSwitch{{ $u->id }}"
                                                                                data-id="{{ $u->id }}" {{ $u->is_active ? 'checked' : '' }}>
                                                                            <label class="custom-control-label" for="userSwitch{{ $u->id }}">
                                                                                {{ $u->is_active ? 'Aktif' : 'Non-Aktif' }}
                                                                            </label>
                                                                        </div>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <button class="btn btn-xs btn-outline-indigo btn-transfer-db" 
                                                                            data-id="{{ $u->id }}" data-name="{{ $u->name }}">
                                                                            <i class="fas fa-exchange-alt mr-1"></i> Transfer
                                                                        </button>
                                                                    </td>
                                                                    <td class="text-right">
                                                                        <button class="btn btn-sm btn-outline-warning" data-toggle="modal"
                                                                            data-target="#editUserModal{{ $u->id }}">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                        <form action="{{ route('admin.settings.users.destroy', $u->id) }}" method="POST"
                                                                            class="d-inline delete-form">
                                                                            @csrf @method('DELETE')
                                                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        </form>
                                                                    </td>
                                                                </tr>
                                                                
                                                                {{-- Modal Edit (Cabang - Staff) --}}
                                                                @include('admin.settings.partials.edit_modal', ['u' => $u, 'roles' => $roles])
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        @endif

                                        {{-- Modal Edit (Leader) --}}
                                        @if($leader)
                                            @include('admin.settings.partials.edit_modal', ['u' => $leader, 'roles' => $roles])
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: TARGET OMSET --}}
            <div class="tab-pane fade p-3 bg-white border border-top-0" id="target" role="tabpanel">
                <form action="{{ route('admin.settings.target.update') }}" method="POST" class="col-md-6">
                    @csrf
                    <div class="form-group">
                        <label class="fw-bold">Target Omset Saat Ini (Rp)</label>
                        <input type="number" name="target_omset" class="form-control" value="{{ $targetOmset }}" required>
                        <small class="text-muted">Target ini akan digunakan untuk perhitungan bonus semua CS secara default
                            kecuali diatur lain.</small>
                    </div>

                    <div class="form-group mt-3">
                        <label class="fw-bold">Target Omset Start-Up Muda Indonesia (Rp)</label>
                        <input type="number" name="target_omset_smi" class="form-control" value="{{ $targetOmsetSmi ?? 0 }}"
                            required>
                        <small class="text-muted">Target khusus untuk Start-Up Muda Indonesia (SMI).</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Target</button>
                </form>
            </div>

            {{-- TAB 3: MENUS MANAGEMENT (GLOBAL) --}}
            <div class="tab-pane fade p-3 bg-white border border-top-0" id="menus" role="tabpanel">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Pengaturan ini akan mengaktifkan/menonaktifkan menu secara
                    <strong>GLOBAL</strong> untuk semua user.
                </div>
                <table class="table table-bordered">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>Label Menu</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menus as $m)
                            <tr>
                                <td>{{ $m->label }}</td>
                                <td>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input menu-toggle" id="switch{{ $m->id }}"
                                            data-id="{{ $m->id }}" {{ $m->is_active ? 'checked' : '' }}>
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

            {{-- TAB 4: ROLE MENU ACCESS --}}
            <div class="tab-pane fade p-3 bg-white border border-top-0" id="rolemenu" role="tabpanel">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Atur akses menu spesifik per Role. Jika Menu Global
                    non-aktif, maka menu tetap tidak muncul meski di sini aktif.
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-left">Role / Menu</th>
                                @foreach($menus as $m)
                                    <th>{{ $m->label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                                <tr>
                                    <td class="text-left font-weight-bold">{{ ucfirst($role) }}</td>
                                    @foreach($menus as $m)
                                        @php
                                            try {
                                                $canAccess = \DB::table('role_menus')
                                                    ->where('role', $role)
                                                    ->where('menu_id', $m->id)
                                                    ->value('can_access') ?? true;
                                            } catch (\Exception $e) {
                                                $canAccess = true; // Fallback if table doesn't exist
                                            }
                                        @endphp
                                        <td>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input role-menu-toggle"
                                                    id="role-{{ $role }}-{{ $m->id }}" data-role="{{ $role }}"
                                                    data-menuid="{{ $m->id }}" {{ $canAccess ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="role-{{ $role }}-{{ $m->id }}"></label>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
                            <select name="role" class="form-control role-select" required>
                                <option value="" disabled selected>-- Pilih Role --</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r }}">
                                        @if($r == 'cs-mbc') CS MBC
                                        @elseif($r == 'cs-smi') CS SMI
                                        @else {{ ucfirst($r) }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group chapter-field-container" style="display: none;">
                            <label>Pilih Chapter</label>
                            <select name="chapter" class="form-control chapter-select" data-current="">
                                <option value="">-- Pilih Chapter --</option>
                                @foreach(['Cirebon', 'Kalimantan Timur', 'Depok', 'Jakarta', 'Makassar', 'Tangerang', 'Lampung', 'Kediri'] as $chap)
                                    <option value="{{ $chap }}">{{ $chap }}</option>
                                @endforeach
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

    {{-- Modal Transfer Database --}}
    <div class="modal fade" id="transferDatabaseModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-exchange-alt mr-2"></i> Transfer Database</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="transferDatabaseForm">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i> <strong>Peringatan!</strong> Tindakan ini akan memindahkan semua data Calon Peserta dan Peserta M1T dari user sumber ke user tujuan.
                        </div>
                        
                        <input type="hidden" name="from_id" id="transfer_from_id">
                        <div class="form-group">
                            <label class="font-weight-bold">User Sumber:</label>
                            <p id="transfer_from_name" class="form-control-plaintext text-primary font-weight-bold ml-2"></p>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Transfer ke User Tujuan:</label>
                            <select name="to_id" id="transfer_to_id" class="form-control" required>
                                <option value="">-- Pilih User Tujuan --</option>
                                @foreach($usersPusat->merge($usersCabang)->sortBy('name') as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ ucfirst($user->role) }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitTransfer">
                            <i class="fas fa-check-circle mr-1"></i> Mulai Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // AJAX Toggle Menu Global
        document.querySelectorAll('.menu-toggle').forEach(item => {
            item.addEventListener('change', event => {
                const id = event.target.dataset.id;
                const active = event.target.checked ? 1 : 0;
                const label = event.target.nextElementSibling;

                label.textContent = active ? 'Aktif' : 'Non-Aktif';

                fetch('{{ Route::has('admin.settings.menus.toggle') ? route('admin.settings.menus.toggle') : '/admin/settings/menus/toggle' }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ id: id, active: active })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            Swal.fire('Gagal!', 'Gagal mengubah status menu', 'error');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });

        // AJAX Toggle Status User
        document.querySelectorAll('.user-toggle').forEach(item => {
            item.addEventListener('change', event => {
                const id = event.target.dataset.id;
                const active = event.target.checked ? 1 : 0;
                const label = event.target.nextElementSibling;

                label.textContent = active ? 'Aktif' : 'Non-Aktif';

                fetch('{{ route('admin.settings.users.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ id: id, active: active })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            Swal.fire('Gagal!', 'Gagal mengubah status user', 'error');
                            // Revert toggle if failed
                            event.target.checked = !active;
                            label.textContent = !active ? 'Aktif' : 'Non-Aktif';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error!', 'Terjadi kesalahan sistem', 'error');
                        // Revert toggle if failed
                        event.target.checked = !active;
                        label.textContent = !active ? 'Aktif' : 'Non-Aktif';
                    });
            });
        });

        // AJAX Toggle Role Menu Access
        document.querySelectorAll('.role-menu-toggle').forEach(item => {
            item.addEventListener('change', event => {
                const role = event.target.dataset.role;
                const menu_id = event.target.dataset.menuid;
                const active = event.target.checked ? 1 : 0;

                console.log(`Updating role ${role} menu ${menu_id} access to ${active}`);

                fetch('{{ Route::has('admin.settings.role-menus.update') ? route('admin.settings.role-menus.update') : '/admin/settings/role-menus/update' }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ role: role, menu_id: menu_id, active: active })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Beri feedback toast jika perlu
                        } else {
                            Swal.fire('Gagal!', 'Gagal mengubah akses menu role', 'error');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });

        // SweetAlert Konfirmasi Hapus
        $('.delete-btn').on('click', function (e) {
            e.preventDefault();
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Yakin hapus user ini?',
                text: "Data ini tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            })
        });

        // Toggle Chapter Visibility & Clean/Dirty Labels
        $(document).on('change', '.role-select', function () {
            let role = $(this).val();
            let modal = $(this).closest('.modal');
            let container = modal.find('.chapter-field-container');
            let chapterSelect = container.find('select');
            let currentChapter = chapterSelect.data('current');
            let takenList = @json($takenChapters);

            if (role === 'chapter' || role === 'reseller') {
                container.slideDown();
                chapterSelect.attr('required', true);

                chapterSelect.find('option').each(function () {
                    let val = $(this).val();
                    if (!val) return;

                    if (role === 'chapter') {
                        // If role is chapter, show taken warning
                        if (takenList.includes(val) && val !== currentChapter) {
                            $(this).prop('disabled', true).text(val + ' (Sudah Ada Penanggung Jawab)').css('background-color', '#f8d7da').show();
                        } else {
                            $(this).prop('disabled', false).text(val).css('background-color', '').show();
                        }
                    } else {
                        // If role is reseller, clean view
                        $(this).prop('disabled', false).text(val).css('background-color', '').show();
                    }
                });
            } else {
                container.slideUp();
                chapterSelect.attr('required', false).val('');
            }
        });

        // Initialize display for edit modals (already handled by PHP style, but for reactivity)
        $('.role-select').trigger('change');

        // Toggle Expand/Collapse Icon
        $('.clickable-row').on('click', function() {
            $(this).find('.toggle-icon').toggleClass('fa-plus fa-minus');
            $(this).toggleClass('bg-white bg-light');
        });

        // Transfer Database Logic
        $('.btn-transfer-db').on('click', function(e) {
            e.stopPropagation(); 
            const id = $(this).data('id');
            const name = $(this).data('name');
            
            $('#transfer_from_id').val(id);
            $('#transfer_from_name').text(name);
            
            $('#transfer_to_id option').prop('disabled', false);
            $('#transfer_to_id option[value="' + id + '"]').prop('disabled', true);
            
            $('#transferDatabaseModal').modal('show');
        });

        $('#transferDatabaseForm').on('submit', function(e) {
            e.preventDefault();
            
            const toId = $('#transfer_to_id').val();
            const toName = $('#transfer_to_id option:selected').text();
            
            if (!toId) {
                Swal.fire('Error', 'Silakan pilih user tujuan.', 'error');
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Transfer',
                text: "Anda yakin ingin memindahkan SEMUA database ke " + toName + "?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4e73df',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Transfer Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang memindahkan database, mohon tunggu.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('admin.settings.users.transfer') }}",
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            Swal.fire('Berhasil!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                            Swal.fire('Gagal!', msg, 'error');
                        }
                    });
                }
            });
        });
    </script>
@endsection