<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration - E-Commerce Platform</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Animate CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2E86AB;
            --primary-hover: #1B5E7A;
            --accent-color: #F18F01;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
        }
        
        body {
            background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 50%, #90CAF9 100%);
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                repeating-linear-gradient(0deg,
                    rgba(46, 134, 171, 0.1),
                    rgba(46, 134, 171, 0.1) 1px,
                    transparent 1px,
                    transparent 1.25rem),
                repeating-linear-gradient(90deg,
                    rgba(46, 134, 171, 0.1),
                    rgba(46, 134, 171, 0.1) 1px,
                    transparent 1px,
                    transparent 1.25rem);
            background-size: 1.25rem 1.25rem;
            z-index: -1;
        }
        
        .register-container {
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        
        .card {
            border: none;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 1rem 2rem rgba(46, 134, 171, 0.15);
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.98);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            border-bottom: none;
            padding: 1.5rem;
        }
        
        .card-header h4 {
            margin: 0;
            font-weight: 600;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }
        
        .form-label i {
            color: var(--primary-color);
            margin-left: 0.25rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(46, 134, 171, 0.25);
        }
        
        .form-control.is-valid {
            border-color: var(--success-color);
        }
        
        .form-control.is-invalid {
            border-color: var(--danger-color);
        }
        
        .btn-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-custom:hover {
            background: linear-gradient(135deg, var(--primary-hover), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(46, 134, 171, 0.3);
        }
        
        .btn-custom:disabled {
            background: var(--secondary-color);
            transform: none;
            box-shadow: none;
        }
        
        .card-footer {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-top: 1px solid #e9ecef;
            padding: 1rem 2rem;
        }
        
        .highlight {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .highlight:hover {
            color: var(--primary-hover);
        }
        
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
        }
        
        .loading-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        /* Form validation feedback */
        .valid-feedback, .invalid-feedback {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        /* Password strength indicator */
        #password-strength {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .register-container {
                margin-top: 1rem;
                padding: 0 1rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .card-footer {
                padding: 1rem 1.5rem;
            }
        }
        
        /* Animation classes */
        .animate-pulse-custom {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <div class="container register-container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-header text-center">
                        <h4><i class="fas fa-user-plus me-2"></i>Customer Registration</h4>
                        <p class="mb-0 mt-2" style="opacity: 0.9;">Create your account to start shopping</p>
                    </div>
                    
                    <div class="card-body">
                        <form id="register-form" novalidate>
                            <div class="row">
                                <!-- Full Name -->
                                <div class="col-md-12 mb-3">
                                    <label for="full_name" class="form-label">
                                        Full Name <i class="fas fa-user"></i>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="full_name" 
                                           name="customer_name" 
                                           placeholder="Enter your full name"
                                           maxlength="100"
                                           required>
                                    <div class="form-text">Minimum 2 characters, letters and spaces only</div>
                                </div>
                                
                                <!-- Email -->
                                <div class="col-md-12 mb-3">
                                    <label for="email" class="form-label">
                                        Email Address <i class="fas fa-envelope"></i>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="customer_email" 
                                           placeholder="Enter your email address"
                                           maxlength="50"
                                           required>
                                    <div class="form-text">We'll check if this email is available</div>
                                </div>
                                
                                <!-- Password -->
                                <div class="col-md-12 mb-3">
                                    <label for="password" class="form-label">
                                        Password <i class="fas fa-lock"></i>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="position-relative">
                                        <input type="password" 
                                               class="form-control" 
                                               id="password" 
                                               name="customer_pass" 
                                               placeholder="Create a strong password"
                                               maxlength="150"
                                               required>
                                        <button type="button" 
                                                class="btn btn-outline-secondary position-absolute top-50 end-0 translate-middle-y me-2" 
                                                id="togglePassword"
                                                style="border: none; background: none;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        At least 8 characters with uppercase, lowercase, number, and special character
                                    </div>
                                </div>
                                
                                <!-- Country -->
                                <div class="col-md-6 mb-3">
                                    <label for="country" class="form-label">
                                        Country <i class="fas fa-globe"></i>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="country" 
                                           name="customer_country" 
                                           placeholder="Enter your country"
                                           maxlength="30"
                                           required>
                                </div>
                                
                                <!-- City -->
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">
                                        City <i class="fas fa-map-marker-alt"></i>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="city" 
                                           name="customer_city" 
                                           placeholder="Enter your city"
                                           maxlength="30"
                                           required>
                                </div>
                                
                                <!-- Contact Number -->
                                <div class="col-md-12 mb-4">
                                    <label for="contact_number" class="form-label">
                                        Contact Number <i class="fas fa-phone"></i>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="contact_number" 
                                           name="customer_contact" 
                                           placeholder="Enter your phone number (e.g., +233123456789)"
                                           maxlength="15"
                                           required>
                                    <div class="form-text">Include country code (e.g., +233 for Ghana)</div>
                                </div>
                                
                                <!-- Terms and Conditions -->
                                <div class="col-12 mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="terms" 
                                               required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the 
                                            <a href="#" class="highlight" data-bs-toggle="modal" data-bs-target="#termsModal">
                                                Terms and Conditions
                                            </a> 
                                            and 
                                            <a href="#" class="highlight" data-bs-toggle="modal" data-bs-target="#privacyModal">
                                                Privacy Policy
                                            </a>
                                            <span class="text-danger">*</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Submit Button -->
                                <div class="col-12">
                                    <button type="submit" 
                                            class="btn btn-custom w-100 animate-pulse-custom">
                                        <i class="fas fa-user-plus me-2"></i>Create Account
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="card-footer text-center">
                        <p class="mb-0">
                            Already have an account? 
                            <a href="login.php" class="highlight">
                                <i class="fas fa-sign-in-alt me-1"></i>Login here
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: var(--primary-color); color: white;">
                    <h5 class="modal-title" id="termsModalLabel">
                        <i class="fas fa-file-contract me-2"></i>Terms and Conditions
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Acceptance of Terms</h6>
                    <p>By creating an account, you agree to abide by these terms and conditions.</p>
                    
                    <h6>2. Account Responsibility</h6>
                    <p>You are responsible for maintaining the confidentiality of your account information.</p>
                    
                    <h6>3. Prohibited Activities</h6>
                    <p>Users must not engage in fraudulent activities or violate any applicable laws.</p>
                    
                    <h6>4. Platform Usage</h6>
                    <p>Our platform is intended for legitimate e-commerce transactions only.</p>
                    
                    <p class="text-muted mt-3">
                        <small>Last updated: September 2025</small>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Privacy Policy Modal -->
    <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: var(--primary-color); color: white;">
                    <h5 class="modal-title" id="privacyModalLabel">
                        <i class="fas fa-shield-alt me-2"></i>Privacy Policy
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>Data Collection</h6>
                    <p>We collect information you provide during registration and platform usage.</p>
                    
                    <h6>Data Usage</h6>
                    <p>Your data is used to provide services, process orders, and improve user experience.</p>
                    
                    <h6>Data Protection</h6>
                    <p>We implement security measures to protect your personal information.</p>
                    
                    <h6>Data Sharing</h6>
                    <p>We do not sell your personal information to third parties.</p>
                    
                    <p class="text-muted mt-3">
                        <small>Last updated: September 2025</small>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/register.js"></script>
   
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                password.type = 'password';
                icon.className = 'fas fa-eye';
            }
        });
        
        // Terms checkbox validation
        document.getElementById('register-form').addEventListener('submit', function(e) {
            const termsCheckbox = document.getElementById('terms');
            if (!termsCheckbox.checked) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Terms Required',
                    text: 'Please accept the Terms and Conditions to continue.',
                    confirmButtonColor: '#D19C97'
                });
            }
        });
    </script>
</body>
</html>