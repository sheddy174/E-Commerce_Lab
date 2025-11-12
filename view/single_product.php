<?php
session_start();
require_once '../settings/core.php';
require_once '../controllers/product_controller.php';
require_once '../controllers/category_controller.php';
require_once '../controllers/brand_controller.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header("Location: all_product.php");
    exit();
}

// Get product details
$product = view_single_product_ctr($product_id);

if (!$product) {
    header("Location: all_product.php");
    exit();
}

// Get related products (same category, exclude current)
$related_products = filter_products_by_category_ctr($product['product_cat']);
$related_products = array_filter($related_products, fn($p) => $p['product_id'] != $product_id);
$related_products = array_slice($related_products, 0, 4);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_title']); ?> - GhanaTunes</title>

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

        .breadcrumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            color: rgba(255, 255, 255, 0.7);
        }

        .breadcrumb-item a {
            color: white;
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: rgba(255, 255, 255, 0.7);
        }

        .product-detail-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .product-image-main {
            width: 100%;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            object-fit: cover;
            max-height: 500px;
        }

        .product-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .badge-category {
            background: rgba(46, 134, 171, 0.1);
            color: var(--primary-color);
        }

        .badge-brand {
            background: rgba(241, 143, 1, 0.1);
            color: var(--accent-color);
        }

        .product-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .product-price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--accent-color);
            margin-bottom: 1.5rem;
        }

        .product-description {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #495057;
            margin-bottom: 2rem;
        }

        .product-info-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .product-info-item i {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-right: 1rem;
            width: 30px;
            text-align: center;
        }

        .btn-add-cart {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 1rem 2.5rem;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-add-cart:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(46, 134, 171, 0.3);
            color: white;
        }

        .btn-back {
            background: white;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 1rem 2rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: var(--primary-color);
            color: white;
        }

        .related-products-section {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .related-product-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 1rem;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
        }

        .related-product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(46, 134, 171, 0.2);
            border-color: var(--primary-color);
        }

        .related-product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .related-product-body {
            padding: 1rem;
        }

        .related-product-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            min-height: 2.5rem;
        }

        .related-product-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--accent-color);
        }

        .keyword-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            background: rgba(46, 134, 171, 0.1);
            color: var(--primary-color);
            border-radius: 1rem;
            font-size: 0.875rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
    </style>
</head>

<body>
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="../index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="all_product.php">Products</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="all_product.php?category=<?php echo $product['product_cat']; ?>">
                            <?php echo htmlspecialchars($product['cat_name']); ?>
                        </a>
                    </li>
                    <li class="breadcrumb-item active">
                        <?php echo htmlspecialchars($product['product_title']); ?>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Main Product Details -->
        <div class="product-detail-card">
            <div class="row">
                <!-- Product Image -->
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <?php
                    $image_url = !empty($product['product_image'])
                        ? '../../' . $product['product_image']
                        : 'https://placehold.co/600x600/E3F2FD/2E86AB?text=' . urlencode($product['product_title']);
                    ?>
                    <img src="<?php echo htmlspecialchars($image_url); ?>"
                        class="product-image-main"
                        alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                        onerror="this.src='https://placehold.co/600x600/E3F2FD/2E86AB?text=GhanaTunes'">
                </div>

                <!-- Product Information -->
                <div class="col-lg-6">
                    <!-- Badges -->
                    <div class="mb-3">
                        <span class="product-badge badge-category">
                            <i class="fas fa-folder me-1"></i>
                            <?php echo htmlspecialchars($product['cat_name']); ?>
                        </span>
                        <span class="product-badge badge-brand">
                            <i class="fas fa-tag me-1"></i>
                            <?php echo htmlspecialchars($product['brand_name']); ?>
                        </span>
                    </div>

                    <!-- Title -->
                    <h1 class="product-title">
                        <?php echo htmlspecialchars($product['product_title']); ?>
                    </h1>

                    <!-- Price -->
                    <div class="product-price">
                        GHS <?php echo number_format($product['product_price'], 2); ?>
                    </div>

                    <!-- Description -->
                    <?php if (!empty($product['product_desc'])): ?>
                        <div class="product-description">
                            <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Product Description</h5>
                            <p><?php echo nl2br(htmlspecialchars($product['product_desc'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Product Info -->
                    <div class="mb-4">
                        <div class="product-info-item">
                            <i class="fas fa-box"></i>
                            <div>
                                <strong>Product ID:</strong> #<?php echo $product['product_id']; ?>
                            </div>
                        </div>

                        <div class="product-info-item">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>Availability:</strong> <span class="text-success">In Stock</span>
                            </div>
                        </div>

                        <div class="product-info-item">
                            <i class="fas fa-truck"></i>
                            <div>
                                <strong>Delivery:</strong> Nationwide shipping available
                            </div>
                        </div>
                    </div>

                    <!-- Keywords -->
                    <?php if (!empty($product['product_keywords'])): ?>
                        <div class="mb-4">
                            <h6 class="mb-2"><i class="fas fa-tags me-2"></i>Tags:</h6>
                            <?php
                            $keywords = explode(',', $product['product_keywords']);
                            foreach ($keywords as $keyword):
                                $keyword = trim($keyword);
                                if (!empty($keyword)):
                            ?>
                                    <span class="keyword-badge"><?php echo htmlspecialchars($keyword); ?></span>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-3 flex-wrap">
                        <button class="btn btn-add-cart" id="addToCartBtn" data-product-id="<?php echo $product['product_id']; ?>">
                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                        </button>
                        <a href="all_product.php" class="btn btn-back">
                            <i class="fas fa-arrow-left me-2"></i>Back to Products
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
            <div class="related-products-section">
                <h3 class="mb-4">
                    <i class="fas fa-layer-group me-2"></i>Related Products
                </h3>

                <div class="row g-4">
                    <?php foreach ($related_products as $related): ?>
                        <div class="col-lg-3 col-md-6">
                            <a href="single_product.php?id=<?php echo $related['product_id']; ?>"
                                class="text-decoration-none">
                                <div class="related-product-card">
                                    <?php
                                    $related_image = !empty($related['product_image'])
                                        ? '../' . $related['product_image']
                                        : 'https://placehold.co/400x400/E3F2FD/2E86AB?text=GhanaTunes';
                                    ?>
                                    <img src="<?php echo htmlspecialchars($related_image); ?>"
                                        class="related-product-image"
                                        alt="<?php echo htmlspecialchars($related['product_title']); ?>"
                                        onerror="this.src='https://placehold.co/400x400/E3F2FD/2E86AB?text=GhanaTunes'">

                                    <div class="related-product-body">
                                        <h6 class="related-product-title">
                                            <?php echo htmlspecialchars($related['product_title']); ?>
                                        </h6>
                                        <div class="related-product-price">
                                            GHS <?php echo number_format($related['product_price'], 2); ?>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- <script>
        $(document).ready(function() {
            // Add to cart functionality (placeholder)
            $('.btn-add-cart').click(function() {
                alert('Add to Cart functionality will be implemented in future labs!');
            });
        });
    </script> -->

    <script src="../js/cart.js"></script>
    <script>
        $(document).ready(function() {
            $('#addToCartBtn').click(function() {
                const productId = $(this).data('product-id');

                <?php if (is_logged_in()): ?>
                    // User is logged in, add to cart
                    addToCart(productId, 1);
                <?php else: ?>
                    // User not logged in, redirect to login
                    Swal.fire({
                        icon: 'warning',
                        title: 'Login Required',
                        text: 'Please login to add items to cart',
                        showCancelButton: true,
                        confirmButtonText: 'Login',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#2E86AB'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '../login/login.php?redirect=single_product&id=' + productId;
                        }
                    });
                <?php endif; ?>
            });
        });
    </script>

</body>
</html>