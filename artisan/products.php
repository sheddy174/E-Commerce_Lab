<?php
/**
 * Artisan Products Management Page
 * Allows artisans to manage their own products
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once '../settings/core.php';
require_once '../controllers/artisan_controller.php';

// Check if user is logged in and is artisan
if (!is_logged_in()) {
    header("Location: ../login/login.php");
    exit();
}

if (!is_artisan()) {
    header("Location: ../login/login.php");
    exit();
}

// Get artisan info
$customer_id = get_user_id();
$artisan = get_artisan_by_customer_id_ctr($customer_id);

if (!$artisan) {
    header("Location: dashboard.php");
    exit();
}

$artisan_id = $artisan['artisan_id'];
$artisan_name = get_user_name();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products - Artisan Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #F18F01;
            --primary-hover: #d97d00;
            --secondary-color: #2E86AB;
        }

        body {
            background: linear-gradient(135deg, #FFF3E0 0%, #FFE0B2 100%);
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .artisan-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 0.5rem 1rem rgba(241, 143, 1, 0.3);
        }

        .artisan-header .btn-outline-light {
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: white;
            transition: all 0.3s ease;
        }

        .artisan-header .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: white;
        }

        .artisan-header .btn-outline-light.active {
            background: rgba(255, 255, 255, 0.3);
            border-color: white;
            font-weight: 600;
        }

        .artisan-header .btn-light {
            background: white;
            color: var(--primary-color);
            font-weight: 600;
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.98);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            border-radius: 1rem 1rem 0 0 !important;
            padding: 1.25rem;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .table thead th {
            background: var(--primary-color);
            color: white;
            border: none;
            font-weight: 600;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
        }

        .stats-card {
            background: linear-gradient(135deg, rgba(241, 143, 1, 0.1), rgba(217, 125, 0, 0.1));
            border: 2px solid var(--primary-color);
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(241, 143, 1, 0.2);
        }

        .product-image-preview {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 0.5rem;
        }

        .image-upload-preview {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 0.5rem;
            margin-top: 1rem;
            display: none;
        }

        .file-upload-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .file-upload-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .file-upload-label {
            cursor: pointer;
            display: inline-block;
            padding: 0.5rem 1rem;
            background: var(--primary-color);
            color: white;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .file-upload-label:hover {
            background: var(--primary-hover);
        }

        .currency-input {
            position: relative;
        }

        .currency-symbol {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-weight: 600;
        }

        .currency-input input {
            padding-left: 35px;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }
    </style>
</head>

<body>
    <!-- Artisan Header -->
    <div class="artisan-header">
        <div class="container">
            <div class="row align-items-center mb-3">
                <div class="col-md-6">
                    <a href="../index.php" class="text-white text-decoration-none d-flex align-items-center">
                        <i class="fas fa-guitar fa-2x me-3"></i>
                        <div>
                            <h2 class="mb-0" style="font-size: 1.8rem; font-weight: 700;">GhanaTunes</h2>
                            <p class="mb-0" style="font-size: 0.9rem; opacity: 0.9;">Artisan Dashboard</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="../index.php" class="btn btn-light me-2">
                        <i class="fas fa-home me-2"></i>Home
                    </a>
                    <a href="dashboard.php" class="btn btn-outline-light me-2">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a href="products.php" class="btn btn-outline-light active">
                        <i class="fas fa-box-open me-2"></i>My Products
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h1 class="mb-0" style="font-size: 2rem; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 1rem;">
                        <i class="fas fa-box-open me-3"></i>My Products
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">Manage your product inventory</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Alert Container -->
        <div id="alertContainer" class="mb-4"></div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-box-open fa-2x mb-2" style="color: var(--primary-color);"></i>
                    <h4 class="mb-1" style="color: var(--primary-color);" id="totalProducts">0</h4>
                    <p class="text-muted mb-0">My Products</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-dollar-sign fa-2x mb-2 text-success"></i>
                    <h4 class="text-success mb-1" id="totalValue">GHS 0</h4>
                    <p class="text-muted mb-0">Inventory Value</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-chart-line fa-2x mb-2 text-info"></i>
                    <h4 class="text-info mb-1" id="totalSales">GHS 0</h4>
                    <p class="text-muted mb-0">Total Sales</p>
                </div>
            </div>
        </div>

        <!-- Main Products Card -->
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">
                            <i class="fas fa-table me-2"></i>My Products List
                        </h5>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="fas fa-plus me-2"></i>Add New Product
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Loading Spinner -->
                <div id="loadingSpinner" class="loading-spinner">
                    <div class="spinner-border" style="color: var(--primary-color);" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading products...</p>
                </div>

                <!-- Products Table -->
                <div id="tableContainer" style="display: none;">
                    <table id="productsTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Product Title</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Add New Product
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="addProductForm" enctype="multipart/form-data">
                    <input type="hidden" name="artisan_id" value="<?php echo $artisan_id; ?>">
                    <div class="modal-body">
                        <div class="row">
                            <!-- Category -->
                            <div class="col-md-6 mb-3">
                                <label for="addProductCategory" class="form-label">
                                    <i class="fas fa-list me-2"></i>Category
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="addProductCategory" name="product_cat" required>
                                    <option value="">Select category</option>
                                </select>
                            </div>

                            <!-- Brand -->
                            <div class="col-md-6 mb-3">
                                <label for="addProductBrand" class="form-label">
                                    <i class="fas fa-tag me-2"></i>Brand
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="addProductBrand" name="product_brand" required>
                                    <option value="">Select brand</option>
                                </select>
                            </div>

                            <!-- Product Title -->
                            <div class="col-md-12 mb-3">
                                <label for="addProductTitle" class="form-label">
                                    <i class="fas fa-heading me-2"></i>Product Title
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="addProductTitle"
                                    name="product_title" placeholder="e.g., Handcrafted Talking Drum"
                                    maxlength="200" required>
                                <div class="form-text">3-200 characters</div>
                            </div>

                            <!-- Price -->
                            <div class="col-md-6 mb-3">
                                <label for="addProductPrice" class="form-label">
                                    <i class="fas fa-money-bill me-2"></i>Price (GHS)
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="currency-input">
                                    <span class="currency-symbol">GHS</span>
                                    <input type="number" class="form-control" id="addProductPrice"
                                        name="product_price" placeholder="0.00"
                                        step="0.01" min="0" required>
                                </div>
                            </div>

                            <!-- Keywords -->
                            <div class="col-md-6 mb-3">
                                <label for="addProductKeywords" class="form-label">
                                    <i class="fas fa-search me-2"></i>Keywords
                                </label>
                                <input type="text" class="form-control" id="addProductKeywords"
                                    name="product_keywords" placeholder="e.g., drum, traditional, handmade"
                                    maxlength="100">
                            </div>

                            <!-- Description -->
                            <div class="col-md-12 mb-3">
                                <label for="addProductDesc" class="form-label">
                                    <i class="fas fa-align-left me-2"></i>Description
                                </label>
                                <textarea class="form-control" id="addProductDesc"
                                    name="product_desc" rows="3"
                                    placeholder="Describe your product..."
                                    maxlength="500"></textarea>
                                <div class="form-text">Maximum 500 characters</div>
                            </div>

                            <!-- Image Upload -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-image me-2"></i>Product Image
                                </label>
                                <div class="file-upload-wrapper">
                                    <input type="file" id="addProductImage" name="product_image"
                                        accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                    <label for="addProductImage" class="file-upload-label">
                                        <i class="fas fa-upload me-2"></i>Choose Image
                                    </label>
                                    <span id="addFileName" class="ms-2 text-muted">No file chosen</span>
                                </div>
                                <img id="addImagePreview" class="image-upload-preview" alt="Preview">
                                <div class="form-text">Allowed: JPG, PNG, GIF, WEBP (Max 5MB)</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Add Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Product
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editProductForm" enctype="multipart/form-data">
                    <input type="hidden" id="editProductId" name="product_id">
                    <input type="hidden" name="artisan_id" value="<?php echo $artisan_id; ?>">

                    <div class="modal-body">
                        <div class="row">
                            <!-- Category -->
                            <div class="col-md-6 mb-3">
                                <label for="editProductCategory" class="form-label">
                                    <i class="fas fa-list me-2"></i>Category
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="editProductCategory" name="product_cat" required>
                                    <option value="">Select category</option>
                                </select>
                            </div>

                            <!-- Brand -->
                            <div class="col-md-6 mb-3">
                                <label for="editProductBrand" class="form-label">
                                    <i class="fas fa-tag me-2"></i>Brand
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="editProductBrand" name="product_brand" required>
                                    <option value="">Select brand</option>
                                </select>
                            </div>

                            <!-- Product Title -->
                            <div class="col-md-12 mb-3">
                                <label for="editProductTitle" class="form-label">
                                    <i class="fas fa-heading me-2"></i>Product Title
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="editProductTitle"
                                    name="product_title" maxlength="200" required>
                            </div>

                            <!-- Price -->
                            <div class="col-md-6 mb-3">
                                <label for="editProductPrice" class="form-label">
                                    <i class="fas fa-money-bill me-2"></i>Price (GHS)
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="currency-input">
                                    <span class="currency-symbol">GHS</span>
                                    <input type="number" class="form-control" id="editProductPrice"
                                        name="product_price" step="0.01" min="0" required>
                                </div>
                            </div>

                            <!-- Keywords -->
                            <div class="col-md-6 mb-3">
                                <label for="editProductKeywords" class="form-label">
                                    <i class="fas fa-search me-2"></i>Keywords
                                </label>
                                <input type="text" class="form-control" id="editProductKeywords"
                                    name="product_keywords" maxlength="100">
                            </div>

                            <!-- Description -->
                            <div class="col-md-12 mb-3">
                                <label for="editProductDesc" class="form-label">
                                    <i class="fas fa-align-left me-2"></i>Description
                                </label>
                                <textarea class="form-control" id="editProductDesc"
                                    name="product_desc" rows="3" maxlength="500"></textarea>
                            </div>

                            <!-- Current Image -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Current Image</label>
                                <div>
                                    <img id="editCurrentImage" class="product-image-preview" alt="Current product image">
                                </div>
                            </div>

                            <!-- New Image Upload -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-image me-2"></i>Change Image (Optional)
                                </label>
                                <div class="file-upload-wrapper">
                                    <input type="file" id="editProductImage" name="product_image"
                                        accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                    <label for="editProductImage" class="file-upload-label">
                                        <i class="fas fa-upload me-2"></i>Choose New Image
                                    </label>
                                    <span id="editFileName" class="ms-2 text-muted">No file chosen</span>
                                </div>
                                <img id="editImagePreview" class="image-upload-preview" alt="Preview">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/artisan_products.js"></script>
</body>
</html>