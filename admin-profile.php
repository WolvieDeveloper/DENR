<?php
include 'sql.php';

session_start();

// Check if the session ID is set
if (!isset($_SESSION['id'])) {
    header("location:admin-login.php?notif=SessionExpired");
    exit;
}

// Fixed the WHEREWHERE typo
$query = "SELECT `id`, `email`, `pword`, `user_type` FROM `login` WHERE id = '".$_SESSION['id']."'";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);

// Handle form submission
if(isset($_POST['update_profile'])) {
    // $email = mysqli_real_escape_string($con, $_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Update email
    if(!empty($email) && $email != $row['email']) {
        $update_email = "UPDATE `login` SET `email` = '$email' WHERE `id` = '".$_SESSION['id']."'";
        if(mysqli_query($con, $update_email)) {
            $success_email = "Email updated successfully!";
        } else {
            $error_email = "Failed to update email: " . mysqli_error($con);
        }
    }
    
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 5px;
        }
        .tab-content {
            padding: 20px 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .alert {
            margin-bottom: 20px;
        }
        .user-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container profile-container">
        <h2 class="mb-4 text-center">Manage Profile</h2>
        
        <div class="user-info">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>User ID:</strong> <?php echo $row['id']; ?></p>
                    <p><strong>Email:</strong> <?php echo $row['email']; ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>User Type:</strong> <?php echo ucfirst($row['user_type']); ?></p>
                    <p><strong>Last Login:</strong> <?php echo isset($_SESSION['last_login']) ? $_SESSION['last_login'] : 'Not available'; ?></p>
                </div>
            </div>
        </div>
        
        <ul class="nav nav-tabs" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="update-profile-tab" data-bs-toggle="tab" data-bs-target="#update-profile" type="button" role="tab" aria-controls="update-profile" aria-selected="true">Update Profile</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="change-password-tab" data-bs-toggle="tab" data-bs-target="#change-password" type="button" role="tab" aria-controls="change-password" aria-selected="false">Change Password</button>
            </li>
        </ul>
        
        <div class="tab-content" id="profileTabsContent">
            <!-- Update Profile Tab -->
            <div class="tab-pane fade show active" id="update-profile" role="tabpanel" aria-labelledby="update-profile-tab">
                <?php if(isset($success_email)): ?>
                    <div class="alert alert-success"><?php echo $success_email; ?></div>
                <?php endif; ?>
                
                <?php if(isset($error_email)): ?>
                    <div class="alert alert-danger"><?php echo $error_email; ?></div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $row['email']; ?>" required>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
            
            <!-- Change Password Tab -->
            <div class="tab-pane fade" id="change-password" role="tabpanel" aria-labelledby="change-password-tab">
                <?php if(isset($success_password)): ?>
                    <div class="alert alert-success"><?php echo $success_password; ?></div>
                <?php endif; ?>
                
                <?php if(isset($error_password)): ?>
                    <div class="alert alert-danger"><?php echo $error_password; ?></div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>