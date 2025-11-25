/**
 * Artisan Registration Form Handler
 * Handles validation and AJAX submission for artisan registration
 */

$(document).ready(function() {
    // Form submission handler
    $('#artisan-register-form').submit(function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Registering...');
        
        // Get form data 
        const formData = {
            // Personal Information
            customer_name: $('#full_name').val().trim(),
            customer_email: $('#email').val().trim(),
            customer_pass: $('#password').val(),
            customer_country: $('#country').val().trim(),
            customer_city: $('#city').val().trim(),
            customer_contact: $('#contact_number').val().trim(),
            
            // Artisan/Business Information
            shop_name: $('#shop_name').val().trim(),
            craft_specialty: $('#craft_specialty').val(),
            years_experience: $('#years_experience').val(),
            workshop_location: $('#workshop_location').val().trim(),
            bio: $('#bio').val().trim()
        };
        
        // Client-side validation
        const validationResult = validateArtisanForm(formData);
        if (!validationResult.isValid) {
            showError(validationResult.message);
            resetSubmitButton(submitBtn, originalText);
            return;
        }
        
        // Submit form via AJAX
        $.ajax({
            url: '../actions/register_artisan_action.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                resetSubmitButton(submitBtn, originalText);
                
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registration Successful!',
                        html: `<p>${response.message}</p>
                               <p class="text-muted mt-2">Your account is pending verification by our admin team.</p>`,
                        confirmButtonColor: '#F18F01',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
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
    
    // Character counter for bio
    $('#bio').on('input', function() {
        const length = $(this).val().length;
        const minLength = 50;
        let counterHtml = '';
        
        if (length < minLength) {
            counterHtml = `<small class="text-danger">${length}/${minLength} characters (minimum ${minLength})</small>`;
        } else {
            counterHtml = `<small class="text-success">${length} characters ✓</small>`;
        }
        
        // Remove existing counter
        $('#bio-counter').remove();
        
        // Add new counter
        $(this).after(`<div id="bio-counter" class="mt-1">${counterHtml}</div>`);
    });
});

/**
 * Validate artisan registration form
 */
function validateArtisanForm(data) {
    // Check for empty personal fields
    const personalFields = ['customer_name', 'customer_email', 'customer_pass', 
                           'customer_country', 'customer_city', 'customer_contact'];
    
    for (const field of personalFields) {
        if (!data[field]) {
            return {
                isValid: false,
                message: `${field.replace('customer_', '').replace('_', ' ')} is required`
            };
        }
    }
    
    // Check for empty business fields
    const businessFields = ['shop_name', 'craft_specialty', 'years_experience', 
                           'workshop_location', 'bio'];
    
    for (const field of businessFields) {
        if (!data[field]) {
            return {
                isValid: false,
                message: `${field.replace('_', ' ')} is required`
            };
        }
    }
    
    // Validate full name
    if (data.customer_name.length < 2 || !/^[a-zA-Z\s]+$/.test(data.customer_name)) {
        return {
            isValid: false,
            message: 'Full name must be at least 2 characters and contain only letters'
        };
    }
    
    // Validate email
    if (!isValidEmail(data.customer_email)) {
        return {
            isValid: false,
            message: 'Please enter a valid email address'
        };
    }
    
    // Validate password
    const passwordValidation = validatePassword(data.customer_pass);
    if (!passwordValidation.isValid) {
        return passwordValidation;
    }
    
    // Validate shop name
    if (data.shop_name.length < 3) {
        return {
            isValid: false,
            message: 'Shop name must be at least 3 characters'
        };
    }
    
    // Validate craft specialty
    if (data.craft_specialty === '') {
        return {
            isValid: false,
            message: 'Please select your craft specialty'
        };
    }
    
    // Validate years of experience
    const experience = parseInt(data.years_experience);
    if (isNaN(experience) || experience < 0 || experience > 100) {
        return {
            isValid: false,
            message: 'Please enter valid years of experience (0-100)'
        };
    }
    
    // Validate bio length
    if (data.bio.length < 50) {
        return {
            isValid: false,
            message: 'Bio must be at least 50 characters'
        };
    }
    
    // Validate contact number
    if (!/^\+?[1-9]\d{9,14}$/.test(data.customer_contact)) {
        return {
            isValid: false,
            message: 'Contact number must be 10-15 digits'
        };
    }
    
    return { isValid: true, message: '' };
}

/**
 * Validate email format
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
            message: 'Password must contain at least one special character'
        };
    }
    
    return { isValid: true, message: '' };
}

/**
 * Check email availability
 */
function checkEmailAvailability(email) {
    $.ajax({
        url: '../actions/check_email_action.php',
        type: 'POST',
        data: { customer_email: email },
        dataType: 'json',
        success: function(response) {
            const emailField = $('#email');
            const feedbackDiv = $('#email-feedback');
            
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
    
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[@$!%*?&]/.test(password)) strength++;
    
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
 * Show error message
 */
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Validation Error',
        text: message,
        confirmButtonColor: '#F18F01'
    });
}

/**
 * Reset submit button
 */
function resetSubmitButton(button, originalText) {
    button.prop('disabled', false).html(originalText);
}