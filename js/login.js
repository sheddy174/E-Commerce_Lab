/**
 * Login JavaScript with AJAX Support
 * Handles client-side validation and AJAX form submission
 */

$(document).ready(function() {
    
    // Enhanced input interactions with visual feedback
    $('.form-control').each(function() {
        const $input = $(this);
        const $label = $input.siblings('.form-label');
        
        // Add focus effects
        $input.on('focus', function() {
            $label.css('color', '#2E86AB');
            $(this).css('border-color', '#2E86AB');
        });
        
        $input.on('blur', function() {
            $label.css('color', '');
            if (!$(this).val()) {
                $(this).css('border-color', '');
            }
        });
        
        // Add typing effects
        $input.on('input', function() {
            if ($(this).val()) {
                $(this).addClass('has-content');
            } else {
                $(this).removeClass('has-content');
            }
        });
    });
    
    // Remember me functionality
    $('#rememberMe').change(function() {
        if ($(this).is(':checked')) {
            localStorage.setItem('rememberLogin', 'true');
            $(this).closest('.form-check').css('color', '#2E86AB');
        } else {
            localStorage.removeItem('rememberLogin');
            localStorage.removeItem('rememberedEmail');
            $(this).closest('.form-check').css('color', '');
        }
    });
    
    // Load remembered email if available
    if (localStorage.getItem('rememberLogin') === 'true') {
        const rememberedEmail = localStorage.getItem('rememberedEmail');
        if (rememberedEmail) {
            $('#email').val(rememberedEmail).addClass('has-content');
            $('#rememberMe').prop('checked', true);
            $('#rememberMe').closest('.form-check').css('color', '#2E86AB');
        }
    }
    
    // Save email when remember me is checked
    $('#email').on('blur', function() {
        if ($('#rememberMe').is(':checked')) {
            localStorage.setItem('rememberedEmail', $(this).val());
        }
    });
    
    // AJAX Form submission
    $('#login-form').on('submit', function(e) {
        e.preventDefault(); // Prevent traditional form submission
        
        const email = $('#email').val().trim();
        const password = $('#password').val();
        const rememberMe = $('#rememberMe').is(':checked');
        
        // Clear any previous error styling
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('.custom-alert').remove();
        
        let hasErrors = false;
        
        // Validate email
        if (!email) {
            showFieldError('#email', 'Email address is required');
            hasErrors = true;
        } else if (!isValidEmail(email)) {
            showFieldError('#email', 'Please enter a valid email address');
            hasErrors = true;
        }
        
        // Validate password
        if (!password) {
            showFieldError('#password', 'Password is required');
            hasErrors = true;
        }
        
        // If there are errors, stop submission
        if (hasErrors) {
            // Focus on first error field
            $('.is-invalid').first().focus();
            
            // Show general error message
            showAlert('error', 'Please correct the errors below and try again.');
            return false;
        }
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Signing In...');
        
        // Prepare form data
        const formData = {
            customer_email: email,
            customer_pass: password
        };
        
        // Submit via AJAX
        $.ajax({
            url: '../actions/login_customer_action.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Save email if remember me is checked
                    if (rememberMe) {
                        localStorage.setItem('rememberedEmail', email);
                        localStorage.setItem('rememberLogin', 'true');
                    }
                    
                    // Show success message
                    showAlert('success', response.message);
                    
                    // Redirect after short delay
                    setTimeout(function() {
                        window.location.href = response.redirect || '../index.php';
                    }, 1000);
                    
                } else {
                    // Show error message
                    submitBtn.prop('disabled', false).html(originalText);
                    showAlert('error', response.message);
                    
                    // Clear password field on failed login
                    $('#password').val('').focus();
                }
            },
            error: function(xhr, status, error) {
                submitBtn.prop('disabled', false).html(originalText);
                console.error('Login AJAX Error:', {xhr, status, error});
                console.error('Response Text:', xhr.responseText);
                
                // Try to parse error response
                let errorMessage = 'An error occurred. Please try again.';
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse.message) {
                        errorMessage = errorResponse.message;
                    }
                } catch (e) {
                    // If not JSON, use default message
                }
                
                showAlert('error', errorMessage);
            }
        });
        
        return false;
    });
    
    // Auto-focus on email field when page loads
    $('#email').focus();
    
    // Enter key handling for better UX
    $('#email').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            $('#password').focus();
        }
    });
    
    $('#password').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            $('#login-form').submit();
        }
    });
    
    // Show any error messages from URL parameters (for backward compatibility)
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    
    if (error) {
        let errorMessage = '';
        switch (error) {
            case 'missing_fields':
                errorMessage = 'Please fill in all required fields.';
                break;
            case 'invalid_email':
                errorMessage = 'Please enter a valid email address.';
                break;
            case 'system_error':
                errorMessage = 'A system error occurred. Please try again.';
                break;
            case 'Invalid email or password':
                errorMessage = 'Invalid email or password. Please check your credentials.';
                break;
            default:
                errorMessage = decodeURIComponent(error);
        }
        
        if (errorMessage) {
            showAlert('error', errorMessage);
            
            // Clear the error from URL
            const newUrl = window.location.origin + window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
    }
    
});

/**
 * Validate email format
 * @param {string} email 
 * @returns {boolean}
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Show field-specific error
 * @param {string} fieldSelector 
 * @param {string} message 
 */
function showFieldError(fieldSelector, message) {
    const $field = $(fieldSelector);
    $field.addClass('is-invalid');
    
    // Remove any existing error message for this field
    $field.siblings('.invalid-feedback').remove();
    
    // Add new error message
    $field.after(`<div class="invalid-feedback">${message}</div>`);
}

/**
 * Show general alert message
 * @param {string} type - 'success', 'error', 'warning', 'info'
 * @param {string} message 
 */
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 
                      type === 'warning' ? 'alert-warning' : 'alert-info';
    
    const icon = type === 'success' ? 'fa-check-circle' : 
                type === 'error' ? 'fa-exclamation-triangle' : 
                type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
    
    // Remove any existing alerts
    $('.custom-alert').remove();
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show custom-alert" role="alert" style="margin-bottom: 1rem;">
            <i class="fas ${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Insert alert at the top of the card body
    $('.card-body').prepend(alertHtml);
    
    // Auto-dismiss after 8 seconds (except for success messages)
    if (type !== 'success') {
        setTimeout(function() {
            $('.custom-alert').alert('close');
        }, 8000);
    }
    
    // Scroll to top to show alert
    $('html, body').animate({ scrollTop: 0 }, 300);
}

/**
 * Show success message
 * @param {string} message 
 */
function showSuccess(message) {
    showAlert('success', message);
}

/**
 * Clear all form errors
 */
function clearFormErrors() {
    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    $('.custom-alert').remove();
}

// Add CSS for enhanced form styling
$(document).ready(function() {
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .form-control.has-content {
                border-color: #2E86AB;
                background-color: #f8f9ff;
            }
            
            .form-control:focus {
                box-shadow: 0 0 0 0.2rem rgba(46, 134, 171, 0.25);
            }
            
            .form-control.is-invalid {
                border-color: #dc3545;
                box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
            }
            
            .invalid-feedback {
                display: block;
                color: #dc3545;
                font-size: 0.875rem;
                margin-top: 0.25rem;
            }
            
            .custom-alert {
                border-radius: 0.5rem;
                border: none;
                box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
                animation: slideInDown 0.3s ease-out;
            }
            
            @keyframes slideInDown {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .btn:disabled {
                cursor: not-allowed;
                opacity: 0.65;
            }
            
            .form-check-input:checked {
                background-color: #2E86AB;
                border-color: #2E86AB;
            }
            
            .form-check-input:focus {
                border-color: #2E86AB;
                box-shadow: 0 0 0 0.25rem rgba(46, 134, 171, 0.25);
            }
        `)
        .appendTo('head');
});