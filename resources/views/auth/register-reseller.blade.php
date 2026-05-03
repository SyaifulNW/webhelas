@extends('layouts.app')

@section('content')
    <style>
        body {
            background: linear-gradient(135deg, #370331, #e10338);
            min-height: 100vh;
            font-family: 'Nunito', sans-serif;
        }

        .register-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            min-height: 100vh;
        }

        .register-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0px 10px 35px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            animation: fadeInUp 0.8s ease;
        }

        .register-header {
            background: linear-gradient(135deg, #370331, #e10338);
            color: white;
            text-align: center;
            padding: 30px 20px;
        }

        .register-header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .register-body {
            padding: 30px;
        }

        .form-control {
            border-radius: 50px;
            padding: 10px 20px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #e10338;
            box-shadow: 0 0 0 0.2rem rgba(225, 3, 56, 0.1);
        }

        /* Custom style for select to look more 'rapi' and 'jelas' */
        select.form-control {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23e10338' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 20px center;
            background-size: 15px;
            cursor: pointer;
        }

        .btn-register {
            background: linear-gradient(135deg, #370331, #e10338);
            border: none;
            border-radius: 50px;
            padding: 12px 25px;
            font-weight: bold;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            background: linear-gradient(135deg, #5a054d, #b1022c);
            transform: translateY(-2px);
        }

        .register-footer {
            text-align: center;
            margin-top: 20px;
        }

        .register-footer a {
            color: #e10338;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <div class="register-wrapper">
        <div class="register-card">
            <div class="register-header">
                <div style="margin-bottom: 15px;">
                    <img src="{{ asset('backend/Helas.jpg') }}" alt="Helas Logo"
                        style="width: 60px; height: 60px; border-radius: 50%; border: 2px solid white;">
                </div>
                <h2>Daftar Sebagai Reseller</h2>
                <p class="mb-0 small">Gabung dengan komunitas reseller Helas</p>
            </div>

            <div class="register-body">
                <form method="POST" action="{{ route('reseller.register') }}">
                    @csrf

                    {{-- Username --}}
                    <div class="mb-3">
                        <label class="small font-weight-bold">Username</label>
                        <input id="username" type="text" class="form-control @error('username') is-invalid @enderror"
                            name="username" value="{{ old('username') }}" placeholder="Username" required autofocus>
                        @error('username')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="small font-weight-bold">Nama Lengkap</label>
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                            value="{{ old('name') }}" placeholder="Nama Lengkap" required>
                        @error('name')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- No ID --}}
                    <div class="mb-3">
                        <label class="small font-weight-bold">No ID</label>
                        <input id="id_no" type="text" class="form-control @error('id_no') is-invalid @enderror" name="id_no"
                            value="{{ old('id_no') }}" placeholder="Nomor ID Reseller" required>
                        @error('id_no')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- No WA --}}
                    <div class="mb-3">
                        <label class="small font-weight-bold">No WA</label>
                        <input id="wa" type="text" class="form-control @error('wa') is-invalid @enderror" name="wa"
                            value="{{ old('wa') }}" placeholder="08xxxxxxxxx" required>
                        @error('wa')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-3">
                        <label class="small font-weight-bold">Email</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" placeholder="email@contoh.com" required>
                        @error('email')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Chapter --}}
                    <div class="mb-3">
                        <label class="small font-weight-bold">Chapter</label>
                        <select id="chapter" class="form-control @error('chapter') is-invalid @enderror" name="chapter"
                            required>
                            <option value="">-- Pilih Chapter --</option>
                            <option value="Cirebon" {{ old('chapter') == 'Cirebon' ? 'selected' : '' }}>Cirebon</option>
                            <option value="Kalimantan Timur" {{ old('chapter') == 'Kalimantan Timur' ? 'selected' : '' }}>
                                Kalimantan Timur</option>
                            <option value="Depok" {{ old('chapter') == 'Depok' ? 'selected' : '' }}>Depok</option>
                            <option value="Jakarta" {{ old('chapter') == 'Jakarta' ? 'selected' : '' }}>Jakarta</option>
                            <option value="Makasar" {{ old('chapter') == 'Makasar' ? 'selected' : '' }}>Makasar</option>
                            <option value="Tanggerang" {{ old('chapter') == 'Tanggerang' ? 'selected' : '' }}>Tanggerang
                            </option>
                            <option value="Lampung" {{ old('chapter') == 'Lampung' ? 'selected' : '' }}>Lampung</option>
                            <option value="Kediri" {{ old('chapter') == 'Kediri' ? 'selected' : '' }}>Kediri</option>
                        </select>
                        @error('chapter')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label class="small font-weight-bold">Password</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                            name="password" placeholder="Password" required>
                        @error('password')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div class="mb-4">
                        <label class="small font-weight-bold">Ulangi Password</label>
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation"
                            placeholder="Ulangi Password" required>
                    </div>

                    <button type="submit" class="btn btn-register">
                        Daftar Sekarang
                    </button>
                </form>

                <div class="register-footer small">
                    Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
                </div>
            </div>
        </div>
    </div>
@endsection