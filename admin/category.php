<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>


<?php
/**
 * Admin Category Management View
 * Provides CRUD interface for category management
 */

session_start();
require_once '../settings/core.php';

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: ../login/login.php");
    exit();
}

// Check if user is admin
if (!is_admin()) {
    header("Location: ../login/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management - GhanaTunes Admin</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2E86AB;
            --primary-hover: #1B5E7A;
            --accent-color: #F18F01;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
        }

        body {
            background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* .admin-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 0.5rem 1rem rgba(46, 134, 171, 0.3);
        }
         */
        /* Fix breadcrumb and navigation styling */
        .admin-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 0.5rem 1rem rgba(46, 134, 171, 0.3);
        }

        .admin-header .btn-outline-light {
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: white;
            transition: all 0.3s ease;
        }

        .admin-header .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: white;
            color: white;
        }

        .admin-header .btn-outline-light.active {
            background: rgba(255, 255, 255, 0.3);
            border-color: white;
            color: white;
            font-weight: 600;
        }

        .admin-header .btn-light {
            background: white;
            color: var(--primary-color);
            font-weight: 600;
            border: none;
        }

        .admin-header .btn-light:hover {
            background: rgba(255, 255, 255, 0.9);
            color: var(--primary-hover);
        }

        /* Remove any conflicting breadcrumb styles */
        .breadcrumb {
            background: none !important;
            padding: 0;
            margin: 0;
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
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

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .table {
            border-radius: 0.5rem;
            overflow: hidden;
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

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(46, 134, 171, 0.25);
        }

        .alert {
            border-radius: 0.75rem;
            border: none;
        }

        .badge {
            font-size: 0.875em;
        }

        .stats-card {
            background: linear-gradient(135deg, rgba(46, 134, 171, 0.1), rgba(27, 94, 122, 0.1));
            border: 2px solid var(--primary-color);
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(46, 134, 171, 0.2);
        }

        .breadcrumb {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 0.5rem;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }
    </style>
</head>

<body>
    <!-- Admin Header -->
    <!-- <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-0">
                        <i class="fas fa-cogs me-3"></i>Category Management
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">Manage your product categories</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-md-end mb-0">
                            <li class="breadcrumb-item">
                                <a href="../index.php" class="text-white text-decoration-none">
                                    <i class="fas fa-home me-1"></i>Home
                                </a>
                            </li>
                            <li class="breadcrumb-item active text-white-50">Categories</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Admin Header -->
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center mb-3">
                <div class="col-md-6">
                    <a href="../index.php" class="text-white text-decoration-none d-flex align-items-center">
                        <i class="fas fa-guitar fa-2x me-3"></i>
                        <div>
                            <h2 class="mb-0" style="font-size: 1.8rem; font-weight: 700;">GhanaTunes</h2>
                            <p class="mb-0" style="font-size: 0.9rem; opacity: 0.9;">Admin Dashboard</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="../index.php" class="btn btn-light me-2">
                        <i class="fas fa-home me-2"></i>Home
                    </a>
                    <a href="category.php" class="btn btn-outline-light me-2 active" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-list me-2"></i>Categories
                    </a>
                    <a href="brand.php" class="btn btn-outline-light me-2">
                        <i class="fas fa-tags me-2"></i>Brands
                    </a>
                    <a href="product.php" class="btn btn-outline-light">
                        <i class="fas fa-box-open me-2"></i>Products
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h1 class="mb-0" style="font-size: 2rem; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 1rem;">
                        <i class="fas fa-cogs me-3"></i>Category Management
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">Manage your product categories</p>
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
                    <i class="fas fa-list fa-2x text-primary mb-2"></i>
                    <h4 class="text-primary mb-1" id="totalCategories">0</h4>
                    <p class="text-muted mb-0">Total Categories</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-plus-circle fa-2x text-success mb-2"></i>
                    <h4 class="text-success mb-1" id="todayAdded">0</h4>
                    <p class="text-muted mb-0">Added Today</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-box fa-2x text-warning mb-2"></i>
                    <h4 class="text-warning mb-1" id="totalProducts">0</h4>
                    <p class="text-muted mb-0">Total Products</p>
                </div>
            </div>
        </div>

        <!-- Main Category Management Card -->
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">
                            <i class="fas fa-table me-2"></i>Categories List
                        </h5>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i class="fas fa-plus me-2"></i>Add New Category
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Loading Spinner -->
                <div id="loadingSpinner" class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading categories...</p>
                </div>

                <!-- Categories Table -->
                <div id="tableContainer" style="display: none;">
                    <table id="categoriesTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category Name</th>
                                <th>Products Count</th>
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

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Add New Category
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="addCategoryForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="addCategoryName" class="form-label">
                                <i class="fas fa-tag me-2"></i>Category Name
                            </label>
                            <input type="text"
                                class="form-control"
                                id="addCategoryName"
                                name="cat_name"
                                placeholder="Enter category name"
                                maxlength="100"
                                required>
                            <div class="form-text">
                                Category name must be unique and between 2-100 characters
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Add Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Category
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editCategoryForm">
                    <div class="modal-body">
                        <input type="hidden" id="editCategoryId" name="cat_id">
                        <div class="mb-3">
                            <label for="editCategoryName" class="form-label">
                                <i class="fas fa-tag me-2"></i>Category Name
                            </label>
                            <input type="text"
                                class="form-control"
                                id="editCategoryName"
                                name="cat_name"
                                placeholder="Enter category name"
                                maxlength="100"
                                required>
                            <div class="form-text">
                                Category name must be unique and between 2-100 characters
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-trash me-2"></i>Delete Category
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h5>Are you sure?</h5>
                        <p class="text-muted">
                            You are about to delete the category "<strong id="deleteCategoryName"></strong>".
                            This action cannot be undone.
                        </p>
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-2"></i>
                            Categories with associated products cannot be deleted.
                        </div>
                    </div>
                    <input type="hidden" id="deleteCategoryId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash me-2"></i>Delete Category
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/category.js"></script>
</body>

</html>