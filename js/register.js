/**
 * Handles form validation and AJAX submission
 */

$(document).ready(function() {
    // Form submission handler
    $('#register-form').submit(function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.text();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Registering...');
        
        // Get form data - FIXED: Using correct names that match database columns
        const formData = {
            customer_name: $('#full_name').val().trim(),      
            customer_email: $('#email').val().trim(),           
            customer_pass: $('#password').val(),
            customer_country: $('#country').val().trim(),       
            customer_city: $('#city').val().trim(),            
            customer_contact: $('#contact_number').val().trim() 
        };
        
        // Client-side validation
        const validationResult = validateForm(formData);
        if (!validationResult.isValid) {
            showError(validationResult.message);
            resetSubmitButton(submitBtn, originalText);
            return;
        }
        
        // Submit form via AJAX
        $.ajax({
            url: '../actions/register_customer_action.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                resetSubmitButton(submitBtn, originalText);
                
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registration Successful!',
                        text: response.message,
                        confirmButtonColor: '#2E86AB',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redirect to login page
                            window.location.href = 'login.php'; 
                        }
                    });
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr, status, error) {
                resetSubmitButton(submitBtn, originalText);
                console.error('AJAX Error:', {xhr, status, error});
                console.error('Response Text:', xhr.responseText);
                showError('An error occurred while processing your request. Please try again.');
            }
        });
    });
    
    // Real-time email validation
    $('#email').on('blur', function() {
        const email = $(this).val().trim();
        if (email && isValidEmail(email)) {
            checkEmailAvailability(email);
        }
    });
    
    // Real-time password strength indicator
    $('#password').on('input', function() {
        const password = $(this).val();
        updatePasswordStrength(password);
    });
});

/**
 * Validate form data on client side
 * FIXED: Updated to work with new field names
 */
function validateForm(data) {
    // Check for empty fields
    const fieldLabels = {
        customer_name: 'Full Name',
        customer_email: 'Email',
        customer_pass: 'Password',
        customer_country: 'Country',
        customer_city: 'City',
        customer_contact: 'Contact Number'
    };
    
    for (const [key, value] of Object.entries(data)) {
        if (!value) {
            return {
                isValid: false,
                message: `${fieldLabels[key]} is required`
            };
        }
    }
    
    // Validate full name (minimum 2 chars, letters and spaces only)
    if (data.customer_name.length < 2 || !/^[a-zA-Z\s]+$/.test(data.customer_name)) {
        return {
            isValid: false,
            message: 'Full name must be at least 2 characters and contain only letters and spaces'
        };
    }
    
    // Validate email format
    if (!isValidEmail(data.customer_email)) {
        return {
            isValid: false,
            message: 'Please enter a valid email address'
        };
    }
    
    // Validate password strength
    const passwordValidation = validatePassword(data.customer_pass);
    if (!passwordValidation.isValid) {
        return passwordValidation;
    }
    
    // Validate country (letters and spaces only)
    if (data.customer_country.length < 2 || !/^[a-zA-Z\s]+$/.test(data.customer_country)) {
        return {
            isValid: false,
            message: 'Country must be at least 2 characters and contain only letters and spaces'
        };
    }
    
    // Validate city (letters and spaces only)
    if (data.customer_city.length < 2 || !/^[a-zA-Z\s]+$/.test(data.customer_city)) {
        return {
            isValid: false,
            message: 'City must be at least 2 characters and contain only letters and spaces'
        };
    }
    
    // Validate contact number (10-15 digits, may include + for country code)
    if (!/^\+?[1-9]\d{9,14}$/.test(data.customer_contact)) {
        return {
            isValid: false,
            message: 'Contact number must be 10-15 digits and may include country code'
        };
    }
    
    return { isValid: true, message: '' };
}

/**
 * Validate email format using regex
 */
function isValidEmail(email) {
    const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
    return emailRegex.test(email);
}

/**
 * Validate password strength
 */
function validatePassword(password) {
    if (password.length < 8) {
        return {
            isValid: false,
            message: 'Password must be at least 8 characters long'
        };
    }
    
    if (!/(?=.*[a-z])/.test(password)) {
        return {
            isValid: false,
            message: 'Password must contain at least one lowercase letter'
        };
    }
    
    if (!/(?=.*[A-Z])/.test(password)) {
        return {
            isValid: false,
            message: 'Password must contain at least one uppercase letter'
        };
    }
    
    if (!/(?=.*\d)/.test(password)) {
        return {
            isValid: false,
            message: 'Password must contain at least one number'
        };
    }
    
    if (!/(?=.*[@$!%*?&])/.test(password)) {
        return {
            isValid: false,
            message: 'Password must contain at least one special character (@$!%*?&)'
        };
    }
    
    return { isValid: true, message: '' };
}

/**
 * Check email availability via AJAX
 * FIXED: Send correct field name
 */
function checkEmailAvailability(email) {
    $.ajax({
        url: '../actions/check_email_action.php',
        type: 'POST',
        data: { customer_email: email },  // Changed from 'email' to 'customer_email'
        dataType: 'json',
        success: function(response) {
            const emailField = $('#email');
            const feedbackDiv = $('#email-feedback');
            
            // Remove existing feedback
            feedbackDiv.remove();
            emailField.removeClass('is-valid is-invalid');
            
            if (response.exists) {
                emailField.addClass('is-invalid');
                emailField.after('<div id="email-feedback" class="invalid-feedback">This email is already registered</div>');
            } else {
                emailField.addClass('is-valid');
                emailField.after('<div id="email-feedback" class="valid-feedback">Email is available</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Email check error:', error);
        }
    });
}

/**
 * Update password strength indicator
 */
function updatePasswordStrength(password) {
    const strengthDiv = $('#password-strength');
    
    if (!password) {
        strengthDiv.hide();
        return;
    }
    
    let strength = 0;
    let strengthText = '';
    let strengthClass = '';
    
    // Calculate strength
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[@$!%*?&]/.test(password)) strength++;
    
    // Determine strength level
    switch (strength) {
        case 0:
        case 1:
            strengthText = 'Very Weak';
            strengthClass = 'text-danger';
            break;
        case 2:
            strengthText = 'Weak';
            strengthClass = 'text-warning';
            break;
        case 3:
            strengthText = 'Fair';
            strengthClass = 'text-info';
            break;
        case 4:
            strengthText = 'Good';
            strengthClass = 'text-primary';
            break;
        case 5:
            strengthText = 'Strong';
            strengthClass = 'text-success';
            break;
    }
    
    // Show/update strength indicator
    if (strengthDiv.length === 0) {
        $('#password').after('<div id="password-strength" class="form-text"></div>');
    }
    
    $('#password-strength')
        .removeClass('text-danger text-warning text-info text-primary text-success')
        .addClass(strengthClass)
        .text(`Password Strength: ${strengthText}`)
        .show();
}

/**
 * Show error message using SweetAlert
 */
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Validation Error',
        text: message,
        confirmButtonColor: '#2E86AB'
    });
}

/**
 * Reset submit button to original state
 */
function resetSubmitButton(button, originalText) {
    button.prop('disabled', false).html(originalText);
}
