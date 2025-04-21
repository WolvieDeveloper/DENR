<?php
include_once "sql.php";
session_start();

// Check if the session ID is set
if (!isset($_SESSION['id'])) {
    header("location:admin-login.php?notif=SessionExpired");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="DENR.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="./style/admin-dash.css">
    <title>DENR Admin Portal</title>
    
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="admin-dash.php">
                <img src="denr.png" alt="DENR Logo"> DENR Admin Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="admin-dash.php"><i class="fas fa-home me-1"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="request.php"><i class="fas fa-clipboard-list me-1"></i> Requests</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> Account
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="admin-profile.php"><i class="fas fa-user me-1"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-1"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Log Out</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
     
    <div class="container mt-4">
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="dashboard-title">Admin Dashboard</h1>
                    <p class="dashboard-subtitle mb-0">Welcome to the DENR Request Management System</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <button class="btn btn-outline-primary me-2"><i class="fas fa-file-export me-1"></i> Export Report</button>
                    <button class="btn btn-primary"><i class="fas fa-sync-alt me-1"></i> Refresh</button>
                </div>
            </div>
        </div>
        
        <div class="row g-4">

        <div class="col-md-4">
    <div class="card stat-card">
        <div class="stat-card-header bg-warning text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">New Request</h5>
                <i class="fas fa-star fa-lg"></i>
            </div>
        </div>
        <div class="stat-card-body text-center">
            <div class="stat-icon mx-auto">
                <i class="fas fa-star fa-2x text-white"></i>
            </div>
            <h2 class="stat-value">
                <?php
                $query = "SELECT COUNT(*) as total FROM `temp-reqform`"; // Replace with your query
                $result = mysqli_query($con, $query);
                $fetch = mysqli_fetch_assoc($result);
                echo $fetch['total'];
                ?>
            </h2>
            <p class="stat-label mb-0">Description of the new category</p>
            <a href="admin-newreq.php" class="btn btn-outline-warning mt-3 w-100">View Details</a>
        </div>
    </div>
</div>


            <!-- <div class="col-md-4">
                <div class="card stat-card">
                    <div class="stat-card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Total Requests</h5>
                            <i class="fas fa-clipboard-list fa-lg"></i>
                        </div>
                    </div>
                    <div class="stat-card-body text-center">
                        <div class="stat-icon mx-auto">
                            <i class="fas fa-list fa-2x text-white"></i>
                        </div>
                        <h2 class="stat-value">
                            <?php
                            // $query = "SELECT COUNT(*) as total FROM reqform";
                            // $result = mysqli_query($con, $query);
                            // $fetch = mysqli_fetch_assoc($result);
                            // echo $fetch['total'];
                            ?>
                        </h2>
                        <p class="stat-label mb-0">All Time Requests</p>
                        <a href="request.php" class="btn btn-outline-primary mt-3 w-100">View All Requests</a>
                    </div>
                </div>
            </div> -->


            <!-- Pending -->
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="stat-card-header bg-danger text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Pending</h5>
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                    </div>
                    <div class="stat-card-body text-center">
                        <div class="stat-icon mx-auto">
                            <i class="fas fa-clock fa-2x text-white"></i>
                        </div>
                        <h2 class="stat-value">
                            <?php
                            $query = "SELECT COUNT(*) as total FROM reqform WHERE status = 'pending'";
                            $result = mysqli_query($con, $query);
                            $fetch = mysqli_fetch_assoc($result);
                            echo $fetch['total'];
                            ?>
                        </h2>
                        <p class="stat-label mb-0">Awaiting Action</p>
                        <a href="admin-pending.php" class="btn btn-outline-danger mt-3 w-100">View Pending</a>
                    </div>
                </div>
            </div>
            


            <!-- Completed -->
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="stat-card-header bg-success text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Completed</h5>
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                    </div>
                    <div class="stat-card-body text-center">
                        <div class="stat-icon mx-auto">
                            <i class="fas fa-check-circle fa-2x text-white"></i>
                        </div>
                        <h2 class="stat-value">
                            <?php
                            $query = "SELECT COUNT(*) as total FROM actions";
                            $result = mysqli_query($con, $query);
                            $fetch = mysqli_fetch_assoc($result);
                            echo $fetch['total'];
                            ?>
                        </h2>
                        <p class="stat-label mb-0">Actioned Requests</p>
                        <a href="admin-reports.php" class="btn btn-outline-success mt-3 w-100">View Completed</a>
                    </div>
                </div>
            </div>
            
            
            
           
        </div>
        



        
        
        <!-- Recent Activities -->
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Requester</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Display the 5 most recent requests
                                    $query = "SELECT * FROM reqform ORDER BY id DESC LIMIT 5";
                                    $result = mysqli_query($con, $query);
                                    
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $statusClass = $row['status'] == 'pending' ? 'warning' : 'success';
                                        echo "<tr>";
                                        echo "<td>REQ-" . str_pad($row['id'], 4, '0', STR_PAD_LEFT) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['ReqPersonel']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['ReqType']) . "</td>";
                                        echo "<td><span class='badge bg-$statusClass'>" . ucfirst($row['status']) . "</span></td>";
                                        echo "<td>" . date('M d, Y', strtotime($row['date_filed'])) . "</td>";
                                        echo "</tr>";
                                    }
                                    
                                    // If no records found
                                    if (mysqli_num_rows($result) == 0) {
                                        echo "<tr><td colspan='5' class='text-center'>No recent requests found</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-end">
                        <a href="request.php" class="btn btn-sm btn-primary">View All Requests</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="admin-request.php" class="btn btn-outline-primary"><i class="fas fa-plus me-2"></i>Create New Request</a>
                            <a href="reports.php" class="btn btn-outline-secondary"><i class="fas fa-chart-bar me-2"></i>Generate Reports</a>
                            <a href="users.php" class="btn btn-outline-dark"><i class="fas fa-users me-2"></i>Manage Users</a>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">System Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <span class="badge bg-success rounded-circle p-2"><i class="fas fa-check"></i></span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Database Connection</h6>
                                <small class="text-muted">Online</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <span class="badge bg-success rounded-circle p-2"><i class="fas fa-check"></i></span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">File Storage</h6>
                                <small class="text-muted">90% Available</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <span class="badge bg-success rounded-circle p-2"><i class="fas fa-check"></i></span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Email Service</h6>
                                <small class="text-muted">Operational</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
    </div>
    
    <footer class="footer">
        <div class="container">
            <p class="mb-0">© <?php echo date('Y'); ?> Department of Environment and Natural Resources. All Rights Reserved.</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>