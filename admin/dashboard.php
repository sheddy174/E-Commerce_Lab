<?php
/**
 * Admin Dashboard - Main Overview Page
 * Central hub for admin operations
 * CORRECTED VERSION - Added Orders navigation button
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../settings/core.php';

// Check if user is logged in and is admin
require_admin();

// You can add dashboard stats queries here later
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GhanaTunes</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2E86AB;
            --primary-hover: #1B5E7A;
            --accent-color: #F18F01;
            --secondary-color: #6c757d;
        }
        
        body {
            background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
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
        
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.98);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.15);
        }
        
        .stats-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 1rem 2rem rgba(46, 134, 171, 0.2);
        }
        
        .stats-card .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .stats-card .number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stats-card .label {
            color: var(--secondary-color);
            font-size: 1rem;
        }
        
        .quick-action-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            display: block;
            color: inherit;
        }
        
        .quick-action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.15);
            text-decoration: none;
            color: inherit;
        }
        
        .quick-action-card .icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
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
                    <a href="orders.php" class="btn btn-outline-light me-2">
                        <i class="fas fa-shopping-cart me-2"></i>Orders
                    </a>
                    <a href="category.php" class="btn btn-outline-light me-2">
                        <i class="fas fa-list me-2"></i>Categories
                    </a>
                    <a href="brand.php" class="btn btn-outline-light me-2">
                        <i class="fas fa-tags me-2"></i>Brands
                    </a>
                    <a href="product.php" class="btn btn-outline-light me-2">
                        <i class="fas fa-box-open me-2"></i>Products
                    </a>
                    <a href="verify_artisans.php" class="btn btn-outline-light">
                        <i class="fas fa-user-check me-2"></i>Artisans
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h1 class="mb-0" style="font-size: 2rem; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 1rem;">
                        <i class="fas fa-tachometer-alt me-3"></i>Admin Dashboard
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">Welcome back, <?php echo htmlspecialchars(get_user_name()); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Welcome Card -->
        <div class="welcome-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3><i class="fas fa-crown me-2"></i>Welcome to GhanaTunes Admin Panel</h3>
                    <p class="mb-0 opacity-75">
                        Manage your e-commerce platform efficiently. Access all administrative functions from this central dashboard.
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <i class="fas fa-chart-line fa-4x opacity-50"></i>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card" onclick="window.location.href='orders.php'">
                    <div class="icon" style="color: #dc3545;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="number" style="color: #dc3545;" id="totalOrders">-</div>
                    <div class="label">Orders</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" onclick="window.location.href='category.php'">
                    <div class="icon" style="color: var(--primary-color);">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="number" style="color: var(--primary-color);" id="totalCategories">-</div>
                    <div class="label">Categories</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" onclick="window.location.href='brand.php'">
                    <div class="icon" style="color: #17a2b8;">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="number" style="color: #17a2b8;" id="totalBrands">-</div>
                    <div class="label">Brands</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" onclick="window.location.href='product.php'">
                    <div class="icon" style="color: #28a745;">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="number" style="color: #28a745;" id="totalProducts">-</div>
                    <div class="label">Products</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="mb-3"><i class="fas fa-bolt me-2"></i>Quick Actions</h4>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <a href="orders.php" class="quick-action-card">
                    <div class="icon" style="color: #dc3545;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h5>Manage Orders</h5>
                    <p class="text-muted mb-0">View and process customer orders</p>
                </a>
            </div>
            <div class="col-md-3">
                <a href="category.php" class="quick-action-card">
                    <div class="icon" style="color: var(--primary-color);">
                        <i class="fas fa-list"></i>
                    </div>
                    <h5>Manage Categories</h5>
                    <p class="text-muted mb-0">Add, edit, or remove product categories</p>
                </a>
            </div>
            <div class="col-md-3">
                <a href="brand.php" class="quick-action-card">
                    <div class="icon" style="color: #17a2b8;">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h5>Manage Brands</h5>
                    <p class="text-muted mb-0">Organize brands by category</p>
                </a>
            </div>
            <div class="col-md-3">
                <a href="product.php" class="quick-action-card">
                    <div class="icon" style="color: #28a745;">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <h5>Add Products</h5>
                    <p class="text-muted mb-0">Add new instruments to inventory</p>
                </a>
            </div>
        </div>

        <!-- Additional Actions Row -->
        <div class="row mb-4">
            <div class="col-md-4">
                <a href="verify_artisans.php" class="quick-action-card">
                    <div class="icon" style="color: var(--accent-color);">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <h5>Verify Artisans</h5>
                    <p class="text-muted mb-0">Review artisan applications</p>
                </a>
            </div>
            <div class="col-md-4">
                <a href="orders.php?status=pending" class="quick-action-card">
                    <div class="icon" style="color: #ffc107;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h5>Pending Orders</h5>
                    <p class="text-muted mb-0">View orders awaiting processing</p>
                </a>
            </div>
            <div class="col-md-4">
                <a href="../view/cart.php" class="quick-action-card">
                    <div class="icon" style="color: #6f42c1;">
                        <i class="fas fa-store"></i>
                    </div>
                    <h5>View Store</h5>
                    <p class="text-muted mb-0">See customer shopping experience</p>
                </a>
            </div>
        </div>

        <!-- Additional Info Cards -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5><i class="fas fa-info-circle me-2 text-primary"></i>System Information</h5>
                        <hr>
                        <p><strong>Platform:</strong> GhanaTunes E-Commerce</p>
                        <p><strong>Admin:</strong> <?php echo htmlspecialchars(get_user_name()); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars(get_user_email()); ?></p>
                        <p class="mb-0"><strong>Last Login:</strong> <?php echo date('M d, Y - h:i A'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5><i class="fas fa-tasks me-2 text-success"></i>Quick Links</h5>
                        <hr>
                        <div class="d-grid gap-2">
                            <a href="../index.php" class="btn btn-outline-primary">
                                <i class="fas fa-eye me-2"></i>View Home Page
                            </a>
                            <a href="orders.php" class="btn btn-outline-danger">
                                <i class="fas fa-shopping-cart me-2"></i>View All Orders
                            </a>
                            <a href="product.php" class="btn btn-outline-success">
                                <i class="fas fa-plus me-2"></i>Add New Product
                            </a>
                            <a href="verify_artisans.php" class="btn btn-outline-warning">
                                <i class="fas fa-user-check me-2"></i>Pending Verifications
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/dashboard.js"></script>
</body>
</html>