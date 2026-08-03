<?php
// Just in case they navigate here manually but registration is actually open, 
// we check status. If it's open, redirect them to the index page.
require_once 'config.php';
if (isRegistrationOpen($pdo)) {
    header('Location: index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Ditutup - PT. Visdat Teknik Utama</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        .closed-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem 2rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 90%;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.5);
            transform: translateY(0);
            transition: all 0.3s ease;
        }
        .closed-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        .icon-container {
            background-color: #ffebee;
            color: #d32f2f;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem auto;
            font-size: 2.5rem;
            box-shadow: 0 10px 20px rgba(211, 47, 47, 0.2);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 10px 20px rgba(211, 47, 47, 0.2);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 10px 25px rgba(211, 47, 47, 0.4);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 10px 20px rgba(211, 47, 47, 0.2);
            }
        }
        .title {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1rem;
            letter-spacing: -0.5px;
        }
        .message {
            font-size: 1.15rem;
            color: #5a6c7d;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .logo-container {
            margin-top: 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            padding-top: 1.5rem;
        }
        .logo-text {
            font-weight: 800;
            color: #d32f2f;
            letter-spacing: 1px;
            font-size: 1.2rem;
            margin-bottom: 0.2rem;
        }
        .logo-sub {
            font-size: 0.8rem;
            color: #95a5a6;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
    </style>
</head>
<body>
    <div class="closed-card">
        <div class="icon-container">
            <i class="fas fa-calendar-times"></i>
        </div>
        <h2 class="title">Pendaftaran Ditutup</h2>
        <p class="message">Silahkan menunggu informasi pendaftaran berikutnya</p>
        <div class="logo-container">
            <div class="logo-text">PT. VISDAT TEKNIK UTAMA</div>
            <div class="logo-sub">Sistem Rekrutmen Online</div>
        </div>
    </div>
</body>
</html>
