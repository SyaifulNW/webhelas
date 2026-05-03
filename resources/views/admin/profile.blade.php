@extends('layouts.masteradmin')

@section('content')
    <style>
        /* Page Background */
        #content {
            background-color: #f8f9fc;
        }

        /* Premium Profile Header */
        .profile-header {
            background: linear-gradient(135deg, #370331, #e10338);
            height: 180px;
            border-radius: 20px 20px 0 0;
            position: relative;
            overflow: hidden;
        }

        .profile-header::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://www.transparenttextures.com/patterns/cubes.png');
            opacity: 0.1;
        }

        /* Floating Image */
        .profile-img-container {
            position: relative;
            display: inline-block;
            margin-top: -90px;
            z-index: 5;
        }

        .profile-img {
            width: 160px;
            height: 160px;
            border: 7px solid #fff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .profile-img:hover {
            transform: scale(1.05);
        }

        /* Card Styling */
        .card-premium {
            border: none;
            border-radius: 20px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1) !important;
            background: #fff;
            overflow: hidden;
        }

        .card-header-premium {
            background: #fff;
            border-bottom: 2px solid #f1f1f1;
            padding: 20px 25px;
        }

        .card-header-premium h6 {
            font-size: 1.2rem;
            font-weight: 800;
            color: #370331;
            margin: 0;
        }

        /* Form Styling */
        .form-group-premium {
            margin-bottom: 1.5rem;
        }

        .label-premium {
            font-weight: 700;
            color: #444;
            font-size: 0.85rem;
            margin-bottom: 8px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-group-premium {
            display: flex;
            align-items: center;
            background: #fdfdfd;
            border: 2px solid #eaecf4;
            border-radius: 12px;
            padding: 2px 15px;
            transition: all 0.3s ease;
        }

        .input-group-premium:focus-within {
            border-color: #e10338;
            background: #fff;
            box-shadow: 0 0 10px rgba(225, 3, 56, 0.1);
        }

        .input-group-premium i {
            color: #888;
            font-size: 1rem;
            width: 25px;
        }

        .input-group-premium .form-control {
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            padding: 10px 5px;
            height: auto;
            color: #333;
            font-weight: 700;
        }

        .input-group-premium .form-control::placeholder {
            color: #ccc;
        }

        /* Specific for Textarea */
        .textarea-premium {
            border: 2px solid #eaecf4;
            border-radius: 12px;
            padding: 12px 15px;
            background: #fdfdfd;
            width: 100%;
            transition: all 0.3s ease;
            font-weight: 700;
            color: #333;
        }

        .textarea-premium:focus {
            border-color: #e10338;
            background: #fff;
            outline: none;
        }

        /* Buttons */
        .btn-premium-save {
            background: linear-gradient(135deg, #370331, #e10338);
            border: none;
            color: white;
            padding: 14px 40px;
            border-radius: 15px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 5px 20px rgba(225, 3, 56, 0.3);
            transition: 0.3s;
        }

        .btn-premium-save:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(225, 3, 56, 0.5);
            color: #fff;
        }

        /* Info Badge */
        .badge-premium-role {
            background: rgba(225, 3, 56, 0.1);
            color: #e10338;
            border: 1px solid rgba(225, 3, 56, 0.2);
            padding: 6px 15px;
            font-weight: 700;
            border-radius: 10px;
        }

        .section-divider {
            display: flex;
            align-items: center;
            margin: 40px 0 30px;
        }

        .section-divider::after {
            content: "";
            flex: 1;
            height: 2px;
            background: linear-gradient(to right, #eaecf4, transparent);
            margin-left: 15px;
        }

        .section-title-alt {
            font-size: 1rem;
            font-weight: 800;
            color: #370331;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        /* Photo Upload Button Over Image */
        .upload-overlay {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: #e10338;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            border: 4px solid white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            z-index: 10;
        }

        .upload-overlay:hover {
            transform: scale(1.1) rotate(90deg);
            background: #370331;
            color: #fff;
            text-decoration: none;
        }

        .upload-overlay i {
            font-size: 1.2rem;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="row">
            <!-- Sidebar Profile -->
            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="card card-premium text-center pb-5 shadow-lg">
                    <div class="profile-header"></div>
                    <div class="profile-img-container">
                        @if($user->photo)
                            <img src="{{ asset($user->photo) }}" class="rounded-circle profile-img shadow" id="profile-preview">
                        @else
                            <img src="{{ asset('backend/img/undraw_profile.svg') }}" class="rounded-circle profile-img shadow" id="profile-preview">
                        @endif
                        
                        <!-- Upload Button Overlay -->
                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="photo-form">
                            @csrf
                            @method('PUT')
                            <input type="file" name="photo" id="photo-input" class="d-none" accept="image/*">
                            <div class="upload-overlay" onclick="document.getElementById('photo-input').click();" title="Ganti Foto Profil">
                                <i class="fas fa-plus"></i>
                            </div>
                        </form>
                    </div>

                    <div class="mt-3 px-4">
                        <form action="{{ route('profile.update') }}" method="POST" id="sidebar-profile-form">
                            @csrf
                            @method('PUT')
                            
                            <!-- Name (View Only) -->
                            <h3 class="font-weight-bold mb-1" style="color: #370331; font-size: 1.6rem">{{ $user->name }}</h3>

                            <div class="mb-3 mt-2">
                                <span class="badge-premium-role small text-uppercase">{{ $user->role }}</span>
                            </div>

                            <!-- Chapter (View Only) -->
                            @if($user->chapter || $user->role == 'chapter')
                                <div class="mb-4">
                                    <span class="text-danger font-weight-bold">
                                        <i class="fas fa-map-marker-alt mr-1"></i> Chapter {{ $user->chapter ?? 'Belum Diatur' }}
                                    </span>
                                </div>
                            @endif

                            <div class="text-left bg-light p-3 rounded-xl mt-4 border position-relative">
                                <!-- Edit Toggle Button -->
                                <button type="button" class="btn btn-sm btn-primary rounded-circle position-absolute shadow-sm" style="top: -15px; right: 10px; z-index: 100;" id="toggle-edit-sidebar">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>

                                <div class="mb-3">
                                    <label class="info-label text-xs text-muted font-weight-bold uppercase mb-0">Email</label>
                                    <div class="view-mode font-weight-bold text-dark">{{ $user->email }}</div>
                                    <div class="edit-mode d-none">
                                        <input type="email" name="email" class="form-control form-control-sm border-2" value="{{ $user->email }}" style="font-weight: 700;">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="info-label text-xs text-muted font-weight-bold uppercase mb-0">WhatsApp</label>
                                    <div class="view-mode font-weight-bold text-dark">{{ $user->wa ?? '-' }}</div>
                                    <div class="edit-mode d-none">
                                        <input type="text" name="wa" class="form-control form-control-sm border-2" value="{{ $user->wa }}" placeholder="08xxxx" style="font-weight: 700;">
                                    </div>
                                </div>
                                <div>
                                    <label class="info-label text-xs text-muted font-weight-bold uppercase mb-0">Biodata</label>
                                    <div class="view-mode text-secondary small">
                                        {{ $user->bio ?? 'Tulis biodata Anda di sini.' }}
                                    </div>
                                    <div class="edit-mode d-none">
                                        <textarea name="bio" class="form-control form-control-sm border-2" rows="3" style="font-weight: 700;">{{ $user->bio }}</textarea>
                                    </div>
                                </div>

                                <!-- Action Buttons for Sidebar Form -->
                                <div class="edit-mode d-none mt-3 text-center">
                                    <hr>
                                    <button type="submit" class="btn btn-sm btn-success px-4 font-weight-bold rounded-pill">Simpan</button>
                                    <button type="button" class="btn btn-sm btn-link text-muted" id="cancel-edit-sidebar">Batal</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Update Form -->
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card card-premium shadow-lg">
                    <div class="card-header-premium">
                        <h6><i class="fas fa-pen-nib mr-2 text-primary"></i> Perbarui Detail Profil</h6>
                    </div>
                    <div class="card-body px-4 pt-3 pb-5">
                        @if(session('success'))
                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: '{{ session('success') }}',
                                        background: '#fff',
                                        confirmButtonColor: '#e10338'
                                    });
                                });
                            </script>
                        @endif

                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- KEAMANAN -->
                            <div class="section-divider" style="margin-top: 15px;">
                                <span class="section-title-alt text-danger">Keamanan & Password</span>
                            </div>

                            <div class="form-group-premium mb-4">
                                <label class="label-premium">Password Saat Ini <small class="text-danger">(Wajib jika ganti
                                        password)</small></label>
                                <div class="input-group-premium">
                                    <i class="fas fa-user-lock text-dark"></i>
                                    <input type="password" name="current_password" class="form-control">
                                </div>
                                @error('current_password') <span
                                class="text-danger small mt-1 ml-2 font-weight-bold">{{ $message }}</span> @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group-premium">
                                    <label class="label-premium">Password Baru</label>
                                    <div class="input-group-premium">
                                        <i class="fas fa-key text-dark"></i>
                                        <input type="password" name="password" class="form-control">
                                    </div>
                                    @error('password') <span
                                    class="text-danger small mt-1 ml-2 font-weight-bold">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6 form-group-premium">
                                    <label class="label-premium">Ulangi Password Baru</label>
                                    <div class="input-group-premium">
                                        <i class="fas fa-check-double text-dark"></i>
                                        <input type="password" name="password_confirmation" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 text-right">
                                <button type="submit" class="btn-premium-save px-5">
                                    <i class="fas fa-save mr-2"></i> Update Profil
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Auto submit photo form when file is selected
        $('#photo-input').on('change', function() {
            if (this.files && this.files[0]) {
                Swal.fire({
                    title: 'Memperbarui Foto...',
                    text: 'Tunggu sebentar ya...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $('#photo-form').submit();
            }
        });

        // Toggle Edit Sidebar
        $('#toggle-edit-sidebar').on('click', function() {
            const isEditing = $('.edit-mode').hasClass('d-none');
            if (isEditing) {
                $('.view-mode').addClass('d-none');
                $('.edit-mode').removeClass('d-none');
                $(this).html('<i class="fas fa-times"></i>').removeClass('btn-primary').addClass('btn-danger');
            } else {
                exitEditMode();
            }
        });

        $('#cancel-edit-sidebar').on('click', function() {
            exitEditMode();
        });

        function exitEditMode() {
            $('.view-mode').removeClass('d-none');
            $('.edit-mode').addClass('d-none');
            $('#toggle-edit-sidebar').html('<i class="fas fa-pencil-alt"></i>').removeClass('btn-danger').addClass('btn-primary');
        }
    </script>
@endpush