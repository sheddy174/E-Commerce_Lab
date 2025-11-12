<?php
session_start();
require_once '../settings/core.php';
require_once '../controllers/product_controller.php';
require_once '../controllers/category_controller.php';
require_once '../controllers/brand_controller.php';

// Get filter parameters
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$brand_id = isset($_GET['brand']) ? (int)$_GET['brand'] : 0;

// Get products based on filters
if ($category_id > 0) {
    $products = filter_products_by_category_ctr($category_id);
} elseif ($brand_id > 0) {
    $products = filter_products_by_brand_ctr($brand_id);
} else {
    $products = view_all_products_ctr();
}

$products = $products ?: [];

// Get categories and brands for filters
$categories = get_all_categories_ctr() ?: [];
$brands = get_all_brands_ctr() ?: [];

// Pagination
$items_per_page = 12;
$total_products = count($products);
$total_pages = ceil($total_products / $items_per_page);
$current_page = isset($_GET['page']) ? max(1, min((int)$_GET['page'], $total_pages)) : 1;
$offset = ($current_page - 1) * $items_per_page;
$paginated_products = array_slice($products, $offset, $items_per_page);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products - GhanaTunes</title>

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

        .filter-section {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        }

        .product-card {
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
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
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--accent-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.875rem;
            z-index: 10;
        }

        .product-body {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-category {
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            min-height: 2.5rem;
            line-height: 1.3;
        }

        .product-description {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 1rem;
            flex-grow: 1;
        }

        .product-price {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .pagination .page-link {
            color: var(--primary-color);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .filter-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: var(--primary-color);
            color: white;
            border-radius: 2rem;
            font-size: 0.875rem;
            margin-right: 0.5rem;
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
                        <i class="fas fa-store me-3"></i>All Products
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">
                        Explore our collection of <?php echo $total_products; ?> authentic instruments
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="../index.php" class="btn btn-light me-2">
                        <i class="fas fa-home me-2"></i>Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Active Filters -->
        <?php if ($category_id > 0 || $brand_id > 0): ?>
            <div class="alert alert-info">
                <strong><i class="fas fa-filter me-2"></i>Active Filters:</strong>
                <?php if ($category_id > 0):
                    $cat = array_filter($categories, fn($c) => $c['cat_id'] == $category_id);
                    $cat = reset($cat);
                ?>
                    <span class="filter-badge">
                        Category: <?php echo htmlspecialchars($cat['cat_name'] ?? 'Unknown'); ?>
                        <a href="all_product.php" class="text-white ms-2">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                <?php endif; ?>

                <?php if ($brand_id > 0):
                    $brand = array_filter($brands, fn($b) => $b['brand_id'] == $brand_id);
                    $brand = reset($brand);
                ?>
                    <span class="filter-badge">
                        Brand: <?php echo htmlspecialchars($brand['brand_name'] ?? 'Unknown'); ?>
                        <a href="all_product.php" class="text-white ms-2">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">
                        <i class="fas fa-folder me-2"></i>Filter by Category
                    </label>
                    <select class="form-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['cat_id']; ?>"
                                <?php echo $category_id == $cat['cat_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['cat_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">
                        <i class="fas fa-tag me-2"></i>Filter by Brand
                    </label>
                    <select class="form-select" id="brandFilter">
                        <option value="">All Brands</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo $brand['brand_id']; ?>"
                                <?php echo $brand_id == $brand['brand_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brand['brand_name']); ?>
                                (<?php echo htmlspecialchars($brand['cat_name']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <a href="all_product.php" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-redo me-2"></i>Clear Filters
                    </a>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <?php if (empty($paginated_products)): ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-5x text-muted mb-3"></i>
                <h3>No Products Found</h3>
                <p class="text-muted">Try adjusting your filters or check back later</p>
                <a href="all_product.php" class="btn btn-primary">
                    <i class="fas fa-redo me-2"></i>View All Products
                </a>
            </div>
        <?php else: ?>
            <div class="row g-4 mb-4">
                <?php foreach ($paginated_products as $product): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="product-card">
                            <div class="product-image-container">
                                <?php
                                $image_url = !empty($product['product_image'])
                                    ? '../../' . $product['product_image']
                                    : 'https://placehold.co/400x400/E3F2FD/2E86AB?text=GhanaTunes';
                                ?>
                                <a href="single_product.php?id=<?php echo $product['product_id']; ?>">
                                    <img src="<?php echo htmlspecialchars($image_url); ?>"
                                        class="product-image"
                                        alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                        onerror="this.src='https://placehold.co/400x400/E3F2FD/2E86AB?text=GhanaTunes'">
                                </a>
                            </div>

                            <div class="product-body">
                                <div class="product-category">
                                    <i class="fas fa-folder me-1"></i>
                                    <?php echo htmlspecialchars($product['cat_name'] ?? 'Uncategorized'); ?>
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-tag me-1"></i>
                                    <?php echo htmlspecialchars($product['brand_name'] ?? 'No Brand'); ?>
                                </div>

                                <a href="single_product.php?id=<?php echo $product['product_id']; ?>"
                                    class="text-decoration-none">
                                    <h5 class="product-title">
                                        <?php echo htmlspecialchars($product['product_title']); ?>
                                    </h5>
                                </a>

                                <?php if (!empty($product['product_desc'])): ?>
                                    <p class="product-description">
                                        <?php
                                        $desc = htmlspecialchars($product['product_desc']);
                                        echo strlen($desc) > 80 ? substr($desc, 0, 80) . '...' : $desc;
                                        ?>
                                    </p>
                                <?php endif; ?>

                                <div class="product-price">
                                    GHS <?php echo number_format($product['product_price'], 2); ?>
                                </div>

                                <div class="d-grid gap-2">
                                    <a href="single_product.php?id=<?php echo $product['product_id']; ?>"
                                        class="btn btn-primary">
                                        <i class="fas fa-eye me-2"></i>View Details
                                    </a>

                                    <?php if (is_logged_in()): ?>
                                        <button class="btn btn-outline-primary add-to-cart-btn"
                                            data-product-id="<?php echo $product['product_id']; ?>">
                                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                        </button>
                                    <?php else: ?>
                                        <a href="../login/login.php" class="btn btn-outline-primary">
                                            <i class="fas fa-sign-in-alt me-2"></i>Login to Buy
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php if ($current_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $current_page - 1;
                                                                    echo $category_id ? '&category=' . $category_id : '';
                                                                    echo $brand_id ? '&brand=' . $brand_id : ''; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i;
                                                                    echo $category_id ? '&category=' . $category_id : '';
                                                                    echo $brand_id ? '&brand=' . $brand_id : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $current_page + 1;
                                                                    echo $category_id ? '&category=' . $category_id : '';
                                                                    echo $brand_id ? '&brand=' . $brand_id : ''; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Category filter change
            $('#categoryFilter').change(function() {
                const categoryId = $(this).val();
                if (categoryId) {
                    window.location.href = 'all_product.php?category=' + categoryId;
                } else {
                    window.location.href = 'all_product.php';
                }
            });

            // Brand filter change
            $('#brandFilter').change(function() {
                const brandId = $(this).val();
                if (brandId) {
                    window.location.href = 'all_product.php?brand=' + brandId;
                } else {
                    window.location.href = 'all_product.php';
                }
            });
        });
    </script>

    <script src="../js/cart.js"></script>
    <script>
        $(document).ready(function() {
            $('.add-to-cart-btn').click(function() {
                const productId = $(this).data('product-id');
                addToCart(productId, 1);
            });
        });
    </script>
</body>

</html>