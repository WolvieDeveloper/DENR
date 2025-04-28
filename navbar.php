<?php

?>

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
                <!-- <li class="nav-item"><a class="nav-link denr-nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'request.php') ? 'denr-active' : ''; ?>" href="request.php" style="font-weight: 500; padding: 0.8rem 1rem !important; transition: all 0.3s ease; color: white;"><i class="fas fa-clipboard-list me-1"></i> Requests</a></li> -->
                <li class="nav-item dropdown">
                    <a class="nav-link denr-nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" style="font-weight: 500; padding: 0.8rem 1rem !important; transition: all 0.3s ease; color: white;">
                        <i class="fas fa-user-circle me-1"></i> Account
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-1"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Log Out</a></li>
                    </ul>
                </li>
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