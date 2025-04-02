<?php 

include_once 'sql.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("location:admin-login.php?notif=SessionExpired");
    exit;
}

 // Get today's date in YYMMDD format
 $today = date("ymd");
    
 // Ensure the count increments correctly by locking the table row
 $count_query = "SELECT COUNT(*) AS count FROM `reqform` WHERE DATE(date_filed) = CURDATE() FOR UPDATE";
 $count_result = mysqli_query($con, $count_query);
 $count_fetch = mysqli_fetch_assoc($count_result);
 $count = $count_fetch['count'] + 1;

 // Generate the unique control number
 $controlnumber = $today . '-' . str_pad($count, 4, "0", STR_PAD_LEFT); // Ensures 4-digit request number


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DENR ICT Service Request Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
 
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./style/admin-request.css">
    <style>
      
    </style>
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
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-1"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-1"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Log Out</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="header">
            <div style="display: flex; align-items: center;">
                <img src="DENR.png" alt="DENR Logo" class="header-logo">
                <h1>DENR ICT Service Request Form (Manual Input) </h1>
            </div>
            <div class="status-badge">New Request</div>
        </div>
        
        <form action="config.php" method="post">
            <div class="form">
                <div class="form-section">
                    <div class="section-title">Request Information</div>
                    <div class="form-row">
                        <div class="form-col">
                            <label for="controlNo">Control No:</label>
                            <input type="text" id="controlNo" disabled name="controlNo" value="<?php echo $controlnumber ?>">
                        </div>
                        <div class="form-col">
                            <label for="dateRequest" class="required">Date of Request:</label>
                            <input type="date" id="dateRequest" name="dateRequest" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <label for="division" class="required">Division:</label>
                            <select id="division" name="division" required>
                                <option value="">-- Select Division --</option>
                                <?php
                                $query = "SELECT * FROM `division`";
                                $result = mysqli_query($con, $query);

                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<option value='".$row['divname']."'>".$row['divname']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-col">
                            <label for="requestType" class="required">Request Type:</label>
                            <select id="requestType" name="requestType" required>
                                <option value="">-- Select Request Type --</option>
                                <option value="hardware">Hardware</option>
                                <option value="software">Software</option>
                                <option value="network">Network</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="section-title">Equipment Information</div>
                    <div class="form-row">
                        <div class="form-col">
                            <label for="purchaseDate">Date of Purchase:</label>
                            <input type="date" id="purchaseDate" name="purchaseDate">
                        </div>
                        <div class="form-col">
                            <label for="responsiblePerson" class="required">Responsible Person:</label>
                            <input type="text" id="responsiblePerson" name="responsiblePerson" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <label for="brandModel">Brand/Model:</label>
                            <input type="text" id="brandModel" name="brandModel">
                        </div>
                        <div class="form-col">
                            <label for="serialNo">Serial No:</label>
                            <input type="text" id="serialNo" name="serialNo">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <label for="propertyNo">Property No:</label>
                            <input type="text" id="propertyNo" name="propertyNo">
                        </div>
                        <div class="form-col">
                            <label for="requestingPersonnel" class="required">Requesting Personnel:</label>
                            <input type="text" id="requestingPersonnel" name="requestingPersonnel" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="section-title">Service Details</div>
                    <div class="form-group">
                        <label for="jobDescription" class="required">Job Request Description:</label>
                        <textarea id="jobDescription" name="jobDescription" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="initialFindings">Initial Findings:</label>
                        <textarea id="initialFindings" name="initialFindings"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="ActionTaken">Action Taken:</label>
                        <textarea id="ActionTaken" name="ActionTaken"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="recommendation">Recommendation:</label>
                        <textarea id="recommendation" name="recommendation"></textarea>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="section-title">Completion and Feedback</div>
                    <div class="form-row">
                        <div class="form-col">
                            <label for="dateFinished">Date Finished:</label>
                            <input type="date" id="dateFinished" name="dateFinished">
                        </div>
                        <div class="form-col">
    <label for="feedbackRating">Feedback Rating: <span id="ratingValue">5</span>/5 - <span id="ratingText">Excellent</span></label>
    
    <div class="rating-container">
       
    </div>
    <select id="feedbackRating" name="feedbackRating">
        <option value="5" selected>Excellent</option>
        <option value="4">Good</option>
        <option value="3">Average</option>
        <option value="2">Poor</option>
        <option value="1">Bad</option>
    </select>
</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="comments">Comments:</label>
                        <textarea id="comments" name="comments"></textarea>
                    </div>
                </div>
                
                <div class="btn-container">
                    <button type="reset" class="btn btn-secondary">Reset</button>
                    <input type="submit" name="admin-sub" class="btn btn-primary" value="Submit Req">
                    <button type="submit" name="admin-sub" class="btn btn-primary">Submit Request</button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
 
</body>
</html>