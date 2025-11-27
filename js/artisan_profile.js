/**
 * Artisan Profile Management JavaScript
 * FIXED: Modeled after working product.js pattern
 */

$(document).ready(function() {
    console.log('Artisan profile JS loaded');
    
    // ==========================================
    // PROFILE IMAGE UPLOAD - FIXED PATTERN
    // ==========================================
    
    /**
     * Handle file selection - show preview
     */
    $('#profileImageInput').change(function() {
        console.log('File input changed');
        const file = this.files[0];
        
        if (file) {
            console.log('File selected:', file.name, file.size, file.type);
            
            // Validate file
            const validation = validateImageFile(file);
            if (!validation.isValid) {
                showAlert('error', validation.errors.join('. '));
                $(this).val(''); // Clear the input
                return;
            }
            
            // Show preview using FileReader
            const reader = new FileReader();
            reader.onload = function(e) {
                console.log('File loaded, showing preview');
                // Hide current image, show preview
                $('#currentProfileImage').hide();
                $('#profileImagePreview').attr('src', e.target.result).show();
                // Show upload button
                $('#uploadProfileImageBtn').show();
            };
            reader.readAsDataURL(file);
        }
    });
    
    /**
     * Handle upload button click
     */
    $('#uploadProfileImageBtn').click(function() {
        console.log('Upload button clicked');
        const fileInput = $('#profileImageInput')[0];
        const file = fileInput.files[0];
        
        if (!file) {
            showAlert('error', 'Please select an image first');
            return;
        }
        
        // Create FormData
        const formData = new FormData();
        formData.append('profile_image', file);
        
        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Uploading...');
        
        $.ajax({
            url: '../actions/upload_artisan_profile_image_action.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            dataType: 'json',
            success: function(response) {
                console.log('Upload response:', response);
                btn.prop('disabled', false).html(originalText);
                
                if (response.status === 'success') {
                    showAlert('success', response.message);
                    
                    // Update both images
                    const imagePath = '../../' + response.image_path;
                    $('#currentProfileImage').attr('src', imagePath).show();
                    $('#profileImagePreview').attr('src', imagePath).hide();
                    $('#uploadProfileImageBtn').hide();
                    
                    // Clear the file input
                    fileInput.value = '';
                    
                    // Reload page after short delay
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('error', response.message || 'Failed to upload image');
                }
            },
            error: function(xhr, status, error) {
                console.error('Upload error:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                });
                btn.prop('disabled', false).html(originalText);
                
                let errorMessage = 'Error uploading image. Please try again.';
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse.message) {
                        errorMessage = errorResponse.message;
                    }
                } catch (e) {
                    if (xhr.status === 413) {
                        errorMessage = 'File too large. Maximum size is 5MB.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error. Check error logs for details.';
                    }
                }
                
                showAlert('error', errorMessage);
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
                    // Close modal
                    $('#editProfileModal').modal('hide');
                    // Reload page
                    setTimeout(() => location.reload(), 1500);
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
    $('#edit_bio').on('input', function() {
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