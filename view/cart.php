<?php
/**
 * Shopping Cart View
 * Displays user's cart with options to update quantities and proceed to checkout
 */

session_start();
require_once '../settings/core.php';
require_once '../controllers/cart_controller.php';

// Require login
if (!is_logged_in()) {
    header("Location: ../login/login.php?redirect=cart");
    exit();
}

$customer_id = get_user_id();

// Get cart items
$cart_items = get_user_cart_ctr($customer_id);
$cart_items = $cart_items ?: [];

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['product_price'] * $item['qty'];
}

$shipping = 0; // Free shipping for now
$total = $subtotal + $shipping;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - GhanaTunes</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 0.5rem 1rem rgba(46, 134, 171, 0.3);
        }
        
        .cart-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .cart-item {
            border-bottom: 1px solid #e9ecef;
            padding: 1.5rem 0;
            transition: all 0.3s ease;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-item:hover {
            background: #f8f9fa;
            border-radius: 0.5rem;
        }
        
        .product-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 0.5rem;
        }
        
        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .product-meta {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .product-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--accent-color);
        }
        
        .qty-control {
            display: inline-flex;
            align-items: center;
            border: 2px solid #dee2e6;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .qty-control button {
            background: white;
            border: none;
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .qty-control button:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .qty-control input {
            border: none;
            text-align: center;
            width: 60px;
            padding: 0.5rem;
            font-weight: 600;
        }
        
        .btn-remove {
            color: #dc3545;
            transition: all 0.3s ease;
        }
        
        .btn-remove:hover {
            color: #bb2d3b;
            transform: scale(1.1);
        }
        
        .summary-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 2rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .summary-total {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            padding-top: 1rem;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 1rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(46, 134, 171, 0.3);
        }
        
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
        }
        
        .empty-cart i {
            font-size: 5rem;
            color: #dee2e6;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="fas fa-shopping-cart me-3"></i>Shopping Cart
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">
                        <?php echo count($cart_items); ?> item<?php echo count($cart_items) != 1 ? 's' : ''; ?> in your cart
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="all_product.php" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Alert Container -->
        <div id="alertContainer"></div>

        <?php if (empty($cart_items)): ?>
            <!-- Empty Cart -->
            <div class="cart-card">
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Your cart is empty</h3>
                    <p class="text-muted mb-4">Looks like you haven't added any items to your cart yet.</p>
                    <a href="all_product.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="cart-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0">
                                <i class="fas fa-list me-2"></i>Cart Items
                            </h4>
                            <button class="btn btn-outline-danger btn-sm" id="emptyCartBtn">
                                <i class="fas fa-trash me-2"></i>Empty Cart
                            </button>
                        </div>

                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item" data-product-id="<?php echo $item['p_id']; ?>">
                                <div class="row align-items-center">
                                    <!-- Product Image -->
                                    <div class="col-md-2 text-center mb-3 mb-md-0">
                                        <?php 
                                        $image_url = !empty($item['product_image']) 
                                            ? '../../' . $item['product_image'] 
                                            : 'https://placehold.co/200x200/E3F2FD/2E86AB?text=Product';
                                        ?>
                                        <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                             class="product-image" 
                                             alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                                             onerror="this.src='https://placehold.co/200x200/E3F2FD/2E86AB?text=Product'">
                                    </div>

                                    <!-- Product Details -->
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <h5 class="product-title">
                                            <?php echo htmlspecialchars($item['product_title']); ?>
                                        </h5>
                                        <div class="product-meta">
                                            <i class="fas fa-folder me-1"></i>
                                            <?php echo htmlspecialchars($item['cat_name']); ?>
                                            <span class="mx-2">|</span>
                                            <i class="fas fa-tag me-1"></i>
                                            <?php echo htmlspecialchars($item['brand_name']); ?>
                                        </div>
                                        <div class="product-price mt-2">
                                            GHS <?php echo number_format($item['product_price'], 2); ?>
                                        </div>
                                    </div>

                                    <!-- Quantity Control -->
                                    <div class="col-md-3 mb-3 mb-md-0 text-center">
                                        <label class="form-label small text-muted">Quantity</label>
                                        <div class="qty-control mx-auto">
                                            <button type="button" class="qty-decrease" data-product-id="<?php echo $item['p_id']; ?>">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" 
                                                   class="qty-input" 
                                                   value="<?php echo $item['qty']; ?>" 
                                                   min="1" 
                                                   max="99" 
                                                   data-product-id="<?php echo $item['p_id']; ?>"
                                                   readonly>
                                            <button type="button" class="qty-increase" data-product-id="<?php echo $item['p_id']; ?>">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Subtotal & Remove -->
                                    <div class="col-md-3 text-center">
                                        <label class="form-label small text-muted">Subtotal</label>
                                        <div class="product-price item-subtotal">
                                            GHS <?php echo number_format($item['product_price'] * $item['qty'], 2); ?>
                                        </div>
                                        <button type="button" 
                                                class="btn btn-link btn-remove mt-2" 
                                                data-product-id="<?php echo $item['p_id']; ?>"
                                                title="Remove item">
                                            <i class="fas fa-trash-alt me-1"></i>Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="summary-card">
                        <h4 class="mb-4">
                            <i class="fas fa-receipt me-2"></i>Order Summary
                        </h4>

                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span id="summarySubtotal">GHS <?php echo number_format($subtotal, 2); ?></span>
                        </div>

                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span class="text-success">
                                <?php echo $shipping == 0 ? 'FREE' : 'GHS ' . number_format($shipping, 2); ?>
                            </span>
                        </div>

                        <div class="summary-row summary-total">
                            <span>Total:</span>
                            <span id="summaryTotal">GHS <?php echo number_format($total, 2); ?></span>
                        </div>

                        <div class="d-grid gap-3 mt-4">
                            <a href="checkout.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-lock me-2"></i>Proceed to Checkout
                            </a>
                            <a href="all_product.php" class="btn btn-outline-primary">
                                <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                            </a>
                        </div>

                        <div class="mt-4 text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Secure Checkout
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/cart.js"></script>
</body>
</html>