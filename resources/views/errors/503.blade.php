<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Maintenance | IT Helas Corporation</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background: radial-gradient(circle at center, #1e1e2f, #0a0a0a);
      color: white;
      font-family: 'Poppins', sans-serif;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      height: 100vh;
      overflow: hidden;
      text-align: center;
    }

    /* LOGO */
    .logo {
      width: 140px;
      height: 140px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid rgba(255, 255, 255, 0.6);
      box-shadow: 0 0 20px rgba(255, 255, 255, 0.8), 0 0 40px rgba(0, 224, 255, 0.6);
      margin-bottom: 25px;
      animation: logoGlow 3s ease-in-out infinite alternate;
    }

    @keyframes logoGlow {
      from { box-shadow: 0 0 15px rgba(255,255,255,0.7), 0 0 30px rgba(0,224,255,0.4); }
      to { box-shadow: 0 0 25px rgba(255,255,255,1), 0 0 50px rgba(0,224,255,0.8); }
    }

    /* MESIN BERPUTAR */
    .machine {
      width: 150px;
      height: 150px;
      border: 6px solid #00e0ff;
      border-top: 6px solid transparent;
      border-radius: 50%;
      position: relative;
      animation: spin 2.5s linear infinite;
      box-shadow: 0 0 25px #00e0ff, inset 0 0 10px #00e0ff;
      margin-top: 10px;
    }

    .machine::before {
      content: '';
      position: absolute;
      top: 20%;
      left: 20%;
      width: 60%;
      height: 60%;
      border: 4px solid #ff0080;
      border-radius: 50%;
      animation: spinReverse 3s linear infinite;
      box-shadow: 0 0 15px #ff0080, inset 0 0 10px #ff0080;
    }

    .machine::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 14px;
      height: 14px;
      background: #fff;
      border-radius: 50%;
      transform: translate(-50%, -50%);
      box-shadow: 0 0 10px #fff;
    }

    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }

    @keyframes spinReverse {
      from { transform: rotate(360deg); }
      to { transform: rotate(0deg); }
    }

    h1 {
      font-size: 2.5em;
      margin-top: 40px;
      letter-spacing: 2px;
      text-shadow: 0 0 10px rgba(255,255,255,0.8);
    }

    p {
      font-size: 1.2em;
      margin-top: 10px;
      opacity: 0.9;
    }

    /* FOOTER */
    .footer {
      position: absolute;
      bottom: 25px;
      font-size: 1.2em;
      text-align: center;
      color: #fff;
      text-shadow: 0 0 10px rgba(255,255,255,0.9);
      line-height: 1.6;
    }

    .heart {
      color: #ff4d6d;
      font-size: 1.3em;
      animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.3); opacity: 0.8; }
    }
  </style>
</head>
<body>

  <!-- LOGO BULAT -->
  <img src="{{ asset('backend/Helas.jpg') }}" alt="Logo Helas Corporation" class="logo">

  <!-- MESIN BERPUTAR -->
  <!--<div class="machine"></div>-->

  <h1>Website Sedang Maintenance</h1>
  <p>Silakan Menghubungi Tim IT Untuk Info Lebih Lanjut.</p>

  <div class="footer">
    Made with <span class="heart">❤️</span> by IT Development Helas Corporation<br>
    © 2025 Helas Corporation
  </div>

</body>
</html>
