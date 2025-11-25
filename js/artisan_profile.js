/**
 * Artisan Profile Management JavaScript
 * Handles profile image uploads and profile updates
 */

$(document).ready(function() {
    
    // ==========================================
    // PROFILE IMAGE UPLOAD
    // ==========================================
    
    /**
     * Preview profile image before upload
     */
    $('#profileImageInput').change(function() {
        const file = this.files[0];
        
        if (file) {
            // Validate file
            const validation = validateImageFile(file);
            if (!validation.isValid) {
                showAlert('error', validation.errors.join('. '));
                $(this).val(''); // Clear the input
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#profileImagePreview').attr('src', e.target.result);
                $('#uploadProfileImageBtn').prop('disabled', false);
            };
            reader.readAsDataURL(file);
        }
    });
    
    /**
     * Upload profile image
     */
    $('#uploadProfileImageBtn').click(function() {
        const fileInput = $('#profileImageInput')[0];
        const file = fileInput.files[0];
        
        if (!file) {
            showAlert('error', 'Please select an image first');
            return;
        }
        
        const formData = new FormData();
        formData.append('image', file);
        formData.append('image_type', 'profile');
        
        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Uploading...');
        
        $.ajax({
            url: '../actions/upload_artisan_image_action.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                btn.prop('disabled', false).html(originalText);
                
                if (response.status === 'success') {
                    showAlert('success', response.message);
                    
                    // Update the displayed image
                    $('#currentProfileImage').attr('src', '../../' + response.image_path);
                    
                    // Clear the file input
                    fileInput.value = '';
                    $('#uploadProfileImageBtn').prop('disabled', true);
                    
                    // Optionally reload page after delay
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr, status, error) {
                btn.prop('disabled', false).html(originalText);
                console.error('Upload error:', error);
                showAlert('error', 'Error uploading image. Please try again.');
            }
        });
    });
    
    // ==========================================
    // PROFILE UPDATE FORM
    // ==========================================
    
    /**
     * Update artisan profile information
     */
    $('#updateProfileForm').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');
        
        $.ajax({
            url: '../actions/update_artisan_profile_action.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);
                
                if (response.status === 'success') {
                    showAlert('success', response.message);
                    
                    // Optionally reload page
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr, status, error) {
                submitBtn.prop('disabled', false).html(originalText);
                console.error('Update error:', error);
                showAlert('error', 'Error updating profile. Please try again.');
            }
        });
    });
    
    // ==========================================
    // HELPER FUNCTIONS
    // ==========================================
    
    /**
     * Validate image file
     */
    function validateImageFile(file) {
        const errors = [];
        
        // Check file size (5MB max)
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            errors.push('Image file is too large. Maximum size is 5MB');
        }
        
        // Check file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type.toLowerCase())) {
            errors.push('Invalid file type. Allowed: JPG, PNG, GIF, WEBP');
        }
        
        return {
            isValid: errors.length === 0,
            errors: errors
        };
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
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => $('.alert').fadeOut(), 5000);
    }
    
    /**
     * Character counter for bio textarea
     */
    $('#bio').on('input', function() {
        const length = $(this).val().length;
        const minLength = 50;
        
        let counterHtml = '';
        if (length < minLength) {
            counterHtml = `<small class="text-danger">${length}/${minLength} characters (minimum ${minLength})</small>`;
        } else {
            counterHtml = `<small class="text-success">${length} characters ✓</small>`;
        }
        
        $('#bioCounter').html(counterHtml);
    });
});