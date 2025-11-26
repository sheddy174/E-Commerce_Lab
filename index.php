<?php
session_start();
require_once __DIR__ . '/settings/core.php';

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

// Get featured products (latest 8 products)
//$featured_products = get_featured_products_ctr(8) ?: [];
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

        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            box-shadow: 0 0.5rem 1rem rgba(46, 134, 171, 0.3);
            padding: 1rem 0;
        }

        .navbar-custom .navbar-brand {
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .navbar-custom .nav-link {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            margin: 0 0.5rem;
            transition: all 0.3s ease;
        }

        .navbar-custom .nav-link:hover {
            color: white;
            transform: translateY(-2px);
        }

        .navbar-custom .btn {
            margin-left: 0.5rem;
        }

        .search-box {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 3rem;
            padding: 0.25rem 0.5rem;
            display: flex;
            align-items: center;
            min-width: 300px;
        }

        .search-box input {
            border: none;
            outline: none;
            background: transparent;
            padding: 0.5rem 1rem;
            flex-grow: 1;
        }

        .search-box button {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .search-box button:hover {
            background: var(--primary-hover);
        }

        .hero-section {
            padding: 4rem 0;
            text-align: center;
        }

        .hero-section h1 {
            color: var(--primary-color);
            font-weight: 800;
            margin-bottom: 1rem;
            font-size: 3rem;
        }

        .hero-section .lead {
            color: var(--secondary-color);
            font-size: 1.2rem;
            margin-bottom: 2rem;
            max-width: 700px;
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

        .btn-artisan {
            background: linear-gradient(135deg, var(--accent-color), #C77700);
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.875rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-artisan:hover {
            background: linear-gradient(135deg, #C77700, var(--accent-color));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(241, 143, 1, 0.3);
        }

        .alert {
            border-radius: 1rem;
            border: none;
            margin-bottom: 2rem;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 1rem 2rem rgba(46, 134, 171, 0.2);
        }

        .feature-card i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .product-card {
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 1rem 2rem rgba(46, 134, 171, 0.2);
        }

        .product-image-container {
            position: relative;
            padding-top: 100%;
            overflow: hidden;
            background: #f8f9fa;
        }

        .product-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-body {
            padding: 1.5rem;
        }

        .product-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            min-height: 2.5rem;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }

        .section-title {
            text-align: center;
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 3rem;
            font-size: 2.5rem;
        }

        /* Registration Modal Styles */
        .registration-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 3px solid transparent;
            cursor: pointer;
            height: 100%;
        }

        .registration-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.15);
        }

        .registration-card.customer:hover {
            border-color: var(--primary-color);
        }

        .registration-card.artisan:hover {
            border-color: var(--accent-color);
        }

        .registration-card .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .registration-card.customer .icon {
            color: var(--primary-color);
        }

        .registration-card.artisan .icon {
            color: var(--accent-color);
        }

        .registration-card h4 {
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .registration-card p {
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
            min-height: 60px;
        }

        .registration-card .btn {
            width: 100%;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            border: none;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .btn-outline-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(46, 134, 171, 0.3);
        }

        .dropdown-menu {
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: none;
        }

        .dropdown-item {
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background-color: var(--light-blue);
            color: var(--primary-color);
            transform: translateX(5px);
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-guitar me-2"></i>GhanaTunes
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view/all_product.php">
                            <i class="fas fa-guitar me-1"></i>All Products
                        </a>
                    </li>
                </ul>

                <!-- Search Box -->
                <form class="d-flex me-3" action="view/product_search_result.php" method="GET">
                    <div class="search-box">
                        <input type="text" 
                               name="query" 
                               placeholder="Search instruments..." 
                               required>
                        <button type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <!-- Auth Menu -->
                <ul class="navbar-nav">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                <?php echo htmlspecialchars(get_user_name() ?: 'Guest'); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if (is_admin()): ?>
                                    <li>
                                        <a class="dropdown-item" href="admin/category.php">
                                            <i class="fas fa-list me-2"></i>Categories
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="admin/brand.php">
                                            <i class="fas fa-tags me-2"></i>Brands
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="admin/product.php">
                                            <i class="fas fa-box-open me-2"></i>Manage Products
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="admin/verify_artisans.php">
                                            <i class="fas fa-user-check me-2"></i>Verify Artisans
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php elseif (is_artisan()): ?>
                                    <li>
                                        <a class="dropdown-item" href="artisan/dashboard.php">
                                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="artisan/products.php">
                                            <i class="fas fa-box me-2"></i>My Products
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li>
                                    <a class="dropdown-item" href="actions/logout_action.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="login/login.php" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <!-- CHANGED: Register button now opens modal -->
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#registrationModal">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </button>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Success/Error Messages -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show mt-4" role="alert">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'info' ? 'info-circle' : 'exclamation-triangle'); ?> me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Hero Section -->
        <div class="hero-section">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <?php if (is_logged_in()): ?>
                        <!-- Logged in user welcome -->
                        <div class="user-info">
                            <h2>
                                <i class="fas fa-music me-2 text-primary"></i>
                                Welcome back, <?php echo display_user_name(); ?>!
                            </h2>
                            <p class="mb-0">
                                <i class="fas fa-envelope me-2 text-muted"></i>
                                <strong>Email:</strong> <?php echo display_user_email(); ?>
                                <span class="mx-3">|</span>
                                <i class="fas fa-user-tag me-2 text-muted"></i>
                                <strong>Role:</strong> <?php echo get_user_role_name(); ?>
                                <?php if (is_admin()): ?>
                                    <span class="mx-3">|</span>
                                    <i class="fas fa-crown me-2 text-warning"></i>
                                    <span class="text-warning fw-bold">Admin Access</span>
                                <?php elseif (is_artisan()): ?>
                                    <span class="mx-3">|</span>
                                    <i class="fas fa-hammer me-2" style="color: var(--accent-color);"></i>
                                    <span class="fw-bold" style="color: var(--accent-color);">Artisan Vendor</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <h1><i class="fas fa-guitar me-3"></i>Explore Our Collection</h1>
                        <p class="lead">
                            Discover authentic Ghanaian instruments and modern music equipment
                        </p>

                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <a href="view/all_product.php" class="btn btn-ocean btn-lg">
                                <i class="fas fa-shopping-bag me-2"></i>Browse All Products
                            </a>
                            <?php if (is_admin()): ?>
                                <a href="admin/product.php" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-cog me-2"></i>Manage Products
                                </a>
                            <?php elseif (is_artisan()): ?>
                                <a href="artisan/dashboard.php" class="btn btn-artisan btn-lg">
                                    <i class="fas fa-tachometer-alt me-2"></i>My Dashboard
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <!-- Guest user welcome -->
                        <h1><i class="fas fa-guitar me-3"></i>Welcome to GhanaTunes</h1>
                        <p class="lead">
                            Discover authentic Ghanaian musical instruments and modern equipment. 
                            Your premier destination for musical excellence.
                        </p>

                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <a href="view/all_product.php" class="btn btn-ocean btn-lg">
                                <i class="fas fa-shopping-bag me-2"></i>Browse Products
                            </a>
                            <button class="btn btn-outline-primary btn-lg" data-bs-toggle="modal" data-bs-target="#registrationModal">
                                <i class="fas fa-user-plus me-2"></i>Get Started
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Featured Products Section -->
        <?php if (!empty($featured_products)): ?>
            <div class="mb-5">
                <h2 class="section-title">
                    <i class="fas fa-star me-2"></i>Featured Products
                </h2>
                
                <div class="row g-4 mb-4">
                    <?php foreach (array_slice($featured_products, 0, 4) as $product): ?>
                        <div class="col-lg-3 col-md-6">
                            <div class="product-card">
                                <div class="product-image-container">
                                    <?php 
                                    $image_url = !empty($product['product_image']) 
                                        ? $product['product_image'] 
                                        : 'https://placehold.co/400x400/E3F2FD/2E86AB?text=GhanaTunes';
                                    ?>
                                    <a href="view/single_product.php?id=<?php echo $product['product_id']; ?>">
                                        <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                             class="product-image" 
                                             alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                             onerror="this.src='https://placehold.co/400x400/E3F2FD/2E86AB?text=GhanaTunes'">
                                    </a>
                                </div>
                                
                                <div class="product-body">
                                    <a href="view/single_product.php?id=<?php echo $product['product_id']; ?>" 
                                       class="text-decoration-none">
                                        <h5 class="product-title">
                                            <?php echo htmlspecialchars($product['product_title']); ?>
                                        </h5>
                                    </a>
                                    
                                    <div class="product-price">
                                        GHS <?php echo number_format($product['product_price'], 2); ?>
                                    </div>
                                    
                                    <a href="view/single_product.php?id=<?php echo $product['product_id']; ?>" 
                                       class="btn btn-ocean w-100">
                                        <i class="fas fa-eye me-2"></i>View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="text-center">
                    <a href="view/all_product.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-th me-2"></i>View All Products
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Features Section -->
        <div class="mb-5">
            <h2 class="section-title">Why Choose GhanaTunes?</h2>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <i class="fas fa-drum"></i>
                        <h5>Traditional Instruments</h5>
                        <p class="text-muted">Authentic Ghanaian djembes, kagan, and seperewa crafted by local artisans</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <i class="fas fa-guitar"></i>
                        <h5>Modern Equipment</h5>
                        <p class="text-muted">Quality guitars, keyboards, and professional audio equipment</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <i class="fas fa-shipping-fast"></i>
                        <h5>Fast Delivery</h5>
                        <p class="text-muted">Quick and reliable shipping across Ghana with mobile money payment</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Options Modal -->
    <div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registrationModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Choose Your Account Type
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Customer Registration -->
                        <div class="col-md-6">
                            <div class="registration-card customer" onclick="window.location.href='login/register.php'">
                                <div class="icon">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <h4>Register as Customer</h4>
                                <p>Browse and purchase authentic Ghanaian instruments and modern music equipment</p>
                                <a href="login/register.php" class="btn btn-ocean">
                                    <i class="fas fa-shopping-bag me-2"></i>Shop Now
                                </a>
                            </div>
                        </div>

                        <!-- Artisan Registration -->
                        <div class="col-md-6">
                            <div class="registration-card artisan" onclick="window.location.href='login/register_artisan.php'">
                                <div class="icon">
                                    <i class="fas fa-hammer"></i>
                                </div>
                                <h4>Register as Artisan</h4>
                                <p>Showcase your craftsmanship, sell your instruments, and reach more customers</p>
                                <a href="login/register_artisan.php" class="btn btn-artisan">
                                    <i class="fas fa-store me-2"></i>Sell Products
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <p class="text-muted mb-0">
                            Already have an account? 
                            <a href="login/login.php" class="text-primary fw-bold">
                                <i class="fas fa-sign-in-alt me-1"></i>Login Here
                            </a>
                        </p>
                    </div>
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