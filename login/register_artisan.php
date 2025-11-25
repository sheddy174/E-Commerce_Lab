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
    <title>Artisan Registration - GhanaTunes</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2E86AB;
            --primary-hover: #1B5E7A;
            --accent-color: #F18F01;
        }
        
        body {
            background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            background-color: rgba(255, 255, 255, 0.98);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--accent-color), #C77700);
            color: white;
            padding: 1.5rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(241, 143, 1, 0.25);
        }
        
        .btn-artisan {
            background: linear-gradient(135deg, var(--accent-color), #C77700);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-artisan:hover {
            background: linear-gradient(135deg, #C77700, var(--accent-color));
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(241, 143, 1, 0.3);
            color: white;
        }
        
        .highlight {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .highlight:hover {
            color: #C77700;
        }

        .section-title {
            color: var(--accent-color);
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--accent-color);
        }
    </style>
</head>
<body>
    <div class="container register-container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-header text-center">
                        <h4><i class="fas fa-hammer me-2"></i>Artisan Registration</h4>
                        <p class="mb-0 mt-2">Join GhanaTunes as a Verified Artisan</p>
                    </div>
                    
                    <div class="card-body" style="padding: 2rem;">
                        <form id="artisan-register-form" novalidate>
                            <!-- Personal Information Section -->
                            <h5 class="section-title">
                                <i class="fas fa-user me-2"></i>Personal Information
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">
                                        Full Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="full_name" name="customer_name" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">
                                        Email Address <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control" id="email" name="customer_email" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control" id="password" name="customer_pass" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="contact_number" class="form-label">
                                        Contact Number <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" class="form-control" id="contact_number" name="customer_contact" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="country" class="form-label">
                                        Country <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="country" name="customer_country" value="Ghana" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">
                                        City <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="city" name="customer_city" required>
                                </div>
                            </div>

                            <!-- Artisan/Business Information Section -->
                            <h5 class="section-title">
                                <i class="fas fa-store me-2"></i>Business Information
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="shop_name" class="form-label">
                                        Shop/Business Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="shop_name" name="shop_name" required>
                                    <div class="form-text">This will be displayed on your storefront</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="craft_specialty" class="form-label">
                                        Craft Specialty <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="craft_specialty" name="craft_specialty" required>
                                        <option value="">Select your specialty</option>
                                        <option value="Traditional Drums">Traditional Drums (Kpanlogo, Fontomfrom, Atumpan)</option>
                                        <option value="String Instruments">String Instruments (Seperewa, Kora)</option>
                                        <option value="Percussion">Percussion Instruments</option>
                                        <option value="Wind Instruments">Wind Instruments (Horns, Flutes)</option>
                                        <option value="Modern Instruments">Modern Instruments</option>
                                        <option value="Instrument Repair">Instrument Repair & Maintenance</option>
                                        <option value="Multiple">Multiple Specialties</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="years_experience" class="form-label">
                                        Years of Experience <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="years_experience" name="years_experience" min="0" max="100" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="workshop_location" class="form-label">
                                        Workshop Location <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="workshop_location" name="workshop_location" placeholder="e.g., Accra, Kumasi, Tamale" required>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label for="bio" class="form-label">
                                        Bio / About Your Craft <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="bio" name="bio" rows="4" placeholder="Tell customers about your craftsmanship, experience, and what makes your instruments special" required></textarea>
                                    <div class="form-text">Minimum 50 characters</div>
                                </div>
                            </div>

                            <!-- Terms and Submit -->
                            <div class="col-12 mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" class="highlight">Artisan Terms & Conditions</a> 
                                        and understand the 10% commission structure <span class="text-danger">*</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-artisan w-100">
                                    <i class="fas fa-hammer me-2"></i>Register as Artisan
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="card-footer text-center" style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); padding: 1rem 2rem;">
                        <p class="mb-0">
                            Already registered? 
                            <a href="login.php" class="highlight">
                                <i class="fas fa-sign-in-alt me-1"></i>Login here
                            </a>
                        </p>
                        <p class="mb-0 mt-2">
                            <small>
                                Want to register as a customer? 
                                <a href="register.php" class="highlight">Customer Registration</a>
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/register_artisan.js"></script>
</body>
</html>