<?php
session_start();
require_once '../settings/core.php';
require_once '../controllers/product_controller.php';
require_once '../controllers/cart_controller.php';

// Get all products
$products = get_all_products_ctr();
if ($products === false) {
    $products = [];
}

// Get user info if logged in
$customer_name = is_logged_in() ? get_user_name() : '';
$cart_count = is_logged_in() ? get_cart_item_count_ctr(get_user_id()) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - GhanaTunes</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2E86AB;
            --primary-hover: #1B5E7A;
            --accent-color: #F18F01;
        }
        
        body {
            background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Top Navigation Bar */
        .top-navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            padding: 1rem 0;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        }

        .brand-logo {
            color: white;
            font-size: 1.75rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .brand-logo i {
            color: var(--accent-color);
        }

        .brand-logo:hover {
            color: rgba(255, 255, 255, 0.9);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .nav-link-custom {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link-custom:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-link-custom.active {
            background: rgba(255, 255, 255, 0.2);
        }

        .cart-badge {
            position: relative;
        }

        .cart-badge .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--accent-color);
            border-radius: 50%;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .user-menu {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .user-menu:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .page-header {
            background: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.05);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
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
            padding-top: 75%; 
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
            font-size: 1.1rem;
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
        
        .product-category {
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        
        .no-products {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="top-navbar">
        <div class="container">
            <div class="row align-items-center">
                <!-- Brand Logo -->
                <div class="col-md-3">
                    <a href="../index.php" class="brand-logo">
                        <i class="fas fa-music"></i>
                        <span>GhanaTunes</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="col-md-5">
                    <div class="nav-links">
                        <a href="../index.php" class="nav-link-custom">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                        <a href="all_product.php" class="nav-link-custom">
                            <i class="fas fa-store me-1"></i>All Products
                        </a>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="col-md-4">
                    <div class="d-flex align-items-center justify-content-end gap-3">
                        <?php if (is_logged_in()): ?>
                            <!-- Cart Icon -->
                            <a href="cart.php" class="nav-link-custom cart-badge">
                                <i class="fas fa-shopping-cart fa-lg"></i>
                                <?php if ($cart_count > 0): ?>
                                    <span class="badge"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </a>

                            <!-- User Menu -->
                            <div class="dropdown">
                                <a href="#" class="user-menu dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-user me-1"></i>
                                    <?php echo htmlspecialchars($customer_name); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="../login/logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a href="../login/login.php" class="nav-link-custom">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="page-title">
                        <i class="fas fa-guitar me-3"></i>Our Products
                    </h1>
                    <p class="text-muted mb-0">Discover authentic Ghanaian musical instruments</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="../index.php" class="btn btn-outline-primary">
                        <i class="fas fa-home me-2"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <?php if (empty($products)): ?>
            <!-- No Products -->
            <div class="no-products">
                <i class="fas fa-box-open fa-5x mb-3"></i>
                <h3>No Products Available</h3>
                <p>Check back soon for new arrivals!</p>
            </div>
        <?php else: ?>
            <!-- Products Grid -->
            <div class="row g-4">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 col-lg-3">
                        <div class="product-card">
                            <!-- Product Image -->
                            <div class="product-image-container">
                                <?php 
                                // Use online placeholder if no image exists
                                $image_url = !empty($product['product_image']) 
                                    ? '../../' . $product['product_image'] 
                                    : 'https://placehold.co/400x400/E3F2FD/2E86AB?text=GhanaTunes';
                                ?>
                                <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                     class="product-image" 
                                     alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                     onerror="this.src='https://placehold.co/400x400/E3F2FD/2E86AB?text=GhanaTunes'">
                            </div>
                            
                            <!-- Product Body -->
                            <div class="product-body">
                                <div class="product-category">
                                    <i class="fas fa-folder me-1"></i>
                                    <?php echo htmlspecialchars($product['cat_name'] ?? 'Uncategorized'); ?>
                                </div>
                                <div class="product-category">
                                    <i class="fas fa-tag me-1"></i>
                                    <?php echo htmlspecialchars($product['brand_name'] ?? 'No Brand'); ?>
                                </div>
                                
                                <h5 class="product-title">
                                    <?php echo htmlspecialchars($product['product_title']); ?>
                                </h5>
                                
                                <?php if (!empty($product['product_desc'])): ?>
                                    <p class="text-muted small">
                                        <?php 
                                        $desc = htmlspecialchars($product['product_desc']);
                                        echo strlen($desc) > 80 ? substr($desc, 0, 80) . '...' : $desc;
                                        ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="product-price">
                                    GHS <?php echo number_format($product['product_price'], 2); ?>
                                </div>
                                
                                <?php if (is_logged_in()): ?>
                                    <!-- User is logged in - show Add to Cart button -->
                                    <button class="btn btn-primary w-100 add-to-cart-btn" 
                                            data-product-id="<?php echo $product['product_id']; ?>">
                                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                    </button>
                                <?php else: ?>
                                    <!-- User not logged in - show Login button -->
                                    <a href="../login/login.php?redirect=product" class="btn btn-primary w-100">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login to Buy
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- jQuery (required for cart.js) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 (required for cart.js alerts) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Cart functionality -->
    <script src="../js/cart.js"></script>
    
    <script>
        $(document).ready(function() {
            // Add to cart button click handler
            $('.add-to-cart-btn').click(function() {
                const productId = $(this).data('product-id');
                addToCart(productId, 1);
            });
        });
    </script>
</body>
</html>