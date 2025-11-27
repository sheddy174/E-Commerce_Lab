<?php
/**
 * Artisan Dashboard
 * Main dashboard for artisans to manage their profile and view statistics
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once '../settings/core.php';
require_once '../controllers/artisan_controller.php';
require_once '../controllers/product_controller.php';

// Check if user is logged in and is an artisan
require_artisan();

$customer_id = get_user_id();
$customer_name = get_user_name();

// Get artisan profile
$artisan_profile = get_artisan_profile_ctr($customer_id);

if (!$artisan_profile) {
    // If no profile found, redirect to registration
    header("Location: ../login/register_artisan.php");
    exit();
}

$artisan_id = $artisan_profile['artisan_id'];
$verification_status = $artisan_profile['verification_status'];

// Get artisan statistics
$stats = get_artisan_stats_ctr($artisan_id);

// Get artisan products (limited to 5 for dashboard)
$products = get_artisan_products_ctr($artisan_id);
$recent_products = $products ? array_slice($products, 0, 5) : [];

// Determine verification badge
$verification_badge = '';
$verification_class = '';
switch ($verification_status) {
    case 'verified':
        $verification_badge = '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Verified</span>';
        $verification_class = 'success';
        break;
    case 'pending':
        $verification_badge = '<span class="badge bg-warning"><i class="fas fa-clock me-1"></i>Pending Verification</span>';
        $verification_class = 'warning';
        break;
    case 'rejected':
        $verification_badge = '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Rejected</span>';
        $verification_class = 'danger';
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artisan Dashboard - GhanaTunes</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2E86AB;
            --accent-color: #F18F01;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        body {
            background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--accent-color), #C77700);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: white !important;
        }
        
        .dashboard-container {
            padding: 2rem 0;
        }
        
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--accent-color), #C77700);
            color: white;
            border: none;
            font-weight: 600;
            padding: 1rem 1.5rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
        }
        
        .stat-card .icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent-color);
        }
        
        .stat-card .label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .profile-image-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 1rem;
        }
        
        .profile-image {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--accent-color);
        }
        
        .profile-image-overlay {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--accent-color);
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .profile-image-overlay:hover {
            background: #C77700;
            transform: scale(1.1);
        }
        
        /* ADDED: Preview image styling */
        .profile-image-preview {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--success-color);
            display: none;
        }
        
        .verification-alert {
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 0.5rem;
        }
        
        .btn-artisan {
            background: linear-gradient(135deg, var(--accent-color), #C77700);
            border: none;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-artisan:hover {
            background: linear-gradient(135deg, #C77700, var(--accent-color));
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(241, 143, 1, 0.3);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-guitar me-2"></i>GhanaTunes - Artisan Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-box me-1"></i>My Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="fas fa-store me-1"></i>Shop
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($customer_name); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../actions/logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container dashboard-container">
        <!-- Alert Container -->
        <div id="alertContainer"></div>

        <!-- Verification Status Alert - Shows all statuses with dismiss buttons -->
        <?php if ($verification_status === 'pending'): ?>
        <div class="alert alert-warning verification-alert alert-dismissible fade show" role="alert">
            <i class="fas fa-clock me-2"></i>
            <strong>Verification Pending:</strong> Your artisan account is awaiting verification by our admin team. 
            You can view your dashboard but cannot add products until verified.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        
        <?php elseif ($verification_status === 'rejected'): ?>
        <div class="alert alert-danger verification-alert alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle me-2"></i>
            <strong>Verification Rejected:</strong> Your artisan application was not approved. 
            Please contact support at <strong>support@ghanatunes.com</strong> for more information.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        
        <?php elseif ($verification_status === 'verified'): ?>
        <div class="alert alert-success verification-alert alert-dismissible fade show" role="alert" id="verifiedAlert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Verified Artisan:</strong> Your account is verified! You can now add and manage products.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h2>
                            <i class="fas fa-hammer me-2" style="color: var(--accent-color);"></i>
                            Welcome, <?php echo htmlspecialchars($customer_name); ?>!
                        </h2>
                        <p class="mb-0"><?php echo $verification_badge; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Row -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="icon" style="color: var(--accent-color);">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="number"><?php echo $stats['total_products'] ?? 0; ?></div>
                    <div class="label">Total Products</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="icon" style="color: var(--success-color);">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="number"><?php echo $stats['total_items_sold'] ?? 0; ?></div>
                    <div class="label">Items Sold</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="icon" style="color: var(--primary-color);">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="number">GHS <?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></div>
                    <div class="label">Total Revenue</div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row">
            <!-- Left Column - Profile -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user me-2"></i>Artisan Profile
                    </div>
                    <div class="card-body text-center">
                        <!-- FIXED: Profile Image Upload Structure -->
                        <div class="profile-image-container">
                            <?php
                            $profile_image = !empty($artisan_profile['profile_image']) 
                                ? '../../' . $artisan_profile['profile_image']
                                : 'https://ui-avatars.com/api/?name=' . urlencode($customer_name) . '&size=150&background=F18F01&color=fff';
                            ?>
                            <!-- Current Profile Image -->
                            <img src="<?php echo htmlspecialchars($profile_image); ?>" 
                                 alt="Profile" 
                                 class="profile-image"
                                 id="currentProfileImage">
                            
                            <!-- Preview Image (shown after selecting new image) -->
                            <img src="" 
                                 alt="Preview" 
                                 class="profile-image-preview"
                                 id="profileImagePreview">
                            
                            <!-- Camera Icon Overlay -->
                            <label for="profileImageInput" class="profile-image-overlay" title="Change profile picture">
                                <i class="fas fa-camera"></i>
                            </label>
                            
                            <!-- Hidden File Input -->
                            <input type="file" 
                                   id="profileImageInput" 
                                   accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                                   style="display: none;">
                        </div>

                        <!-- Upload Button (shown after selecting image) -->
                        <button type="button" 
                                id="uploadProfileImageBtn" 
                                class="btn btn-success btn-sm mb-3"
                                style="display: none;">
                            <i class="fas fa-upload me-2"></i>Upload New Image
                        </button>

                        <h5><?php echo htmlspecialchars($customer_name); ?></h5>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($artisan_profile['shop_name']); ?></p>
                        
                        <div class="text-start">
                            <p class="mb-2">
                                <strong><i class="fas fa-tools me-2"></i>Specialty:</strong><br>
                                <?php echo htmlspecialchars($artisan_profile['craft_specialty']); ?>
                            </p>
                            <p class="mb-2">
                                <strong><i class="fas fa-calendar me-2"></i>Experience:</strong><br>
                                <?php echo $artisan_profile['years_experience']; ?> years
                            </p>
                            <p class="mb-2">
                                <strong><i class="fas fa-map-marker-alt me-2"></i>Workshop:</strong><br>
                                <?php echo htmlspecialchars($artisan_profile['workshop_location']); ?>
                            </p>
                            <p class="mb-2">
                                <strong><i class="fas fa-star me-2"></i>Rating:</strong>
                                <?php echo number_format($artisan_profile['rating'] ?? 0, 2); ?>/5.00
                            </p>
                        </div>

                        <button class="btn btn-artisan w-100 mt-3" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="fas fa-edit me-2"></i>Edit Profile
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column - Recent Products & Bio -->
            <div class="col-lg-8">
                <!-- Bio Section -->
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="fas fa-info-circle me-2"></i>About My Craft
                    </div>
                    <div class="card-body">
                        <p><?php echo nl2br(htmlspecialchars($artisan_profile['bio'])); ?></p>
                    </div>
                </div>

                <!-- Recent Products -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-box me-2"></i>Recent Products</span>
                        <a href="products.php" class="btn btn-sm btn-light">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_products)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>No products yet. Add your first product to get started!</p>
                                <?php if ($verification_status === 'verified'): ?>
                                <a href="products.php" class="btn btn-artisan">
                                    <i class="fas fa-plus me-2"></i>Add Product
                                </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Category</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_products as $product): ?>
                                        <tr>
                                            <td>
                                                <?php
                                                $product_image = !empty($product['product_image'])
                                                    ? '../../' . $product['product_image']
                                                    : 'https://placehold.co/60x60/E3F2FD/2E86AB?text=No+Image';
                                                ?>
                                                <img src="<?php echo htmlspecialchars($product_image); ?>" 
                                                     alt="Product" 
                                                     class="product-image">
                                            </td>
                                            <td><?php echo htmlspecialchars($product['product_title']); ?></td>
                                            <td><strong>GHS <?php echo number_format($product['product_price'], 2); ?></strong></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars($product['cat_name'] ?? 'No Category'); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--accent-color), #C77700); color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="updateProfileForm">
                        <input type="hidden" name="artisan_id" value="<?php echo $artisan_id; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_shop_name" class="form-label">Shop Name</label>
                                <input type="text" class="form-control" id="edit_shop_name" name="shop_name" 
                                       value="<?php echo htmlspecialchars($artisan_profile['shop_name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="edit_craft_specialty" class="form-label">Craft Specialty</label>
                                <select class="form-select" id="edit_craft_specialty" name="craft_specialty" required>
                                    <option value="Traditional Drums" <?php echo $artisan_profile['craft_specialty'] === 'Traditional Drums' ? 'selected' : ''; ?>>Traditional Drums</option>
                                    <option value="String Instruments" <?php echo $artisan_profile['craft_specialty'] === 'String Instruments' ? 'selected' : ''; ?>>String Instruments</option>
                                    <option value="Percussion" <?php echo $artisan_profile['craft_specialty'] === 'Percussion' ? 'selected' : ''; ?>>Percussion</option>
                                    <option value="Wind Instruments" <?php echo $artisan_profile['craft_specialty'] === 'Wind Instruments' ? 'selected' : ''; ?>>Wind Instruments</option>
                                    <option value="Modern Instruments" <?php echo $artisan_profile['craft_specialty'] === 'Modern Instruments' ? 'selected' : ''; ?>>Modern Instruments</option>
                                    <option value="Instrument Repair" <?php echo $artisan_profile['craft_specialty'] === 'Instrument Repair' ? 'selected' : ''; ?>>Instrument Repair</option>
                                    <option value="Multiple" <?php echo $artisan_profile['craft_specialty'] === 'Multiple' ? 'selected' : ''; ?>>Multiple Specialties</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="edit_years_experience" class="form-label">Years of Experience</label>
                                <input type="number" class="form-control" id="edit_years_experience" name="years_experience" 
                                       value="<?php echo $artisan_profile['years_experience']; ?>" min="0" max="100" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="edit_workshop_location" class="form-label">Workshop Location</label>
                                <input type="text" class="form-control" id="edit_workshop_location" name="workshop_location" 
                                       value="<?php echo htmlspecialchars($artisan_profile['workshop_location']); ?>" required>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="edit_bio" class="form-label">Bio</label>
                                <textarea class="form-control" id="edit_bio" name="bio" rows="4" required><?php echo htmlspecialchars($artisan_profile['bio']); ?></textarea>
                                <div id="bioCounter"></div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-artisan w-100">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/artisan_profile.js"></script>
</body>
</html>