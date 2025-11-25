<?php
/**
 * Admin Artisan Verification Page
 * Allows admins to approve/reject artisan applications
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once '../settings/core.php';
require_once '../controllers/artisan_controller.php';

// Check if user is logged in and is admin
require_admin();

// Get all artisans grouped by status
$pending_artisans = get_pending_artisans_ctr();
$verified_artisans = get_verified_artisans_ctr();
$all_artisans = get_all_artisans_ctr();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Artisans - Admin Panel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
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
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), #1B5E7A);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), #1B5E7A);
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
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.15);
        }
        
        .stat-card .icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: 700;
        }
        
        .artisan-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .btn-verify {
            background: var(--success-color);
            color: white;
            border: none;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
        }
        
        .btn-verify:hover {
            background: #146c43;
            transform: scale(1.05);
            color: white;
        }
        
        .btn-reject {
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
        }
        
        .btn-reject:hover {
            background: #bb2d3b;
            transform: scale(1.05);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-guitar me-2"></i>GhanaTunes Admin
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="verify_artisans.php">
                            <i class="fas fa-user-check me-1"></i>Verify Artisans
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../actions/logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid" style="padding: 2rem;">
        <!-- Alert Container -->
        <div id="alertContainer"></div>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-check me-2"></i>Artisan Verification</h2>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="icon" style="color: var(--warning-color);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="number" style="color: var(--warning-color);">
                        <?php echo count($pending_artisans ?? []); ?>
                    </div>
                    <div class="text-muted">Pending Verification</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="icon" style="color: var(--success-color);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="number" style="color: var(--success-color);">
                        <?php echo count($verified_artisans ?? []); ?>
                    </div>
                    <div class="text-muted">Verified Artisans</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="icon" style="color: var(--primary-color);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="number" style="color: var(--primary-color);">
                        <?php echo count($all_artisans ?? []); ?>
                    </div>
                    <div class="text-muted">Total Artisans</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="icon" style="color: var(--accent-color);">
                        <i class="fas fa-hammer"></i>
                    </div>
                    <div class="number" style="color: var(--accent-color);">
                        <?php
                        $specialties = array_unique(array_column($all_artisans ?? [], 'craft_specialty'));
                        echo count($specialties);
                        ?>
                    </div>
                    <div class="text-muted">Craft Specialties</div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="artisanTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button">
                    <i class="fas fa-clock me-2"></i>Pending (<?php echo count($pending_artisans ?? []); ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="verified-tab" data-bs-toggle="tab" data-bs-target="#verified" type="button">
                    <i class="fas fa-check-circle me-2"></i>Verified (<?php echo count($verified_artisans ?? []); ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button">
                    <i class="fas fa-users me-2"></i>All Artisans (<?php echo count($all_artisans ?? []); ?>)
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="artisanTabContent">
            <!-- Pending Artisans -->
            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-clock me-2"></i>Pending Artisan Applications
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="pendingTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Shop Name</th>
                                        <th>Specialty</th>
                                        <th>Experience</th>
                                        <th>Location</th>
                                        <th>Contact</th>
                                        <th>Applied</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($pending_artisans)): ?>
                                        <?php foreach ($pending_artisans as $artisan): ?>
                                        <tr>
                                            <td><?php echo $artisan['artisan_id']; ?></td>
                                            <td><?php echo htmlspecialchars($artisan['customer_name']); ?></td>
                                            <td><strong><?php echo htmlspecialchars($artisan['shop_name']); ?></strong></td>
                                            <td><span class="badge bg-info"><?php echo htmlspecialchars($artisan['craft_specialty']); ?></span></td>
                                            <td><?php echo $artisan['years_experience']; ?> years</td>
                                            <td><?php echo htmlspecialchars($artisan['workshop_location']); ?></td>
                                            <td><?php echo htmlspecialchars($artisan['customer_contact']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($artisan['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-verify verify-btn" 
                                                        data-id="<?php echo $artisan['artisan_id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($artisan['customer_name']); ?>"
                                                        title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-reject reject-btn" 
                                                        data-id="<?php echo $artisan['artisan_id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($artisan['customer_name']); ?>"
                                                        title="Reject">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary view-btn" 
                                                        data-id="<?php echo $artisan['artisan_id']; ?>"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                                No pending applications
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verified Artisans -->
            <div class="tab-pane fade" id="verified" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-check-circle me-2"></i>Verified Artisans
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="verifiedTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Shop Name</th>
                                        <th>Specialty</th>
                                        <th>Rating</th>
                                        <th>Products</th>
                                        <th>Verified On</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($verified_artisans)): ?>
                                        <?php foreach ($verified_artisans as $artisan): ?>
                                        <tr>
                                            <td><?php echo $artisan['artisan_id']; ?></td>
                                            <td><?php echo htmlspecialchars($artisan['customer_name']); ?></td>
                                            <td><strong><?php echo htmlspecialchars($artisan['shop_name']); ?></strong></td>
                                            <td><span class="badge bg-info"><?php echo htmlspecialchars($artisan['craft_specialty']); ?></span></td>
                                            <td>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-star"></i> <?php echo number_format($artisan['rating'] ?? 0, 2); ?>
                                                </span>
                                            </td>
                                            <td><?php echo count_artisan_products_ctr($artisan['artisan_id']); ?></td>
                                            <td><?php echo $artisan['verification_date'] ? date('M d, Y', strtotime($artisan['verification_date'])) : 'N/A'; ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary view-btn" 
                                                        data-id="<?php echo $artisan['artisan_id']; ?>"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                                No verified artisans yet
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Artisans -->
            <div class="tab-pane fade" id="all" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-users me-2"></i>All Artisans
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="allTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Shop Name</th>
                                        <th>Status</th>
                                        <th>Specialty</th>
                                        <th>Experience</th>
                                        <th>Products</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($all_artisans)): ?>
                                        <?php foreach ($all_artisans as $artisan): ?>
                                        <tr>
                                            <td><?php echo $artisan['artisan_id']; ?></td>
                                            <td><?php echo htmlspecialchars($artisan['customer_name']); ?></td>
                                            <td><strong><?php echo htmlspecialchars($artisan['shop_name']); ?></strong></td>
                                            <td>
                                                <?php
                                                switch ($artisan['verification_status']) {
                                                    case 'verified':
                                                        echo '<span class="badge bg-success">Verified</span>';
                                                        break;
                                                    case 'pending':
                                                        echo '<span class="badge bg-warning">Pending</span>';
                                                        break;
                                                    case 'rejected':
                                                        echo '<span class="badge bg-danger">Rejected</span>';
                                                        break;
                                                }
                                                ?>
                                            </td>
                                            <td><span class="badge bg-info"><?php echo htmlspecialchars($artisan['craft_specialty']); ?></span></td>
                                            <td><?php echo $artisan['years_experience']; ?> years</td>
                                            <td><?php echo count_artisan_products_ctr($artisan['artisan_id']); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary view-btn" 
                                                        data-id="<?php echo $artisan['artisan_id']; ?>"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                                No artisans found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="viewDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color), #1B5E7A); color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-user me-2"></i>Artisan Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="artisanDetailsContent">
                    <!-- Content loaded via AJAX -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/verify_artisans.js"></script>
</body>
</html>