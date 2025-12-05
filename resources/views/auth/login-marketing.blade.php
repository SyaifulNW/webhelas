<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Marketing | Dashboard MBC</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #007bff, #00c6ff);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            background: #fff;
            width: 400px;
            animation: fadeInUp 0.8s ease;
        }

        .card-header {
            background: transparent;
            border-bottom: none;
            text-align: center;
            padding-bottom: 0;
        }

        .logo-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
        }

        .logo {
            width: 90px;
            height: 90px;
            object-fit: contain;
            border-radius: 50%;
            background: #f8f9fa;
            padding: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .title {
            font-weight: 700;
            color: #007bff;
        }

        .btn-login {
            background: #007bff;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            color: white;
            transition: 0.3s;
        }

        .btn-login:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }

        .form-control {
            border-radius: 10px;
        }

        .footer {
            text-align: center;
            font-size: 0.85rem;
            color: #777;
            margin-top: 10px;
        }

        /* Animasi lembut saat halaman muncul */
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
</head>
<body>

    <div class="card p-4">
        <div class="card-header">
            <div class="logo-wrapper">
                <img src="{{ asset('backend/marketing.png') }}" alt="Marketing Logo" class="logo">
            </div>
            <h4 class="title mb-1">Login Marketing</h4>
            <p class="text-muted mb-0">Masuk ke Dashboard Program Kerja</p>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-envelope"></i></span>
                        <input id="email" type="email" class="form-control" name="email" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-lock"></i></span>
                        <input id="password" type="password" class="form-control" name="password" required>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-login">
                        <i class="fa-solid fa-right-to-bracket me-2"></i> Masuk
                    </button>
                </div>
            </form>
        </div>

        <div class="footer">
            © {{ date('Y') }} MBC Marketing Dashboard
        </div>
    </div>

</body>
</html>
