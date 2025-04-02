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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Action form for administrative purposes">
    <title>Action Form - Control Number: <?php echo h($controlnumber); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .form-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .action-header {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="action-header">
                <h2>Action Form</h2>
                <p class="lead">Control Number: <strong><?php echo h($controlnumber); ?></strong></p>
                <?php if(isset($_GET['message'])): ?>
                <div class="alert alert-info"><?php echo h($_GET['message']); ?></div>
                <?php endif; ?>
            </div>
            
            <form action="submit_action.php" method="POST" id="actionForm">
                <input type="hidden" name="controlnumber" value="<?php echo h($controlnumber); ?>">
                
                <div class="mb-3">
                    <label for="initialFindings" class="form-label required-field">Initial Findings</label>
                    <textarea class="form-control" id="initialFindings" name="initialFindings" rows="4" required><?php echo h($initialFindings); ?></textarea>
                    <div class="form-text">Describe what was initially discovered.</div>
                </div>
                
                <div class="mb-3">
                    <label for="actionTaken" class="form-label required-field">Action Taken</label>
                    <textarea class="form-control" id="actionTaken" name="actionTaken" rows="4" required><?php echo h($actionTaken); ?></textarea>
                    <div class="form-text">Describe the steps taken to address the issue.</div>
                </div>
                
                <div class="mb-3">
                    <label for="recommendation" class="form-label required-field">Recommendation</label>
                    <textarea class="form-control" id="recommendation" name="recommendation" rows="4" required><?php echo h($recommendation); ?></textarea>
                    <div class="form-text">Provide recommendations for future handling of similar issues.</div>
                </div>
                
                <div class="mb-3">
                    <label for="attachments" class="form-label">Attachments (Optional)</label>
                    <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
                    <div class="form-text">Upload any relevant documents (PDF, DOCX, JPG, PNG). Max size: 5MB each.</div>
                </div>
                
                <div class="nav-buttons">
                    <a href="admin-dash.php" class="btn btn-secondary">Back to Dashboard</a>
                    <div>
                        <button type="button" class="btn btn-outline-secondary me-2" id="saveAsDraft">Save as Draft</button>
                        <button type="submit" name="action_sub" class="btn btn-primary">Submit Action</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
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
                alert('Please fill in all required fields.');
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
                if (data.success) {
                    alert('Draft saved successfully!');
                } else {
                    alert('Failed to save draft: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the draft.');
            });
        });
    });
    </script>
</body>
</html>