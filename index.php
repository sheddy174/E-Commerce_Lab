<?php
session_start();
require_once 'settings/core.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Handle success/error messages
$message = '';
$message_type = '';

if (isset($_GET['login'])) {
    $message = 'Welcome back! You have successfully logged in.';
    $message_type = 'success';
} elseif (isset($_GET['logout'])) {
    $message = 'You have been successfully logged out.';
    $message_type = 'info';
} elseif (isset($_GET['register'])) {
    $message = 'Registration successful! Please login to continue.';
    $message_type = 'success';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GhanaTunes - Musical Instruments E-commerce</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2E86AB;
            --primary-hover: #1B5E7A;
            --accent-color: #F18F01;
            --secondary-color: #6c757d;
            --light-blue: #E3F2FD;
        }

        body {
            background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 50%, #90CAF9 100%);
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .menu-tray {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid rgba(46, 134, 171, 0.2);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            box-shadow: 0 0.5rem 1rem rgba(46, 134, 171, 0.15);
            backdrop-filter: blur(10px);
            z-index: 1000;
        }

        .menu-tray .btn {
            margin-left: 0.5rem;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 0.25rem 0.5rem rgba(46, 134, 171, 0.3);
        }

        .btn-outline-success {
            border-color: #198754;
            color: #198754;
        }

        .btn-outline-success:hover {
            background-color: #198754;
            border-color: #198754;
            color: white;
            transform: translateY(-2px);
        }

        .btn-outline-danger {
            border-color: #dc3545;
            color: #dc3545;
        }

        .btn-outline-danger:hover {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
            transform: translateY(-2px);
        }

        .welcome-section {
            padding-top: 6rem;
            text-align: center;
        }

        .welcome-section h1 {
            color: var(--primary-color);
            font-weight: 800;
            margin-bottom: 1rem;
            font-size: 3rem;
        }

        .welcome-section .lead {
            color: var(--secondary-color);
            font-size: 1.2rem;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .user-info {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid var(--primary-color);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 0.5rem 1rem rgba(46, 134, 171, 0.1);
        }

        .btn-ocean {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.875rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-ocean:hover {
            background: linear-gradient(135deg, var(--primary-hover), var(--primary-color));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(46, 134, 171, 0.3);
        }

        .alert {
            border-radius: 1rem;
            border: none;
            margin-bottom: 2rem;
        }
    </style>
</head>

<body>
    <!-- Navigation Menu -->
    <div class="menu-tray">
        <span class="me-3 fw-semibold text-muted">
            <i class="fas fa-bars me-2"></i>Menu:
        </span>

        <?php if (is_logged_in()): ?>
            <!-- User is logged in -->
            <span class="me-2 text-primary fw-semibold">
                <i class="fas fa-user me-1"></i>
                Welcome, <?php echo htmlspecialchars(get_user_name()); ?>!
            </span>

            <a href="actions/logout_action.php" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-sign-out-alt me-1"></i>Logout
            </a>

            <?php if (is_admin()): ?>
                <!-- Admin menu -->
                <a href="admin/category.php" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-list me-1"></i>Category
                </a>
                <a href="admin/brand.php" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-tags me-1"></i>Brand
                </a>
                <a href="admin/product.php" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-box-open me-1"></i>Add Product
                </a>
            <?php endif; ?>

            <!-- Products link (for all logged in users) -->
            <a href="view/product.php" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-guitar me-1"></i>Products
            </a>

        <?php else: ?>
            <!-- User is not logged in -->
            <a href="login/register.php" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-user-plus me-1"></i>Register
            </a>
            <a href="login/login.php" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-sign-in-alt me-1"></i>Login
            </a>
        <?php endif; ?>
    </div>

    <div class="container">
        <!-- Success/Error Messages -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show mt-5" role="alert">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'info' ? 'info-circle' : 'exclamation-triangle'); ?> me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <?php if (is_logged_in()): ?>
                        <!-- Logged in user welcome -->
                        <div class="user-info">
                            <h2><i class="fas fa-music me-2 text-primary"></i>Welcome back, <?php echo htmlspecialchars(get_user_name()); ?>!</h2>
                            <p class="mb-0">
                                <i class="fas fa-envelope me-2 text-muted"></i>
                                <strong>Email:</strong> <?php echo htmlspecialchars(get_user_email()); ?>
                                <span class="mx-3">|</span>
                                <i class="fas fa-user-tag me-2 text-muted"></i>
                                <strong>Role:</strong> <?php echo is_admin() ? 'Administrator' : 'Customer'; ?>
                                <?php if (is_admin()): ?>
                                    <span class="mx-3">|</span>
                                    <i class="fas fa-crown me-2 text-warning"></i>
                                    <span class="text-warning fw-bold">Admin Access</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <h1><i class="fas fa-guitar me-3"></i>GhanaTunes Dashboard</h1>
                        <p class="lead">
                            Welcome to your musical journey! Explore traditional Ghanaian instruments and modern music equipment.
                        </p>

                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <?php if (is_admin()): ?>
                                <a href="admin/category.php" class="btn btn-lg btn-ocean">
                                    <i class="fas fa-cog me-2"></i>Manage Categories
                                </a>
                            <?php endif; ?>
                            <a href="#" class="btn btn-lg btn-ocean">
                                <i class="fas fa-shopping-bag me-2"></i>Browse Instruments
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Guest user welcome -->
                        <h1><i class="fas fa-guitar me-3"></i>Welcome to GhanaTunes</h1>
                        <p class="lead">
                            Discover authentic Ghanaian musical instruments and modern equipment. Your premier destination for musical excellence.
                        </p>

                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <a href="login/register.php" class="btn btn-lg btn-ocean">
                                <i class="fas fa-user-plus me-2"></i>Get Started - Register Now
                            </a>
                            <a href="login/login.php" class="btn btn-lg" style="border: 2px solid var(--primary-color); color: var(--primary-color); background: transparent; border-radius: 0.5rem; padding: 0.875rem 2rem; font-weight: 600;">
                                <i class="fas fa-sign-in-alt me-2"></i>Already a Member?
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="row mt-5">
            <div class="col-md-4 mb-4">
                <div class="text-center p-4 bg-white rounded-3 shadow-sm h-100">
                    <i class="fas fa-drum fa-3x text-primary mb-3"></i>
                    <h5>Traditional Instruments</h5>
                    <p class="text-muted">Authentic Ghanaian djembes, kagan, and seperewa crafted by local artisans</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="text-center p-4 bg-white rounded-3 shadow-sm h-100">
                    <i class="fas fa-guitar fa-3x text-primary mb-3"></i>
                    <h5>Modern Equipment</h5>
                    <p class="text-muted">Quality guitars, keyboards, and professional audio equipment</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="text-center p-4 bg-white rounded-3 shadow-sm h-100">
                    <i class="fas fa-shipping-fast fa-3x text-primary mb-3"></i>
                    <h5>Fast Delivery</h5>
                    <p class="text-muted">Quick and reliable shipping across Ghana with mobile money payment</p>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>

</html>