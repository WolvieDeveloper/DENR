<?php 
session_start();
include_once "sql.php";
if (isset($_SESSION['id'])) {
    header("location:admin-dash.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="DENR.png" />
    <title>DENR Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="./style/admin-login.css" />
</head>
<body>
    <div class="background-image"></div>
    
    <!-- Home/Return Button -->
    <a href="index.html" class="btn-home">
        <i class="fas fa-home"></i>
        <span class="tooltip-text">Return Home</span>
    </a>
    
    <div class="login-container">
        <div class="login-card">
            <img src="DENR.png" class="denr-logo" alt="DENR Logo Background">
            
            <div class="logo-container">
                <img src="DENR.png" class="logo-image" alt="DENR Logo">
                <h3 class="text-center">DENR ICTU</h3>
            </div>
            
            <form method="post" action="config.php">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" placeholder="Enter your email" name="email" required>
                    <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
                </div>
                
                <div class="form-floating mb-4 position-relative">
                    <input type="password" class="form-control" id="password" placeholder="Enter your password" name="pword" required>
                    <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                    <i class="fas fa-eye-slash toggle-password" id="togglePassword"></i>
                </div>
                
                <button type="submit" name="login" class="btn btn-login w-100 mb-3 pulse">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
                
                <div class="text-center">
                    <a href="admin-register.php" class="forgot-link">Register</a>
                </div>
                
                <div class="help-text">
                    Department of Environment and Natural Resources<br>
                    Administrative Portal
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add animation when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const loginCard = document.querySelector('.login-card');
            loginCard.style.opacity = '0';
            loginCard.style.transform = 'translateY(20px)';
            
            setTimeout(function() {
                loginCard.style.transition = 'all 0.5s ease';
                loginCard.style.opacity = '1';
                loginCard.style.transform = 'translateY(0)';
            }, 200);
            
            // Home button animation
            const homeBtn = document.querySelector('.btn-home');
            homeBtn.style.opacity = '0';
            homeBtn.style.transform = 'translateX(-20px)';
            
            setTimeout(function() {
                homeBtn.style.transition = 'all 0.5s ease';
                homeBtn.style.opacity = '1';
                homeBtn.style.transform = 'translateX(0)';
            }, 600);
            
            // Password visibility toggle
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');
            
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
            
            // Remove pulse animation after 5 seconds
            setTimeout(function() {
                document.querySelector('.pulse').classList.remove('pulse');
            }, 5000);
        });
    </script>
</body>
</html>