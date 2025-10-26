<?php
session_start();
require_once '../settings/core.php';
require_once '../controllers/product_controller.php';

// Get all products
$products = get_all_products_ctr();
if ($products === false) {
    $products = [];
}
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
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
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
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="fas fa-guitar me-3"></i>Our Products
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">Discover authentic Ghanaian musical instruments</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="../index.php" class="btn btn-light">
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
                                    ? '../' . $product['product_image'] 
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
                                
                                <button class="btn btn-primary w-100">
                                    <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>