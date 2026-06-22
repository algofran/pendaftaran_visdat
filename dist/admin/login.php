<?php
session_start();
require_once '../config.php';

$error = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === 'admin' && $password === 'visdatkom12@2025') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Username atau password salah';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - PT. Visdat Teknik Utama</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            animation: fadeInUp 0.5s ease-out;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #dc2626 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        .login-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 50%, rgba(255,255,255,0.1) 100%);
        }
        .login-body {
            padding: 2rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #1e3a8a 0%, #dc2626 100%);
            border: none;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 58, 138, 0.4);
            background: linear-gradient(135deg, #dc2626 0%, #1e3a8a 100%);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-card">
                    <div class="login-header">
                        <i class="fas fa-user-shield fa-3x mb-3"></i>
                        <h3>Admin Login</h3>
                        <p class="mb-0">PT. Visdat Teknik Utama</p>
                    </div>
                    <div class="login-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" name="username" class="form-control" required autofocus>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Login
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        <div class="text-center">
                            <small class="text-muted">
                                Default: admin / admin123
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>