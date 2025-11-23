<?php
session_start();
require_once '../settings/core.php';
require_once '../controllers/product_controller.php';
require_once '../controllers/category_controller.php';
require_once '../controllers/brand_controller.php';
require_once '../controllers/cart_controller.php';

// Get search query
$search_query = isset($_GET['query']) ? trim($_GET['query']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$brand_id = isset($_GET['brand']) ? (int)$_GET['brand'] : 0;

// Get products based on search and filters
if (!empty($search_query)) {
    $products = search_products_advanced_ctr($search_query);
    
    // Apply additional filters if specified
    if ($category_id > 0 && $products) {
        $products = array_filter($products, fn($p) => $p['product_cat'] == $category_id);
    }
    
    if ($brand_id > 0 && $products) {
        $products = array_filter($products, fn($p) => $p['product_brand'] == $brand_id);
    }
} else {
    // No search query, redirect to all products
    header("Location: all_product.php");
    exit();
}

$products = $products ?: [];

// Get categories and brands for filters
$categories = get_all_categories_ctr() ?: [];
$brands = get_all_brands_ctr() ?: [];

// Get user info if logged in
$customer_name = is_logged_in() ? get_user_name() : '';
$cart_count = is_logged_in() ? get_cart_item_count_ctr(get_user_id()) : 0;

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
    <title>Search: <?php echo htmlspecialchars($search_query); ?> - GhanaTunes</title>
    
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
        
        .search-bar {
            background: white;
            border-radius: 3rem;
            padding: 0.5rem 1rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
            border: 2px solid var(--primary-color);
        }
        
        .search-bar input {
            border: none;
            outline: none;
            font-size: 1rem;
            padding: 0.5rem;
        }
        
        .search-bar button {
            background: var(--primary-color);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .search-bar button:hover {
            background: var(--primary-hover);
        }
        
        .search-results-info {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
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
        
        .highlight {
            background: yellow;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
            font-weight: 600;
        }
        
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
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
        
        .pagination .page-link {
            color: var(--primary-color);
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
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

    <!-- Page Header with Search -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center mb-3">
                <div class="col-md-8">
                    <h1 class="page-title">
                        <i class="fas fa-search me-3"></i>Search Results
                    </h1>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="all_product.php" class="btn btn-outline-primary">
                        <i class="fas fa-th me-2"></i>All Products
                    </a>
                </div>
            </div>
            
            <!-- Search Bar -->
            <form method="GET" action="product_search_result.php" class="search-bar">
                <div class="row align-items-center g-0">
                    <div class="col">
                        <input type="text" 
                               name="query" 
                               class="form-control border-0" 
                               placeholder="Search for instruments, brands, categories..."
                               value="<?php echo htmlspecialchars($search_query); ?>"
                               required>
                    </div>
                    <div class="col-auto">
                        <button type="submit">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Search Results Info -->
        <div class="search-results-info">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-2">
                        <i class="fas fa-list-check me-2 text-primary"></i>
                        Found <strong class="text-primary"><?php echo $total_products; ?></strong> 
                        result<?php echo $total_products != 1 ? 's' : ''; ?> for 
                        "<strong class="text-primary"><?php echo htmlspecialchars($search_query); ?></strong>"
                    </h4>
                    
                    <?php if ($category_id > 0 || $brand_id > 0): ?>
                        <div class="mt-2">
                            <strong>Active Filters:</strong>
                            <?php if ($category_id > 0): 
                                $cat = array_filter($categories, fn($c) => $c['cat_id'] == $category_id);
                                $cat = reset($cat);
                            ?>
                                <span class="filter-badge">
                                    Category: <?php echo htmlspecialchars($cat['cat_name'] ?? 'Unknown'); ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($brand_id > 0): 
                                $brand = array_filter($brands, fn($b) => $b['brand_id'] == $brand_id);
                                $brand = reset($brand);
                            ?>
                                <span class="filter-badge">
                                    Brand: <?php echo htmlspecialchars($brand['brand_name'] ?? 'Unknown'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="product_search_result.php?query=<?php echo urlencode($search_query); ?>" class="btn btn-outline-primary">
                        <i class="fas fa-redo me-2"></i>Clear Filters
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h5 class="mb-3">
                <i class="fas fa-filter me-2"></i>Narrow Your Results
            </h5>
            <div class="row align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold">
                        <i class="fas fa-folder me-2"></i>Category
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
                
                <div class="col-md-5">
                    <label class="form-label fw-bold">
                        <i class="fas fa-tag me-2"></i>Brand
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
                
                <div class="col-md-2">
                    <a href="product_search_result.php?query=<?php echo urlencode($search_query); ?>" 
                       class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times me-2"></i>Clear
                    </a>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <?php if (empty($paginated_products)): ?>
            <div class="no-results">
                <i class="fas fa-search fa-5x text-muted mb-3"></i>
                <h3>No Results Found</h3>
                <p class="text-muted mb-4">
                    We couldn't find any products matching "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                </p>
                <p class="text-muted">Try:</p>
                <ul class="list-unstyled text-muted">
                    <li><i class="fas fa-check me-2 text-primary"></i>Using different keywords</li>
                    <li><i class="fas fa-check me-2 text-primary"></i>Checking your spelling</li>
                    <li><i class="fas fa-check me-2 text-primary"></i>Using more general terms</li>
                </ul>
                <a href="all_product.php" class="btn btn-primary btn-lg mt-3">
                    <i class="fas fa-th me-2"></i>Browse All Products
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
                                
                                <a href="single_product.php?id=<?php echo $product['product_id']; ?>" 
                                   class="btn btn-primary w-100">
                                    <i class="fas fa-eye me-2"></i>View Details
                                </a>
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
                                <a class="page-link" href="?query=<?php echo urlencode($search_query); ?>&page=<?php echo $current_page - 1; echo $category_id ? '&category='.$category_id : ''; echo $brand_id ? '&brand='.$brand_id : ''; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                <a class="page-link" href="?query=<?php echo urlencode($search_query); ?>&page=<?php echo $i; echo $category_id ? '&category='.$category_id : ''; echo $brand_id ? '&brand='.$brand_id : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?query=<?php echo urlencode($search_query); ?>&page=<?php echo $current_page + 1; echo $category_id ? '&category='.$category_id : ''; echo $brand_id ? '&brand='.$brand_id : ''; ?>">
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
    <script>
        $(document).ready(function() {
            const searchQuery = '<?php echo addslashes($search_query); ?>';
            
            // Category filter change
            $('#categoryFilter').change(function() {
                const categoryId = $(this).val();
                let url = 'product_search_result.php?query=' + encodeURIComponent(searchQuery);
                if (categoryId) {
                    url += '&category=' + categoryId;
                }
                window.location.href = url;
            });
            
            // Brand filter change
            $('#brandFilter').change(function() {
                const brandId = $(this).val();
                let url = 'product_search_result.php?query=' + encodeURIComponent(searchQuery);
                if (brandId) {
                    url += '&brand=' + brandId;
                }
                window.location.href = url;
            });
        });
    </script>
</body>
</html>