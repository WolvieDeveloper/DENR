<?php
include_once "sql.php";
session_start();

// Check if the session ID is set
if (!isset($_SESSION['id'])) {
    header("location:admin-login.html?notif=SessionExpired");
    exit;
}

$sessid = intval($_SESSION['id']);
$query = "SELECT user_type FROM login WHERE id = ?";
$stmt = mysqli_prepare($con, $query);

// Bind the parameter and execute the query
mysqli_stmt_bind_param($stmt, "i", $sessid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$fetch = mysqli_fetch_assoc($result);

if ($fetch) {
    $usertype = $fetch['user_type'];
    if ($usertype != 1) {
        header("location:index.html?notif=Restricted");
        exit;
    }
} else {
    header("location:login.html?notif=UserNotFound");
    exit;
}

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="DENR.png" />
    <title>DENR Admin - Pending Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a5632;
            --secondary-color: #343a40;
            --light-color: #f8f9fa;
            --accent-color: #ffc107;
            --priority-color: #dc3545;
        }
        
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container-fluid {
            padding: 25px 30px;
        }
        
        .card {
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border-radius: 8px 8px 0 0 !important;
            padding: 12px 20px;
        }
        
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        
        .badge-priority {
            background-color: var(--priority-color);
            color: white;
        }
        
        .priority-row {
            background-color: rgba(220, 53, 69, 0.1);
        }
        
        .priority-info {
            border-left: 4px solid var(--priority-color);
        }
        
        .days-normal {
            color: #6c757d;
        }
        
        .days-priority {
            color: var(--priority-color);
        }
    </style>
</head>
<body>
    <?php include_once "navbar.php"; ?>
    
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-clock me-2"></i> Pending Requests
                </div>
                <div>
                    <span class="badge bg-warning text-dark me-2">
                        Regular: 
                        <?php
                            $now = new DateTime();
                            $three_days_ago = (clone $now)->modify('-3 days');
                            $count_normal_query = mysqli_query($con, "SELECT COUNT(*) as total FROM `temp-reqform` WHERE `status`='Pending' AND `date_filed` > '".$three_days_ago->format('Y-m-d')."'");
                            $count_normal_result = mysqli_fetch_assoc($count_normal_query);
                            echo $count_normal_result['total'];
                        ?>
                    </span>
                    <span class="badge bg-danger">
                        Priority: 
                        <?php
                            $count_priority_query = mysqli_query($con, "SELECT COUNT(*) as total FROM `temp-reqform` WHERE `status`='Pending' AND `date_filed` <= '".$three_days_ago->format('Y-m-d')."'");
                            $count_priority_result = mysqli_fetch_assoc($count_priority_query);
                            echo $count_priority_result['total'];
                        ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Requester</th>
                                <th scope="col">Division</th>
                                <th scope="col">Request Type</th>
                                <th scope="col">Date Filed</th>
                                <th scope="col">Wait Time</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $now = new DateTime();
                                $sql = mysqli_query($con, "SELECT *, DATEDIFF(NOW(), date_filed) as days_waiting FROM `temp-reqform` WHERE `status`='Pending' ORDER BY days_waiting DESC, `controlnumber` ASC");
                                while($fetch = mysqli_fetch_assoc($sql)){
                                    $days_waiting = $fetch['days_waiting'];
                                    $is_priority = $days_waiting > 3;
                                    $row_class = $is_priority ? 'priority-row' : '';
                                    $badge_class = $is_priority ? 'badge-priority' : 'badge-pending';
                                    $badge_text = $is_priority ? 'Priority' : 'Pending';
                                    $days_class = $is_priority ? 'days-priority' : 'days-normal';
                                    
                                    echo "<tr class='$row_class'>
                                            <td>{$fetch['controlnumber']}</td>
                                            <td>{$fetch['ReqPersonel']}</td>
                                            <td>{$fetch['Division']}</td>
                                            <td>{$fetch['ReqType']}</td>
                                            <td>" . date("M d, Y", strtotime($fetch['date_filed'])) . "</td>
                                            <td><span class='$days_class'>{$days_waiting} day" . ($days_waiting != 1 ? "s" : "") . "</span></td>
                                            <td><span class='badge $badge_class'>$badge_text</span></td>
                                            <td class='text-center'>
                                                <button type='button' class='btn btn-sm " . ($is_priority ? "btn-danger" : "btn-primary") . "' data-bs-toggle='modal' data-bs-target='#viewModal' 
                                                data-controlnumber='{$fetch['controlnumber']}' 
                                                data-reqp='{$fetch['ReqPersonel']}' 
                                                data-cmail='{$fetch['Email']}' 
                                                data-div='{$fetch['Division']}'
                                                data-reqt='{$fetch['ReqType']}'
                                                data-brandm='{$fetch['BrandModel']}' 
                                                data-serno='{$fetch['SerialNo']}' 
                                                data-propno='{$fetch['PropertyNo']}' 
                                                data-jobdesc='{$fetch['JobDescription']}' 
                                                data-date='{$fetch['date_filed']}'
                                                data-pdate='{$fetch['p_date']}'
                                                data-resperson='{$fetch['resperson']}'
                                                data-priority='$is_priority'
                                                data-days='$days_waiting'>
                                                <i class='fas fa-eye me-1'></i> View
                                                </button>
                                            </td>
                                          </tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" id="modal-header">
                    <h5 class="modal-title" id="modalLabel"><i class="fas fa-info-circle me-2"></i>Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="priority-alert" class="alert alert-danger d-none mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i> This request has been pending for <span id="days-count"></span> days and requires immediate attention!
                    </div>
                    
                    <div class="request-info" id="req-info-1">
                        <p class="mb-2"><strong><i class="fas fa-hashtag me-2"></i>Control Number:</strong> <span id="modal-controlnumber"></span></p>
                        <p class="mb-2"><strong><i class="fas fa-user me-2"></i>Requesting Personnel:</strong> <span id="modal-reqp"></span></p>
                        <p class="mb-2"><strong><i class="fas fa-envelope me-2"></i>Email:</strong> <span id="modal-cmail"></span></p>
                        <p class="mb-2"><strong><i class="fas fa-building me-2"></i>Division:</strong> <span id="modal-div"></span></p>
                        <p class="mb-2"><strong><i class="fas fa-clipboard-list me-2"></i>Request Type:</strong> <span id="modal-reqt"></span></p>
                    </div>
                    
                    <div class="request-info" id="req-info-2">
                        <p class="mb-2"><strong><i class="fas fa-laptop me-2"></i>Brand/Model:</strong> <span id="modal-brandm"></span></p>
                        <p class="mb-2"><strong><i class="fas fa-barcode me-2"></i>Serial Number:</strong> <span id="modal-serno"></span></p>
                        <p class="mb-2"><strong><i class="fas fa-tag me-2"></i>Property Number:</strong> <span id="modal-propno"></span></p>
                        <p class="mb-2"><strong><i class="fas fa-calendar-check me-2"></i>Purchase Date:</strong> <span id="modal-pdate"></span></p>
                        <p class="mb-2"><strong><i class="fas fa-user-shield me-2"></i>Responsible Person:</strong> <span id="modal-resperson"></span></p>
                    </div>
                    
                    <div class="request-info" id="req-info-3">
                        <p class="mb-2"><strong><i class="fas fa-tasks me-2"></i>Job Description:</strong></p>
                        <p class="p-2 bg-light rounded" id="modal-jobdesc"></p>
                        <p class="mb-0"><strong><i class="fas fa-calendar-alt me-2"></i>Date Filed:</strong> <span id="modal-date"></span></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <form action="decline.php" method="POST" class="me-auto">
                        <input type="hidden" name="controlnumber" id="action-controlnumber-decline">
                        <button type="submit" class="btn btn-danger" id="decline-btn">
                            <i class="fas fa-times me-1"></i> Decline Request
                        </button>
                    </form>
                    <form action="accept.php" method="POST">
                        <input type="hidden" name="controlnumber" id="action-controlnumber-accept">
                        <button type="submit" class="btn btn-success" id="accept-btn">
                            <i class="fas fa-check me-1"></i> Accept Request
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var viewModal = document.getElementById('viewModal');
        viewModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var controlnumber = button.getAttribute('data-controlnumber');
            var reqp = button.getAttribute('data-reqp');
            var cmail = button.getAttribute('data-cmail');
            var div = button.getAttribute('data-div');
            var reqt = button.getAttribute('data-reqt');
            var brandm = button.getAttribute('data-brandm');
            var serno = button.getAttribute('data-serno');
            var propno = button.getAttribute('data-propno');
            var jobdesc = button.getAttribute('data-jobdesc');
            var date = button.getAttribute('data-date');
            var pdate = button.getAttribute('data-pdate');
            var resperson = button.getAttribute('data-resperson');
            var isPriority = button.getAttribute('data-priority') === 'true';
            var days = button.getAttribute('data-days');

            var modalHeader = viewModal.querySelector('#modal-header');
            var priorityAlert = viewModal.querySelector('#priority-alert');
            var daysCount = viewModal.querySelector('#days-count');
            var reqInfo1 = viewModal.querySelector('#req-info-1');
            var reqInfo2 = viewModal.querySelector('#req-info-2');
            var reqInfo3 = viewModal.querySelector('#req-info-3');

            var actionControlNumberDecline = viewModal.querySelector('#action-controlnumber-decline');
            var actionControlNumberAccept = viewModal.querySelector('#action-controlnumber-accept');

            if (isPriority) {
                modalHeader.classList.add('bg-danger');
                priorityAlert.classList.remove('d-none');
                daysCount.textContent = days;
                reqInfo1.classList.add('priority-info');
                reqInfo2.classList.add('priority-info');
                reqInfo3.classList.add('priority-info');
            } else {
                modalHeader.classList.remove('bg-danger');
                modalHeader.classList.add('bg-primary');
                priorityAlert.classList.add('d-none');
                reqInfo1.classList.remove('priority-info');
                reqInfo2.classList.remove('priority-info');
                reqInfo3.classList.remove('priority-info');
            }

            document.getElementById('modal-controlnumber').textContent = controlnumber;
            document.getElementById('modal-reqp').textContent = reqp;
            document.getElementById('modal-cmail').textContent = cmail;
            document.getElementById('modal-div').textContent = div;
            document.getElementById('modal-reqt').textContent = reqt;
            document.getElementById('modal-brandm').textContent = brandm;
            document.getElementById('modal-serno').textContent = serno;
            document.getElementById('modal-propno').textContent = propno;
            document.getElementById('modal-jobdesc').textContent = jobdesc;
            document.getElementById('modal-date').textContent = new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            document.getElementById('modal-pdate').textContent = pdate ? new Date(pdate).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }) : 'N/A';
            
            document.getElementById('modal-resperson').textContent = resperson || 'Not specified';

            actionControlNumberDecline.value = controlnumber;
            actionControlNumberAccept.value = controlnumber;
        });
    </script>
</body>
</html>