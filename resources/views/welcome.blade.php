<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Helas Corporation - Sistem Manajemen</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #1a001a, #a30035, #ff005e);
            color: white;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 60px;
            overflow-x: hidden;
        }

        .container {
            max-width: 1000px;
            width: 90%;
            margin: auto;
            padding: 40px 25px;
            text-align: center;
            animation: fadeIn 1.2s ease-out;
        }

        /* LOGO + TITLE AREA */
        .header-section {
            margin-bottom: 40px;
        }

        .logo img {
            width: 140px;
            border-radius: 50%;
            margin-bottom: 15px;
            box-shadow: 0 0 25px rgba(255, 255, 255, 0.3);
            transition: transform 0.4s ease;
        }

        .logo img:hover {
            transform: rotate(8deg) scale(1.1);
        }

        h1 {
            font-size: 2.6rem;
            margin-bottom: 12px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        p.description {
            max-width: 700px;
            margin: 0 auto 30px;
            font-size: 1.1rem;
            line-height: 1.7;
            opacity: 0.9;
        }

        /* Tombol utama di bawah judul */
        .main-login-button {
            display: inline-block;
            margin-top: 20px;
            background: linear-gradient(90deg, #fde047, #facc15);
            color: #000;
            padding: 12px 35px;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(250, 204, 21, 0.4);
        }

        .main-login-button:hover {
            background: linear-gradient(90deg, #facc15, #eab308);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(250, 204, 21, 0.5);
        }

        /* FITUR KARTU */
        .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin-top: 50px;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 18px;
            width: 260px;
            padding: 25px 20px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.25);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            background: rgba(255, 255, 255, 0.2);
        }

        .feature-card img {
            width: 85px;
            height: 85px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 18px;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.3);
        }

        .feature-card h3 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .card-button {
            display: inline-block;
            margin-top: 12px;
            background: linear-gradient(90deg, #fde047, #facc15);
            color: #000;
            padding: 9px 22px;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(250, 204, 21, 0.4);
        }

        .card-button:hover {
            background: linear-gradient(90deg, #facc15, #eab308);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(250, 204, 21, 0.5);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* RESPONSIVE */
        @media (max-width: 600px) {
            h1 {
                font-size: 2.2rem;
            }

            .feature-card {
                width: 90%;
            }
        }
        
        /* Kotak Login Owner */
.owner-box {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 18px;
    padding: 25px;
    margin: 40px auto 0;
    width: 320px;
    text-align: center;
    color: #fff;
    box-shadow: 0 0 25px rgba(0, 0, 0, 0.3);
    animation: fadeIn 1.5s ease;
}

.owner-box h3 {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 8px;
}

.owner-box p {
    font-size: 0.95rem;
    opacity: 0.85;
    margin-bottom: 18px;
}

.owner-button {
    display: inline-block;
    background: linear-gradient(90deg, #38bdf8, #0ea5e9);
    color: #fff;
    padding: 10px 25px;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 700;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4);
}

.owner-button:hover {
    background: linear-gradient(90deg, #0ea5e9, #0284c7);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(14, 165, 233, 0.6);
}

        
    </style>
</head>

<body>
    <div class="container">
        <div class="header-section">
            <div class="logo">
                <img src="{{ asset('backend/Helas.jpg') }}" alt="Logo Helas Corporation">
            </div>
            <h1>HELAS CORPORATION</h1>
      

    
            
            <!-- Kotak khusus untuk Owner -->
<div class="owner-box">
    <h3>Login Khusus Owner</h3>
    <p>Akses penuh untuk memantau seluruh cabang dan divisi</p>
    <a href="{{ route('home') }}" class="owner-button">Masuk</a>
</div>
            
            
        </div>
<style>
.features {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* 🔹 Fix 3 kolom */
    gap: 30px;
    justify-items: center;
    align-items: center;
    max-width: 900px;
    margin: 60px auto;
    padding: 0 20px;
}

.feature-card {
    background: linear-gradient(145deg, #b83564, #d94f8a);
    border-radius: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.25);
    text-align: center;
    padding: 25px;
    width: 220px;
    transition: all 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.3);
}

.feature-card img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    margin-bottom: 15px;
}

.feature-card h3 {
    font-size: 1rem;
    font-weight: bold;
    color: #fff;
    margin-bottom: 12px;
}

.card-button {
    display: inline-block;
    background: #ffea00;
    color: #000;
    padding: 8px 22px;
    border-radius: 10px;
    font-weight: bold;
    text-decoration: none;
    transition: 0.3s;
}

.card-button:hover {
    background: #ffd500;
}

/* Responsif: 2 kolom di tablet, 1 kolom di HP */
@media (max-width: 992px) {
    .features {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .features {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="features">
    <div class="feature-card">
        <img src="{{ asset('backend/mbc1.png') }}" alt="MBC Hamasah">
        <h3>MBC Hamasah</h3>
        <a href="{{ route('home') }}" class="card-button">Masuk</a>
    </div>

    <div class="feature-card">
        <img src="{{ asset('backend/logosmi1.jpg') }}" alt="SMI">
        <h3>SMI</h3>
        <a href="{{ route('login.smi') }}" class="card-button">Masuk</a>
    </div>

    <div class="feature-card">
        <img src="{{ asset('backend/marketing.png') }}" alt="Marketing">
        <h3>Marketing</h3>
        <a href="{{ route('login.marketing') }}" class="card-button">Masuk</a>
    </div>

    <div class="feature-card">
        <img src="{{ asset('backend/Finance.png') }}" alt="Keuangan">
        <h3>Keuangan</h3>
        <a href="{{ route('home') }}" class="card-button">In Development</a>
    </div>

    <div class="feature-card">
        <img src="{{ asset('backend/HR.png') }}" alt="HR">
        <h3>HR</h3>
        <a href="{{ route('home') }}" class="card-button">In Development</a>
    </div>

    <div class="feature-card">
        <img src="{{ asset('backend/produksi.png') }}" alt="Produksi">
        <h3>Produksi</h3>
        <a href="{{ route('home') }}" class="card-button">In Development</a>
    </div>
</div>



    </div>
</body>

</html>
