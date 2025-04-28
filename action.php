<?php 
// Include necessary files and start session first
session_start();
require_once 'sql.php'; // Use require_once for critical files
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants
define('USER_TYPE_ADMIN', 1);

// Function to sanitize output
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Function to redirect with a message
function redirect($location, $message) {
    header("location: $location?notif=" . urlencode($message));
    exit;
}

// Check if the session ID is set
if (!isset($_SESSION['id'])) {
    redirect("admin-login.php", "SessionExpired");
}

// Validate user session and permissions
$sessid = intval($_SESSION['id']);
$query = "SELECT user_type FROM login WHERE id = ?";

try {
    $stmt = mysqli_prepare($con, $query);
    
    if (!$stmt) {
        throw new Exception("Database query preparation failed: " . mysqli_error($con));
    }
    
    // Bind the parameter and execute the query
    mysqli_stmt_bind_param($stmt, "i", $sessid);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Query execution failed: " . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    $fetch = mysqli_fetch_assoc($result);
    
    if (!$fetch) {
        redirect("admin-login.html", "UserNotFound");
    }
    
    $usertype = $fetch['user_type'];
    if ($usertype != USER_TYPE_ADMIN) {
        redirect("index.html", "Restricted");
    }
    
    mysqli_stmt_close($stmt);
} catch (Exception $e) {
    // Log the error (in a production environment)
    error_log($e->getMessage());
    redirect("error.php", "DatabaseError");
}

// Process form submission
$controlnumber = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate control number
    if (isset($_POST['controlnumber']) && !empty($_POST['controlnumber'])) {
        $controlnumber = trim($_POST['controlnumber']);
        
        // You could add additional validation for control number format here
        // For example, check if it exists in your database
    } else {
        redirect("admin-dash.php", "MissingControlNumber");
    }
} else {
    // If not POST, check if it's provided in GET
    if (isset($_GET['controlnumber']) && !empty($_GET['controlnumber'])) {
        $controlnumber = trim($_GET['controlnumber']);
    } else {
        redirect("admin-dash.php", "MissingControlNumber");
    }
}

// Fetch the previous data if it exists (for editing capability)
$initialFindings = $actionTaken = $recommendation = '';
$stmt = mysqli_prepare($con, "SELECT initialFindings, actionTaken, recommendation FROM actions WHERE controlnumber = ?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $controlnumber);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $initialFindings, $actionTaken, $recommendation);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

$requestData = [];
$requestStmt = mysqli_prepare($con, "SELECT * FROM reqform WHERE controlnumber = ?");
if ($requestStmt) {
    mysqli_stmt_bind_param($requestStmt, "s", $controlnumber);
    mysqli_stmt_execute($requestStmt);
    $requestResult = mysqli_stmt_get_result($requestStmt);
    $requestData = mysqli_fetch_assoc($requestResult);
    mysqli_stmt_close($requestStmt);
}

// Get current status for the status indicator
$status = $requestData['status'] ?? $requestData['request_status'] ?? 'Pending';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Action form for DENR administrative purposes">
    <link rel="icon" href="DENR.png" />
    <title>DENR Action Form - Control Number: <?php echo h($controlnumber); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --denr-primary: #0c5e2b; /* DENR Green */
            --denr-secondary: #f8c301; /* DENR Yellow */
            --denr-accent: #2c8c5e;
            --denr-light: #e8f5e9;
            --denr-dark: #0b4d23;
            --denr-gray: #f3f3f3;
        }
        
        body {
            background-color: #f9f9f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .form-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            background-color: white;
            overflow: hidden;
        }
        
        .action-header {
            background-color: var(--denr-primary);
            color: white;
            padding: 20px 25px;
            margin-bottom: 0;
        }
        
        .form-content {
            padding: 25px;
        }
        
        .denr-logo {
            height: 50px;
            margin-right: 15px;
        }
        
        .header-flex {
            display: flex;
            align-items: center;
        }
        
        .control-badge {
            background-color: var(--denr-secondary);
            color: var(--denr-dark);
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin-top: 10px;
        }
        
        .card {
            border: none;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: var(--denr-light);
            color: var(--denr-dark);
            font-weight: 600;
            padding: 15px 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .section-title {
            color: var(--denr-primary);
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eaeaea;
        }
        
        .required-field::after {
            content: " *";
            color: #dc3545;
            font-weight: bold;
        }
        
        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eaeaea;
        }
        
        .form-label {
            font-weight: 500;
            color: #444;
        }
        
        .form-control:focus {
            border-color: var(--denr-accent);
            box-shadow: 0 0 0 0.2rem rgba(44, 140, 94, 0.25);
        }
        
        .btn-primary {
            background-color: var(--denr-primary);
            border-color: var(--denr-primary);
        }
        
        .btn-primary:hover {
            background-color: var(--denr-dark);
            border-color: var(--denr-dark);
        }
        
        .btn-outline-primary {
            color: var(--denr-primary);
            border-color: var(--denr-primary);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--denr-primary);
            color: white;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 3px;
            font-size: 0.85rem;
        }
        
        .info-value {
            margin-bottom: 15px;
        }
        
        .job-description {
            padding: 15px;
            background-color: var(--denr-gray);
            border-radius: 6px;
            border-left: 4px solid var(--denr-accent);
        }
        
        .status-indicator {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .equipment-details {
            background-color: rgba(44, 140, 94, 0.05);
            border-radius: 6px;
            padding: 15px;
            margin: 10px 0;
        }
        
        textarea.form-control {
            border: 1px solid #ddd;
        }
        
        .form-text {
            font-style: italic;
            color: #777;
        }
        
        /* Toast styling */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }
        
        .denr-toast {
            background-color: white;
            border-left: 4px solid var(--denr-primary);
        }
    </style>
</head>
<body>
    <!-- Toast container for notifications -->
    <div class="toast-container">
        <?php if(isset($_GET['message'])): ?>
        <div class="toast denr-toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">DENR System Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <?php echo h($_GET['message']); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="container">
        <div class="form-container">
            <div class="action-header">
                <div class="header-flex">
                    <img src="https://denr.gov.ph/wp-content/uploads/2023/04/denr-logo-png-1-1.png" alt="DENR Logo" class="denr-logo">
                    <div>
                        <h2 class="mb-0">DENR System Action Form</h2>
                        <p class="mb-0">Department of Environment and Natural Resources</p>
                    </div>
                </div>
                <div class="control-badge">
                    <i class="fas fa-hashtag"></i> Control Number: <?php echo h($controlnumber); ?>
                </div>
            </div>
            
            <div class="form-content">
                <!-- Request Information Card -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Request Information</h5>
                        <?php 
                        $statusClass = 'status-pending';
                        if ($status == 'Processing' || $status == 'In Progress') {
                            $statusClass = 'status-processing';
                        } elseif ($status == 'Completed' || $status == 'Resolved') {
                            $statusClass = 'status-completed';
                        }
                        ?>
                        <span class="status-indicator <?php echo $statusClass; ?>"><?php echo h($status); ?></span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p class="info-label">Requesting Personnel</p>
                                <p class="info-value"><?php echo h($requestData['ReqPersonel'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="info-label">Division</p>
                                <p class="info-value"><?php echo h($requestData['Division'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="info-label">Email</p>
                                <p class="info-value"><?php echo h($requestData['Email'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <p class="info-label">Request Type</p>
                                <p class="info-value">
                                    <?php 
                                    $reqType = $requestData['ReqType'] ?? 'N/A';
                                    $iconClass = 'fa-question-circle';
                                    
                                    if ($reqType == 'Hardware') {
                                        $iconClass = 'fa-desktop';
                                    } elseif ($reqType == 'Software') {
                                        $iconClass = 'fa-code';
                                    } elseif ($reqType == 'Network') {
                                        $iconClass = 'fa-network-wired';
                                    }
                                    ?>
                                    <i class="fas <?php echo $iconClass; ?> me-1"></i> <?php echo h($reqType); ?>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p class="info-label">Date Submitted</p>
                                <p class="info-value">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    <?php echo h(date('M d, Y', strtotime($requestData['date_submitted'] ?? 'now'))); ?>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p class="info-label">Control Number</p>
                                <p class="info-value fw-bold"><?php echo h($controlnumber); ?></p>
                            </div>
                        </div>
                        
                        <?php if($requestData['ReqType'] == 'Hardware'): ?>
                        <div class="equipment-details mt-3">
                            <h6 class="mb-3"><i class="fas fa-tools me-2"></i>Equipment Details</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <p class="info-label">Brand/Model</p>
                                    <p class="info-value"><?php echo h($requestData['BrandModel'] ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p class="info-label">Serial Number</p>
                                    <p class="info-value"><?php echo h($requestData['SerialNo'] ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p class="info-label">Property No</p>
                                    <p class="info-value"><?php echo h($requestData['PropertyNo'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="info-label">Responsible Person</p>
                                    <p class="info-value"><?php echo h($requestData['ResPerson'] ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="info-label">Date of Purchase</p>
                                    <p class="info-value">
                                    <?php 
                                    $purchaseDate = $requestData['datepurchase'] ?? 'N/A';
                                    echo ($purchaseDate != 'N/A') ? date('M d, Y', strtotime($purchaseDate)) : 'N/A'; 
                                    ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <h6 class="mb-2"><i class="fas fa-tasks me-2"></i>Job Request Description</h6>
                            <div class="job-description">
                                <?php echo nl2br(h($requestData['JobDescription'] ?? 'No description provided')); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Action Details</h5>
                    </div>
                    <div class="card-body">
                        <form action="submit_action.php" method="POST" id="actionForm" enctype="multipart/form-data">
                            <input type="hidden" name="controlnumber" value="<?php echo h($controlnumber); ?>">
                            
                            <div class="mb-4">
                                <label for="initialFindings" class="form-label required-field">Initial Findings</label>
                                <textarea class="form-control" id="initialFindings" name="initialFindings" rows="4" required><?php echo h($initialFindings); ?></textarea>
                                <div class="form-text"><i class="fas fa-info-circle me-1"></i>Describe what was initially discovered upon assessment.</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="actionTaken" class="form-label required-field">Action Taken</label>
                                <textarea class="form-control" id="actionTaken" name="actionTaken" rows="4" required><?php echo h($actionTaken); ?></textarea>
                                <div class="form-text"><i class="fas fa-info-circle me-1"></i>Describe the steps taken to address the issue in detail.</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="recommendation" class="form-label required-field">Recommendation</label>
                                <textarea class="form-control" id="recommendation" name="recommendation" rows="4" required><?php echo h($recommendation); ?></textarea>
                                <div class="form-text"><i class="fas fa-info-circle me-1"></i>Provide recommendations for future handling of similar issues.</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="attachments" class="form-label">Attachments (Optional)</label>
                                <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
                                <div class="form-text"><i class="fas fa-paperclip me-1"></i>Upload any relevant documents (PDF, DOCX, JPG, PNG). Max size: 5MB each.</div>
                            </div>
                            
                            <div class="nav-buttons">
                                <a href="admin-pending.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Pending Requests
                                </a>
                                <div>
                                    <button type="button" class="btn btn-outline-primary me-2" id="saveAsDraft">
                                        <i class="far fa-save me-1"></i> Save as Draft
                                    </button>
                                    <button type="submit" name="action_sub" class="btn btn-primary">
                                        <i class="fas fa-check-circle me-1"></i> Submit Action
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <footer class="text-center text-muted mb-4">
            <small>DENR Information System &copy; <?php echo date('Y'); ?> | Department of Environment and Natural Resources</small>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize toasts
        const toastElList = [].slice.call(document.querySelectorAll('.toast'));
        const toastList = toastElList.map(function(toastEl) {
            const toast = new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: 5000
            });
            return toast;
        });
        
        // Form validation
        const form = document.getElementById('actionForm');
        
        form.addEventListener('submit', function(event) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                event.preventDefault();
                
                // Create and show validation error toast
                const toastContainer = document.querySelector('.toast-container');
                const validationToast = document.createElement('div');
                validationToast.className = 'toast denr-toast show';
                validationToast.setAttribute('role', 'alert');
                validationToast.innerHTML = `
                    <div class="toast-header">
                        <strong class="me-auto">Form Validation Error</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        Please fill in all required fields.
                    </div>
                `;
                toastContainer.appendChild(validationToast);
                
                // Initialize the new toast
                const bsToast = new bootstrap.Toast(validationToast, {
                    autohide: true,
                    delay: 5000
                });
                bsToast.show();
            }
        });
        
        // Reset validation on input
        const inputs = form.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                }
            });
        });
        
        // Draft functionality
        document.getElementById('saveAsDraft').addEventListener('click', function() {
            const formData = new FormData(form);
            formData.append('save_as_draft', 'true');
            
            fetch('save_draft.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Create and show draft status toast
                const toastContainer = document.querySelector('.toast-container');
                const draftToast = document.createElement('div');
                draftToast.className = 'toast denr-toast show';
                draftToast.setAttribute('role', 'alert');
                
                if (data.success) {
                    draftToast.innerHTML = `
                        <div class="toast-header">
                            <strong class="me-auto">Draft Status</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                        </div>
                        <div class="toast-body">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Draft saved successfully!
                        </div>
                    `;
                } else {
                    draftToast.innerHTML = `
                        <div class="toast-header">
                            <strong class="me-auto">Draft Status</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                        </div>
                        <div class="toast-body">
                            <i class="fas fa-exclamation-circle text-danger me-2"></i>
                            Failed to save draft: ${data.message}
                        </div>
                    `;
                }
                
                toastContainer.appendChild(draftToast);
                
                // Initialize the new toast
                const bsToast = new bootstrap.Toast(draftToast, {
                    autohide: true,
                    delay: 5000
                });
                bsToast.show();
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Create and show error toast
                const toastContainer = document.querySelector('.toast-container');
                const errorToast = document.createElement('div');
                errorToast.className = 'toast denr-toast show';
                errorToast.setAttribute('role', 'alert');
                errorToast.innerHTML = `
                    <div class="toast-header">
                        <strong class="me-auto">Error</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        An error occurred while saving the draft.
                    </div>
                `;
                toastContainer.appendChild(errorToast);
                
                // Initialize the new toast
                const bsToast = new bootstrap.Toast(errorToast, {
                    autohide: true,
                    delay: 5000
                });
                bsToast.show();
            });
        });
    });
    </script>
</body>
</html>