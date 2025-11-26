<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Redirect if already logged in
if (isset($_SESSION['customer_id'])) {
    // Redirect based on role
    if (isset($_SESSION['user_role'])) {
        if ($_SESSION['user_role'] == 1) {
            header("Location: ../admin/category.php");
        } elseif ($_SESSION['user_role'] == 3) {
            header("Location: ../artisan/dashboard.php");
        } else {
            header("Location: ../index.php");
        }
    } else {
        header("Location: ../index.php");
    }
    exit();
}

// Handle error messages
$error_message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'missing_fields':
            $error_message = 'Please fill in all fields';
            break;
        case 'invalid_email':
            $error_message = 'Please enter a valid email address';
            break;
        case 'system_error':
            $error_message = 'System error occurred. Please try again.';
            break;
        case 'unauthorized':
            $error_message = 'You do not have permission to access that page';
            break;
        default:
            $error_message = htmlspecialchars($_GET['error']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GhanaTunes</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2E86AB;
            --primary-hover: #1B5E7A;
            --accent-color: #F18F01;
            --secondary-color: #6c757d;
        }
        
        body {
            background: linear-gradient(135deg, #2E86AB 0%, #1B5E7A 100%);
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            margin-top: 4rem;
            margin-bottom: 2rem;
        }
        
        .card {
            border: none;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.2);
            background-color: rgba(255, 255, 255, 0.98);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            border-bottom: none;
            padding: 2rem;
            text-align: center;
        }
        
        .card-body {
            padding: 2.5rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 0.875rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(46, 134, 171, 0.25);
        }
        
        .btn-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            border: none;
            color: white;
            padding: 0.875rem 2rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            background: linear-gradient(135deg, var(--primary-hover), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(46, 134, 171, 0.3);
            color: white;
        }
        
        .card-footer {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-top: 1px solid #e9ecef;
            padding: 1.5rem 2.5rem;
            text-align: center;
        }
        
        .highlight {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .highlight:hover {
            text-decoration: underline;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: var(--accent-color);
            transform: translateX(-5px);
        }

        .register-options {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .register-options a {
            flex: 1;
            min-width: 200px;
        }

        .btn-artisan {
            background: linear-gradient(135deg, var(--accent-color), #C77700);
            color: white;
        }

        .btn-artisan:hover {
            background: linear-gradient(135deg, #C77700, var(--accent-color));
            color: white;
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <a href="../index.php" class="back-link">
                    <i class="fas fa-arrow-left me-2"></i>Back to Home
                </a>

                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-sign-in-alt me-2"></i>Welcome Back</h4>
                        <p class="mb-0 mt-2" style="opacity: 0.9;">Sign in to your GhanaTunes account</p>
                    </div>
                    
                    <div class="card-body">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form id="login-form" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email Address
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="customer_email" 
                                       placeholder="Enter your email address"
                                       required
                                       autocomplete="email">
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="customer_pass" 
                                       placeholder="Enter your password"
                                       required
                                       autocomplete="current-password">
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="rememberMe" 
                                           name="remember_me">
                                    <label class="form-check-label" for="rememberMe">
                                        Remember me
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-custom w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
                            </button>
                        </form>
                    </div>
                    
                    <div class="card-footer">
                        <p class="mb-3"><strong>Don't have an account?</strong></p>
                        <div class="register-options">
                            <a href="register.php" class="btn btn-outline-primary">
                                <i class="fas fa-user-plus me-1"></i>Customer
                            </a>
                            <a href="register_artisan.php" class="btn btn-artisan">
                                <i class="fas fa-hammer me-1"></i>Artisan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/login.js"></script>
</body>
</html>