<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Helas Corporation - Sistem Manajemen</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
        <link rel="icon" href="{{ asset('backend/Helas.jpg') }}" type="image/jpeg">
    <style>
        :root {
            --primary-red: #a30035;
            --primary-pink: #ff005e;
            --accent-gold: #facc15;
            --glass-bg: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.25);
            --card-hover-bg: rgba(255, 255, 255, 0.25);
        }

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
            padding-top: 10px;
            overflow-x: hidden;
        }

        /* Ambient Glow Background */
        body::before {
            content: '';
            position: absolute;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            width: 80%;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            z-index: -1;
            pointer-events: none;
        }

        .container {
            max-width: 1800px;
            width: 98%;
            margin: auto;
            padding: 10px;
            text-align: center;
            animation: fadeIn 1.2s ease-out;
        }

        /* HEADER */
        .header-section {
            margin-bottom: 20px;
        }

        .logo img {
            width: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
            box-shadow: 0 0 25px rgba(255, 255, 255, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.4s ease;
        }

        .logo img:hover {
            transform: rotate(5deg) scale(1.05);
            box-shadow: 0 0 40px rgba(255, 255, 255, 0.5);
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 5px;
            font-weight: 800;
            letter-spacing: 1px;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
            color: #fff;
        }

        /* ORGANISASI CHART */
        .org-structure-container {
            margin: 10px auto 30px;
            width: fit-content;
            max-width: 95%;
            padding: 20px;
            display: flex;
            justify-content: center;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 40px; /* Rounder corners */
            transition: all 0.4s ease;
        }

        .org-structure-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            background: rgba(255, 255, 255, 0.25);
        }

        .org-structure-container img {
            width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: contain;
            border-radius: 20px;
            display: block;
        }

        /* LAYOUT UTAMA: LOGIN ROW */
        .login-row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap; 
        }
        
        .card-group {
            display: flex;
            gap: 20px;
        }

        /* SHARED CARD STYLES */
        .common-card {
            width: 250px;
            height: 320px;
            padding: 25px 15px;
            border-radius: 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            position: relative;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(15px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .common-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            z-index: 5;
        }

        /* OWNER BOX - SPECIFIC STYLE */
        .owner-box {
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .owner-box:hover {
             background: rgba(255, 255, 255, 0.25);
        }

        .owner-box h3 {
            font-size: 1.3rem;
            font-weight: 800;
            color: #fff;
            margin-top: 20px;
        }

        .owner-box p {
            font-size: 0.9rem;
            opacity: 0.9;
            color: #eee;
            margin-bottom: auto;
        }

        .owner-button {
            display: inline-block;
            background: linear-gradient(90deg, #38bdf8, #0ea5e9);
            color: #fff;
            padding: 10px 30px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(14, 165, 233, 0.5);
            margin-bottom: 20px;
        }
        
        .owner-button:hover {
             box-shadow: 0 0 30px rgba(14, 165, 233, 0.7);
             background: linear-gradient(90deg, #0ea5e9, #0284c7);
        }

        /* FEATURE CARD - SPECIFIC STYLE */
        .feature-card {
            background: linear-gradient(145deg, rgba(184, 53, 100, 0.6), rgba(217, 79, 138, 0.6));
        }

        .feature-card:hover {
            background: linear-gradient(145deg, rgba(184, 53, 100, 0.8), rgba(217, 79, 138, 0.8));
            border-color: rgba(255, 255, 255, 0.4);
        }

        /* Wrapper khusus logo */
        .logo-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100px;
            width: 100%;
            margin-top: 10px;
        }

        .feature-card img {
            width: 90px;
            height: 90px;
            object-fit: contain;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        
        .feature-card > img {
             margin-top: 20px;
        }

        .feature-card:hover img {
            transform: scale(1.1);
        }

        .feature-card h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            margin: auto 0;
        }

        .card-button {
            display: inline-block;
            background: linear-gradient(90deg, #fde047, #facc15);
            color: #000;
            padding: 10px 28px;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(250, 204, 21, 0.4);
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .card-button:hover {
            background: linear-gradient(90deg, #facc15, #eab308);
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(250, 204, 21, 0.5);
        }

        /* Inactive Cards Styling */
        .feature-card.inactive {
             background: rgba(255, 255, 255, 0.1);
             border-color: rgba(255,255,255,0.1);
        }
        
        .feature-card.inactive .card-button {
            background: transparent;
            color: #ddd;
            border: 1px solid #ddd;
            box-shadow: none;
            cursor: default;
        }
        
        .feature-card.inactive:hover .card-button {
             transform: none;
        }

        .feature-card.inactive img {
             filter: grayscale(100%);
             opacity: 0.7;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* RESPONSIVE */
        @media (max-width: 1400px) {
            .common-card {
                width: 220px;
                height: 300px;
             }
        }

        @media (max-width: 1200px) {
            .login-row {
                flex-direction: column;
            }
            .card-group {
                justify-content: center;
                flex-wrap: wrap;
            }
            .owner-box {
                order: -1; 
                margin-bottom: 20px;
                width: 100%;
                max-width: 400px;
                height: auto;
                padding: 30px;
            }
            
            .feature-card {
                 width: 45%;
                 max-width: 200px;
            }
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
        </div>



        <!-- Layout Sejajar Pusat: Cards - Owner - Cards -->
        <!-- Single Login Section -->
        <div class="login-row">
            <div class="feature-card common-card">
                <div class="logo-wrapper">
                    <img src="{{ asset('backend/Helas.jpg') }}" alt="Logo Helas Corporation">
                </div>
                <h3>Login Ke Sistem</h3>
                <a href="{{ route('login') }}" class="card-button">Masuk</a>
            </div>
        </div>

    </div>
</body>

</html>
```
