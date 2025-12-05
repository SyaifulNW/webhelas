<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Start-Up Muda Indonesia</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  <style>
    body {
      background: radial-gradient(circle at top right, #005c97, #363795);
      min-height: 100vh;
      font-family: 'Poppins', sans-serif;
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-card {
      background: #fff;
      border-radius: 25px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
      overflow: hidden;
      width: 100%;
      max-width: 420px;
      animation: fadeInUp 0.8s ease;
    }

    .login-header {
      background: linear-gradient(135deg, #005c97, #363795);
      color: white;
      text-align: center;
      padding: 40px 20px 30px;
    }

    .login-header img {
      width: 110px;
      height: 110px;
      object-fit: cover;
      border-radius: 50%;
      border: 4px solid #fff;
      box-shadow: 0 0 15px rgba(255, 255, 255, 0.5);
      margin-bottom: 10px;
      animation: pulse 2s infinite;
    }

    .login-header h2 {
      margin: 0;
      font-weight: 700;
      font-size: 1.4rem;
    }

    .login-header p {
      margin-top: 6px;
      font-size: 0.95rem;
      opacity: 0.9;
    }

    .login-body {
      padding: 35px 40px 40px;
    }

    .form-control {
      border-radius: 50px;
      padding: 13px 45px 13px 20px;
      width: 100%;
      font-size: 1rem;
      border: 1px solid #ccc;
      margin-bottom: 18px;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
      box-sizing: border-box;
    }

    .form-control:focus {
      border-color: #363795;
      outline: none;
      box-shadow: 0 0 8px rgba(54, 55, 149, 0.3);
    }

    .position-relative {
      position: relative;
    }

    .toggle-password {
      position: absolute;
      top: 50%;
      right: 18px;
      transform: translateY(-50%);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 20px;
      width: 20px;
      cursor: pointer;
      color: #888;
      font-size: 1.05rem;
      transition: color 0.3s;
    }

    .toggle-password:hover {
      color: #363795;
    }

    .btn-login {
      background: linear-gradient(135deg, #005c97, #363795);
      border: none;
      border-radius: 50px;
      padding: 13px 25px;
      font-weight: bold;
      color: white;
      width: 100%;
      transition: all 0.3s ease;
      cursor: pointer;
      font-size: 1.05rem;
      margin-top: 5px;
    }

    .btn-login:hover {
      background: linear-gradient(135deg, #0073c6, #5e5ebf);
      transform: translateY(-2px);
    }

    .login-footer {
      text-align: center;
      margin-top: 20px;
    }

    .login-footer a {
      color: #005c97;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.3s ease;
    }

    .login-footer a:hover {
      color: #363795;
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes pulse {
      0% { transform: scale(1); box-shadow: 0 0 15px rgba(255, 255, 255, 0.4); }
      50% { transform: scale(1.05); box-shadow: 0 0 25px rgba(255, 255, 255, 0.7); }
      100% { transform: scale(1); box-shadow: 0 0 15px rgba(255, 255, 255, 0.4); }
    }
  </style>
</head>

<body>
  <div class="login-card">
    <div class="login-header">
      <img src="{{ asset('backend/logosmi1.jpg') }}" alt="Logo SMI">
      <h2>Start-Up Muda Indonesia</h2>
      <p>Silakan login terlebih dahulu</p>
    </div>

    <div class="login-body">
      {{-- ✅ Gunakan form bawaan Laravel --}}
      <form method="POST" action="{{ route('login') }}">
        @csrf
        <input type="hidden" name="smi_login" value="1"> <!-- penanda login SMI -->

        <input type="email" name="email" class="form-control" placeholder="Email Address" required>

        <div class="position-relative">
          <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
          <span class="toggle-password" id="togglePassword">
            <i class="fa fa-eye"></i>
          </span>
        </div>

        <button type="submit" class="btn-login">Login</button>
      </form>

      <div class="login-footer">
        <a href="#">Lupa Password?</a>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    // Toggle password visibility
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    togglePassword.addEventListener('click', () => {
      const icon = togglePassword.querySelector('i');
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        passwordInput.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
      }
    });

    // Tampilkan SweetAlert jika login gagal
    @if (session('loginError'))
      Swal.fire({
        icon: 'error',
        title: 'Login Gagal',
        text: '{{ session('loginError') }}',
        confirmButtonColor: '#363795'
      });
    @endif
  </script>
</body>
</html>
