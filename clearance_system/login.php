<?php
session_start(); // Start session FIRST
require_once 'config.php';
require_once 'functions.php'; // Load functions FIRST
require_once 'auth.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    switch ($role) {
        case 'student':
            header('Location: dashboard.php');
            break;
        case 'admin':
            header('Location: admin.php');
            break;
        case 'registry':
            header('Location: registry_dashboard.php');
            break;
        default:
            header('Location: staff.php');
            break;
    }
    exit();
}

// Handle login form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $result = Auth::login($username, $password);
    
    if ($result['success']) {
        $role = $result['role'];
        $success = $result['message'];
        
        // Redirect based on role
        switch ($role) {
            case 'student':
                header('Location: dashboard.php');
                break;
            case 'admin':
                header('Location: admin.php');
                break;
            case 'registry':
                header('Location: registry_dashboard.php');
                break;
            default:
                header('Location: staff.php');
                break;
        }
        exit();
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Clearance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="login-card">
                    <div class="login-header text-center p-4">
                        <h2><i class="fas fa-graduation-cap"></i> Smart Clearance</h2>
                        <p class="mb-0">University Clearance System</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user"></i> Username or Email
                                </label>
                                <input type="text" class="form-control form-control-lg" id="username" 
                                       name="username" required placeholder="Enter username or email">
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Password
                                </label>
                                <input type="password" class="form-control form-control-lg" id="password" 
                                       name="password" required placeholder="Enter password">
                            </div>
                            
                            <div class="d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-login btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </button>
                            </div>
                            
                            <div class="text-center">
                                <small class="text-muted">
                                    Forgot password? Contact the system administrator
                                </small>
                            </div>
                        </form>
                    </div>
                    
                    <div class="card-footer text-center py-3">
                        <small class="text-muted">
                            &copy; <?php echo date('Y'); ?> University Smart Clearance System
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>