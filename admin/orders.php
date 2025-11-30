<?php
/**
 * Admin Order Management
 * Manage all orders and delivery tracking
 * CORRECTED VERSION - Fixed DataTables initialization
 */

session_start();

require_once '../settings/core.php';
require_once '../controllers/order_controller.php';

// Check if user is logged in and is admin
require_admin();

$admin_name = get_user_name();

// Get order statistics
$stats = get_order_stats_ctr();

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : null;
$search_term = isset($_GET['search']) ? trim($_GET['search']) : null;

// Get all orders with filters
$orders = get_all_orders_ctr($status_filter, $search_term);

// Debug logging
error_log("Orders page loaded - Stats: " . json_encode($stats));
error_log("Orders count: " . (is_array($orders) ? count($orders) : 'false'));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - GhanaTunes Admin</title>

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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), #1a5f7a);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .navbar-brand {
            font-weight: 700;
            color: white !important;
        }

        .content-container {
            padding: 2rem 0;
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), #1a5f7a);
            color: white;
            border: none;
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-radius: 1rem 1rem 0 0;
        }

        .stat-card {
            text-align: center;
            padding: 1.5rem;
            border-radius: 1rem;
            background: white;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
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
            color: var(--primary-color);
        }

        .stat-card .label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            white-space: nowrap;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #cfe2ff;
            color: #084298;
        }

        .status-shipped {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-delivered {
            background: #d1e7dd;
            color: #0a3622;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #842029;
        }

        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.05);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #1a5f7a);
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1a5f7a, var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(46, 134, 171, 0.3);
        }

        .action-buttons .btn {
            margin: 0.2rem;
        }

        /* DataTables custom styling */
        table.dataTable tbody tr {
            cursor: pointer;
        }

        table.dataTable tbody tr:hover {
            background-color: rgba(46, 134, 171, 0.05);
        }

        .dataTables_wrapper .dataTables_length select {
            padding: 0.375rem 2rem 0.375rem 0.75rem;
            border-radius: 0.375rem;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="orders.php">
                            <i class="fas fa-shopping-cart me-1"></i>Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="product.php">
                            <i class="fas fa-box me-1"></i>Products
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($admin_name); ?>
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

    <div class="container content-container">
        <!-- Alert Container -->
        <div id="alertContainer"></div>

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2>
                    <i class="fas fa-shopping-cart me-2"></i>Order Management
                </h2>
                <p class="text-muted">Manage orders and track deliveries</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="icon" style="color: #6c757d;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="number"><?php echo $stats['total_orders'] ?? 0; ?></div>
                    <div class="label">Total Orders</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="icon" style="color: var(--warning-color);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="number"><?php echo $stats['pending_orders'] ?? 0; ?></div>
                    <div class="label">Pending</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="icon" style="color: var(--primary-color);">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="number"><?php echo $stats['shipped_orders'] ?? 0; ?></div>
                    <div class="label">Shipped</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="icon" style="color: var(--success-color);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="number"><?php echo $stats['delivered_orders'] ?? 0; ?></div>
                    <div class="label">Delivered</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <form method="GET" action="orders.php" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Filter by Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Search Customer</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..."
                        value="<?php echo htmlspecialchars($search_term ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list me-2"></i>Orders List
            </div>
            <div class="card-body">
                <?php if ($orders === false): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error loading orders. Please check your database connection.
                    </div>
                <?php elseif (empty($orders)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No orders found</h5>
                        <p class="text-muted">Orders will appear here once customers make purchases.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table id="ordersTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Tracking</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><strong>#<?php echo $order['order_id']; ?></strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                        <td>
                                            <strong>
                                                <?php
                                                // Use payment_amount from the query
                                                $amount = $order['payment_amount'] ?? 0;
                                                echo 'GHS ' . number_format($amount, 2);
                                                ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $order['order_delivery_status'] ?? 'pending'; ?>">
                                                <?php echo ucfirst($order['order_delivery_status'] ?? 'pending'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($order['tracking_number'])): ?>
                                                <small><i class="fas fa-shipping-fast me-1"></i><?php echo htmlspecialchars($order['tracking_number']); ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">N/A</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="action-buttons">
                                            <button class="btn btn-sm btn-primary view-order-btn"
                                                data-order-id="<?php echo $order['order_id']; ?>"
                                                title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-success update-status-btn"
                                                data-order-id="<?php echo $order['order_id']; ?>"
                                                data-current-status="<?php echo $order['order_delivery_status'] ?? 'pending'; ?>"
                                                title="Update Status">
                                                <i class="fas fa-edit"></i>
                                            </button>
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

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color), #1a5f7a); color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Update Order Status
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="updateStatusForm">
                        <input type="hidden" id="update_order_id" name="order_id">

                        <div class="mb-3">
                            <label class="form-label">Order Status</label>
                            <select class="form-select" id="update_status" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div class="mb-3" id="trackingNumberField" style="display: none;">
                            <label class="form-label">Tracking Number (Optional)</label>
                            <input type="text" class="form-control" id="update_tracking" name="tracking_number" placeholder="Enter tracking number">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Delivery Notes (Optional)</label>
                            <textarea class="form-control" id="update_notes" name="notes" rows="3" placeholder="Add any notes about this delivery..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i>Update Status
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color), #1a5f7a); color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i>Order Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-3x"></i>
                        <p class="mt-3">Loading order details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="../js/orders_management.js"></script>

    <script>
        // Initialize DataTables only if table has data
        $(document).ready(function() {
            <?php if (!empty($orders)): ?>
            $('#ordersTable').DataTable({
                "order": [[0, "desc"]], // Sort by Order ID descending
                "pageLength": 25,
                "language": {
                    "search": "Search orders:",
                    "lengthMenu": "Show _MENU_ orders per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ orders",
                    "infoEmpty": "No orders available",
                    "infoFiltered": "(filtered from _MAX_ total orders)"
                }
            });
            <?php endif; ?>
        });
    </script>
</body>

</html>