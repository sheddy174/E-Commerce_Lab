/**
 * Artisan Verification JavaScript
 * Handles verification actions for admin
 * DataTables initialization (now in PHP file)
 */

$(document).ready(function() {
    
    // Verify Artisan
    $(document).on('click', '.verify-btn', function() {
        const artisanId = $(this).data('id');
        const artisanName = $(this).data('name');
        
        Swal.fire({
            title: 'Verify Artisan?',
            html: `Are you sure you want to <strong>approve</strong> ${artisanName}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Approve',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                updateVerificationStatus(artisanId, 'verified', artisanName);
            }
        });
    });
    
    // Reject Artisan
    $(document).on('click', '.reject-btn', function() {
        const artisanId = $(this).data('id');
        const artisanName = $(this).data('name');
        
        Swal.fire({
            title: 'Reject Application?',
            html: `Are you sure you want to <strong>reject</strong> ${artisanName}'s application?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Reject',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                updateVerificationStatus(artisanId, 'rejected', artisanName);
            }
        });
    });
    
    // View Details
    $(document).on('click', '.view-btn', function() {
        const artisanId = $(this).data('id');
        loadArtisanDetails(artisanId);
        $('#viewDetailsModal').modal('show');
    });
    
    /**
     * Update verification status
     */
    function updateVerificationStatus(artisanId, status, artisanName) {
        $.ajax({
            url: '../actions/update_artisan_verification_action.php',
            type: 'POST',
            data: {
                artisan_id: artisanId,
                status: status
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonColor: '#2E86AB'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                showAlert('error', 'Error updating verification status');
            }
        });
    }
    
    /**
     * Load artisan details
     */
    function loadArtisanDetails(artisanId) {
        $('#artisanDetailsContent').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        
        $.ajax({
            url: '../actions/get_artisan_details_action.php',
            type: 'GET',
            data: { artisan_id: artisanId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const artisan = response.data;
                    displayArtisanDetails(artisan);
                } else {
                    $('#artisanDetailsContent').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${response.message}
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                $('#artisanDetailsContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error loading artisan details
                    </div>
                `);
            }
        });
    }
    
    /**
     * Display artisan details
     */
    function displayArtisanDetails(artisan) {
        const html = `
            <div class="row">
                <div class="col-md-4 text-center">
                    <img src="${artisan.profile_image || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(artisan.customer_name) + '&size=200'}" 
                         class="img-fluid rounded-circle mb-3" 
                         style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #2E86AB;">
                    <h5>${artisan.customer_name}</h5>
                    <p class="text-muted">${artisan.customer_email}</p>
                </div>
                <div class="col-md-8">
                    <h6 class="mb-3"><i class="fas fa-store me-2"></i>Business Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <th>Shop Name:</th>
                            <td>${artisan.shop_name}</td>
                        </tr>
                        <tr>
                            <th>Specialty:</th>
                            <td><span class="badge bg-info">${artisan.craft_specialty}</span></td>
                        </tr>
                        <tr>
                            <th>Experience:</th>
                            <td>${artisan.years_experience} years</td>
                        </tr>
                        <tr>
                            <th>Workshop:</th>
                            <td>${artisan.workshop_location}</td>
                        </tr>
                        <tr>
                            <th>Contact:</th>
                            <td>${artisan.customer_contact}</td>
                        </tr>
                        <tr>
                            <th>Location:</th>
                            <td>${artisan.customer_city}, ${artisan.customer_country}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>${getStatusBadge(artisan.verification_status)}</td>
                        </tr>
                    </table>
                    
                    <h6 class="mt-3"><i class="fas fa-info-circle me-2"></i>Bio</h6>
                    <p>${artisan.bio || 'No bio provided'}</p>
                </div>
            </div>
        `;
        
        $('#artisanDetailsContent').html(html);
    }
    
    /**
     * Get status badge
     */
    function getStatusBadge(status) {
        switch (status) {
            case 'verified':
                return '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Verified</span>';
            case 'pending':
                return '<span class="badge bg-warning"><i class="fas fa-clock me-1"></i>Pending</span>';
            case 'rejected':
                return '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Rejected</span>';
            default:
                return '<span class="badge bg-secondary">Unknown</span>';
        }
    }
    
    /**
     * Show alert message
     */
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('#alertContainer').html(alertHtml);
        $('html, body').animate({ scrollTop: 0 }, 300);
        
        setTimeout(() => $('.alert').fadeOut(), 5000);
    }
});