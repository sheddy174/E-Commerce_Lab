<?php
/**
 * Customer My Orders Page
 * View order history and track deliveries
 * CORRECTED VERSION - Fixed field names (total_amount, order_delivery_status)
 */

session_start();

require_once '../settings/core.php';
require_once '../controllers/order_controller.php';

// Check if user is logged in
require_login();

$customer_id = get_user_id();
$customer_name = get_user_name();

// Get customer orders
$orders = get_customer_orders_ctr($customer_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - GhanaTunes</title>
    
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
            background: linear-gradient(135deg, var(--primary-color), #1a5f7a);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .content-container {
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
        
        .order-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 1rem;
        }
        
        .order-id {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
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
        
        .order-info {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        
        .info-item {
            margin-bottom: 0.5rem;
        }
        
        .info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.875rem;
        }
        
        .info-value {
            color: #212529;
            font-size: 1rem;
        }
        
        .tracking-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }
        
        .timeline {
            position: relative;
            padding-left: 2rem;
            margin-top: 1rem;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }
        
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -2rem;
            top: 0.5rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: var(--primary-color);
        }
        
        .timeline-item.active::before {
            background: var(--success-color);
            box-shadow: 0 0 0 4px rgba(25, 135, 84, 0.2);
        }
        
        .timeline-item::after {
            content: '';
            position: absolute;
            left: -1.5rem;
            top: 1.5rem;
            width: 2px;
            height: calc(100% - 0.5rem);
            background: #dee2e6;
        }
        
        .timeline-item:last-child::after {
            display: none;
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
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
        
        .empty-state i {
            font-size: 5rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-guitar me-2"></i>GhanaTunes
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart me-1"></i>Cart
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="my_orders.php">
                            <i class="fas fa-box me-1"></i>My Orders
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

    <div class="container content-container">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2>
                    <i class="fas fa-box me-2"></i>My Orders
                </h2>
                <p class="text-muted">Track your order history and deliveries</p>
            </div>
        </div>

        <!-- Orders List -->
        <?php if (empty($orders)): ?>
            <div class="card">
                <div class="card-body">
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h4>No Orders Yet</h4>
                        <p class="text-muted">You haven't placed any orders yet.</p>
                        <a href="../index.php" class="btn btn-primary mt-3">
                            <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <!-- Order Header -->
                    <div class="order-header">
                        <div>
                            <div class="order-id">#<?php echo $order['order_id']; ?></div>
                            <small class="text-muted">
                                Ordered on <?php echo date('M d, Y', strtotime($order['order_date'])); ?>
                            </small>
                        </div>
                        <div>
                            <span class="status-badge status-<?php echo $order['order_delivery_status']; ?>">
                                <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                <?php echo ucfirst($order['order_delivery_status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Order Info -->
                    <div class="order-info">
                        <div class="info-item">
                            <div class="info-label">Order Amount</div>
                            <div class="info-value">
                                <strong style="color: var(--success-color); font-size: 1.25rem;">
                                    GHS <?php echo number_format($order['total_amount'], 2); ?>
                                </strong>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Payment Status</div>
                            <div class="info-value">
                                <?php echo $order['payment_status'] === 'completed' ? 
                                    '<span class="badge bg-success">Paid</span>' : 
                                    '<span class="badge bg-warning">Pending</span>'; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tracking Information -->
                    <?php if ($order['tracking_number']): ?>
                    <div class="tracking-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-shipping-fast me-2 text-primary"></i>
                                <strong>Tracking Number:</strong> <?php echo htmlspecialchars($order['tracking_number']); ?>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="copyTracking('<?php echo htmlspecialchars($order['tracking_number']); ?>')">
                                <i class="fas fa-copy me-1"></i>Copy
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Delivery Timeline -->
                    <div class="timeline">
                        <div class="timeline-item <?php echo in_array($order['order_delivery_status'], ['pending', 'processing', 'shipped', 'delivered']) ? 'active' : ''; ?>">
                            <strong>Order Placed</strong>
                            <br>
                            <small class="text-muted">
                                <?php echo date('M d, Y g:i A', strtotime($order['order_date'])); ?>
                            </small>
                        </div>
                        
                        <div class="timeline-item <?php echo in_array($order['order_delivery_status'], ['processing', 'shipped', 'delivered']) ? 'active' : ''; ?>">
                            <strong>Processing</strong>
                            <br>
                            <small class="text-muted">
                                <?php echo $order['order_delivery_status'] === 'pending' ? 'Awaiting processing' : 'Order is being prepared'; ?>
                            </small>
                        </div>
                        
                        <div class="timeline-item <?php echo in_array($order['order_delivery_status'], ['shipped', 'delivered']) ? 'active' : ''; ?>">
                            <strong>Shipped</strong>
                            <br>
                            <small class="text-muted">
                                <?php 
                                if ($order['shipped_date']) {
                                    echo date('M d, Y g:i A', strtotime($order['shipped_date']));
                                } else {
                                    echo 'Not yet shipped';
                                }
                                ?>
                            </small>
                        </div>
                        
                        <div class="timeline-item <?php echo $order['order_delivery_status'] === 'delivered' ? 'active' : ''; ?>">
                            <strong>Delivered</strong>
                            <br>
                            <small class="text-muted">
                                <?php 
                                if ($order['delivered_date']) {
                                    echo date('M d, Y g:i A', strtotime($order['delivered_date']));
                                } else {
                                    echo 'Not yet delivered';
                                }
                                ?>
                            </small>
                        </div>
                    </div>
                    
                    <!-- Delivery Notes -->
                    <?php if ($order['delivery_notes']): ?>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> <?php echo htmlspecialchars($order['delivery_notes']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Action Button -->
                    <div class="mt-3">
                        <button class="btn btn-primary btn-sm view-details-btn" 
                                data-order-id="<?php echo $order['order_id']; ?>">
                            <i class="fas fa-eye me-1"></i>View Full Details
                        </button>
                        
                        <?php if ($order['order_delivery_status'] === 'delivered'): ?>
                        <a href="write_review.php?order_id=<?php echo $order['order_id']; ?>" 
                           class="btn btn-success btn-sm">
                            <i class="fas fa-star me-1"></i>Write Review
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
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
    <script>
        // Copy tracking number to clipboard
        function copyTracking(trackingNumber) {
            navigator.clipboard.writeText(trackingNumber).then(() => {
                alert('Tracking number copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        }
        
        // View order details
        $(document).on('click', '.view-details-btn', function() {
            const orderId = $(this).data('order-id');
            
            $('#orderDetailsModal').modal('show');
            $('#orderDetailsContent').html(`
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p class="mt-3">Loading order details...</p>
                </div>
            `);
            
            // Fetch order details (reuse the same action handler)
            $.ajax({
                url: '../actions/get_order_details_action.php',
                type: 'GET',
                data: { order_id: orderId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        displayOrderDetails(response.data);
                    } else {
                        $('#orderDetailsContent').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                ${response.message}
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#orderDetailsContent').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error loading order details.
                        </div>
                    `);
                }
            });
        });
        
        function displayOrderDetails(order) {
            // Customer-focused order details display
            const statusClass = 'status-' + order.order_delivery_status;
            const orderDate = new Date(order.order_date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Order Information</h6>
                        <p><strong>Order ID:</strong> #${order.order_id}</p>
                        <p><strong>Date:</strong> ${orderDate}</p>
                        <p><strong>Amount:</strong> <span class="text-success fw-bold">GHS ${parseFloat(order.total_amount).toFixed(2)}</span></p>
                        <p><strong>Status:</strong> <span class="status-badge ${statusClass}">${order.order_delivery_status.charAt(0).toUpperCase() + order.order_delivery_status.slice(1)}</span></p>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Payment Information</h6>
                        <p><strong>Invoice:</strong> ${escapeHtml(order.invoice_no)}</p>
                        <p><strong>Payment Status:</strong> <span class="badge bg-success">Paid</span></p>
                        <p><strong>Payment Method:</strong> ${escapeHtml(order.payment_method || 'Paystack')}</p>
                    </div>
                </div>
            `;
            
            if (order.tracking_number) {
                html += `
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3">Tracking Information</h6>
                            <p><strong>Tracking Number:</strong> ${escapeHtml(order.tracking_number)}</p>
                        </div>
                    </div>
                `;
            }
            
            if (order.shipped_date || order.delivered_date) {
                html += `
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3">Delivery Timeline</h6>
                `;
                
                if (order.shipped_date) {
                    const shippedDate = new Date(order.shipped_date).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    html += `<p><strong>Shipped:</strong> ${shippedDate}</p>`;
                }
                
                if (order.delivered_date) {
                    const deliveredDate = new Date(order.delivered_date).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    html += `<p><strong>Delivered:</strong> ${deliveredDate}</p>`;
                }
                
                html += `
                        </div>
                    </div>
                `;
            }
            
            if (order.delivery_notes) {
                html += `
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3">Delivery Notes</h6>
                            <p>${escapeHtml(order.delivery_notes)}</p>
                        </div>
                    </div>
                `;
            }
            
            $('#orderDetailsContent').html(html);
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.toString().replace(/[&<>"']/g, m => map[m]);
        }
    </script>
</body>
</html>