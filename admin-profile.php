<?php
include 'sql.php';

session_start();

// Check if the session ID is set
if (!isset($_SESSION['id'])) {
    header("location:admin-login.php?notif=SessionExpired");
    exit;
}

// Query to get user data
$query = "SELECT `id`, `email`, `pword`, `user_type` FROM `login` WHERE id = '".$_SESSION['id']."'";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);

// Handle password change form submission
if(isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Update password
    if(!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        // Verify current password
        if(password_verify($current_password, $row['pword']) || $current_password == $row['pword']) {
            if($new_password == $confirm_password) {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_password = "UPDATE `login` SET `pword` = '$hashed_password' WHERE `id` = '".$_SESSION['id']."'";
                
                if(mysqli_query($con, $update_password)) {
                    $success_password = "Password updated successfully!";
                } else {
                    $error_password = "Failed to update password: " . mysqli_error($con);
                }
            } else {
                $error_password = "New password and confirm password do not match!";
            }
        } else {
            $error_password = "Current password is incorrect!";
        }
    }
    
    // Refresh user data after update
    $result = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile</title>
    <link rel="icon" href="DENR.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --denr-dark-green: #2d6a4f;
            --denr-medium-green: #40916c;
            --denr-light-green: #52b788;
            --denr-pale-green: #d8f3dc;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        .profile-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0;
            background: linear-gradient(135deg, var(--denr-pale-green) 0%, #ffffff 100%);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
        }
        
        .profile-header {
            background: linear-gradient(to right, var(--denr-dark-green), var(--denr-medium-green));
            padding: 25px 30px;
            color: white;
            border-bottom: 4px solid var(--denr-light-green);
        }
        
        .profile-content {
            padding: 30px;
        }
        
        .tab-content {
            padding: 25px 15px;
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--denr-medium-green);
            box-shadow: 0 0 0 0.25rem rgba(64, 145, 108, 0.25);
        }
        
        .user-info {
            background: linear-gradient(to right, #ffffff, var(--denr-pale-green));
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-left: 4px solid var(--denr-medium-green);
        }
        
        .info-icon {
            background-color: var(--denr-medium-green);
            color: white;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 15px;
            font-size: 18px;
        }
        
        .info-row {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .info-content {
            flex: 1;
        }
        
        label {
            color: var(--denr-dark-green);
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .btn-primary {
            background-color: var(--denr-medium-green);
            border-color: var(--denr-medium-green);
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--denr-dark-green);
            border-color: var(--denr-dark-green);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .alert {
            border-radius: 8px;
            border-left: 4px solid;
        }
        
        .alert-success {
            border-left-color: #28a745;
            background-color: rgba(40, 167, 69, 0.1);
        }
        
        .alert-danger {
            border-left-color: #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
        }
    </style>
</head>
<body>
    
<nav class="navbar navbar-expand-lg" style="background-color: #2d6a4f !important; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
    <div class="container">
        <a class="navbar-brand text-white" href="admin-dash.php" style="font-weight: 600; display: flex; align-items: center; gap: 10px;">
            <img src="https://denr.gov.ph/wp-content/uploads/2023/04/denr-logo-png-1-1.png" alt="DENR Logo" style="width: 40px; height: auto;"> DENR Admin Portal
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link denr-nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'admin-dash.php') ? 'denr-active' : ''; ?>" href="admin-dash.php" style="font-weight: 500; padding: 0.8rem 1rem !important; transition: all 0.3s ease; color: white;"><i class="fas fa-home me-1"></i> Dashboard</a></li>
                
                <li class="nav-item"><a class="nav-link denr-nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'logout.php') ? 'denr-active' : ''; ?>" href="logout.php" style="font-weight: 500; padding: 0.8rem 1rem !important; transition: all 0.3s ease; color: white;"><i class="fas fa-sign-out-alt me-1"></i> Log Out</a></li>
            </ul>
        </div>
    </div>
</nav>

<style>
    .denr-nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
    }
    
    .denr-active {
        background-color: rgba(255, 255, 255, 0.2);
        border-radius: 4px;
    }
</style>

<div class="container profile-container">
    <div class="profile-header">
        <h2 class="mb-1"><i class="fas fa-user-circle me-2"></i>Manage Profile</h2>
        <p class="mb-0">Update your account password</p>
    </div>
    
    <div class="profile-content">
        <div class="user-info">
            <div class="info-row">
                <div class="info-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="info-content">
                    <p class="mb-0"><strong>Email Address</strong></p>
                    <p class="mb-0"><?php echo $row['email']; ?></p>
                </div>
            </div>
        </div>
        
        <div class="tab-content">
            <!-- Change Password Section -->
            <div>
                <?php if(isset($success_password)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success_password; ?>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($error_password)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_password; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="form-group">
                        <label for="current_password"><i class="fas fa-lock me-2"></i>Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password"><i class="fas fa-key me-2"></i>New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password"><i class="fas fa-check-double me-2"></i>Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" name="update_password" class="btn btn-primary">
                        <i class="fas fa-key me-2"></i>Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
    
<!-- JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>