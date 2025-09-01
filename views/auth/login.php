<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$auth = new Auth();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both Staff/Student ID and password';
    } elseif ($auth->login($username, $password)) {
        header('Location: ' . BASE_URL . '/dashboard');
        exit();
    } else {
        $error = 'Invalid Staff/Student ID or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ladoke Akintola University of Technology</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL) ?>/style.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            min-height: 100vh;
        }
        
        .image-section {
            flex: 1;
            background-image: url('<?= htmlspecialchars(BASE_URL) ?>./banner.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
            width: 100%;
            height: 100vh;
        }
        
        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px;
            color: white;
        }
        
        .login-section {
            width: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px;
            background: white;
        }
        
        .login-container {
            max-width: 350px;
            margin: 0 auto;
            width: 100%;
        }
        
        .login-container img {
            display: block;
            max-width: 100px;
            margin: 0 auto 20px;
        }
        
        .university-name {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 5px;
            color: #333;
            text-align: center;
        }
        
        .university-fullname {
            font-size: 14px;
            margin-bottom: 30px;
            color: #555;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #999;
        }
        
        .btn-login {
            width: 100%;
            padding: 10px;
            background-color: #0066cc;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        
        .btn-login:hover {
            background-color: #0055aa;
        }
        
        .forgot-password {
            margin-top: 15px;
            font-size: 13px;
            text-align: center;
        }
        
        .forgot-password a {
            color: #0066cc;
            text-decoration: none;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .error-message {
            color: #e74c3c;
            margin-bottom: 15px;
            text-align: center;
            background: #fdf2f2;
            padding: 10px;
            border-radius: 3px;
            border: 1px solid #fecaca;
            font-size: 13px;
        }
        
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .image-section {
                height: 200px;
                flex: none;
            }
            
            .login-section {
                width: 100%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="image-section">
        <div class="image-overlay">
            <!-- You can add content here if needed -->
        </div>
    </div>
    
    <div class="login-section">
        <div class="login-container">
            <img src="<?= htmlspecialchars(BASE_URL) ?>./logo.png" alt="University Logo">
            <div class="university-name">LADOKE AKINTOLA UNIVERSITY OF TECHNOLOGY</div>
            <div class="university-fullname">Ogbomoso, Oyo State, Nigeria</div>
            
            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" autocomplete="on">
                <div class="form-group">
                    <label for="username">Staff/Student ID</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
               
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
               
                <button type="submit" class="btn-login">LOGIN</button>
            </form>
            <div class="forgot-password">
                <a href="<?= htmlspecialchars(BASE_URL) ?>/forgot-password">Forgot Password?</a>
            </div>
        </div>
    </div>
</body>
</html>