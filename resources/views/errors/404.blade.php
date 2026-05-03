<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan | Helas Corporation</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-maroon: #a30035;
            --primary-pink: #ff005e;
            --accent-gold: #facc15;
            --dark-bg: #1a001a;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: radial-gradient(circle at center, #2a002a 0%, var(--dark-bg) 100%);
            color: white;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated Background Elements */
        .bg-blob {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(163, 0, 53, 0.3) 0%, transparent 70%);
            filter: blur(80px);
            z-index: -1;
            animation: move 20s infinite alternate;
        }

        .blob-1 { top: -10%; left: -10%; }
        .blob-2 { bottom: -10%; right: -10%; background: radial-gradient(circle, rgba(255, 0, 94, 0.2) 0%, transparent 70%); }

        @keyframes move {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(100px, 50px) scale(1.2); }
        }

        .container {
            text-align: center;
            z-index: 1;
            padding: 40px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 40px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            max-width: 600px;
            width: 90%;
            animation: fadeIn 1s ease-out;
        }

        .error-code {
            font-size: 15rem;
            font-weight: 900;
            line-height: 1;
            background: linear-gradient(to bottom, #fff, var(--primary-pink));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.3));
        }

        .error-code::after {
            content: '404';
            position: absolute;
            left: 5px;
            top: 5px;
            z-index: -1;
            background: linear-gradient(to bottom, var(--primary-maroon), transparent);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            opacity: 0.5;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            background: linear-gradient(45deg, #fff, var(--accent-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            font-size: 1.1rem;
            opacity: 0.8;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(45deg, var(--primary-maroon), var(--primary-pink));
            color: white;
            padding: 18px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 25px rgba(163, 0, 53, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-home:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 35px rgba(255, 0, 94, 0.6);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .btn-home i {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .btn-home:hover i {
            transform: translateX(-5px);
        }


        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px) scale(0.9); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Float animation for the code */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        .error-code {
            animation: float 4s ease-in-out infinite;
        }

        @media (max-width: 768px) {
            .error-code { font-size: 8rem; }
            h1 { font-size: 1.8rem; }
            .container { padding: 30px 20px; }
        }
    </style>
</head>

<body>
    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-2"></div>

    <div class="container">
        <div class="error-code">404</div>
        <h1>Ups! Halaman Hilang</h1>
        <p>Sepertinya Anda tersesat di dimensi lain. Halaman yang Anda cari tidak dapat ditemukan atau telah dipindahkan.</p>
        
        <a href="{{ url('/') }}" class="btn-home">
            <i class="fas fa-arrow-left"></i>
            Kembali ke Beranda
        </a>
    </div>
</body>

</html>
