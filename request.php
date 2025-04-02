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
    <title>DENR Admin - Request Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a5632; /* DENR green */
            --secondary-color: #343a40;
            --light-color: #f8f9fa;
            --accent-color: #ffc107;
        }
        
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: 600;
        }
        
        .navbar-brand img {
            width: 40px;
            margin-right: 10px;
        }
        
        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--accent-color) !important;
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
        
        .table {
            margin-bottom: 0;
        }
        
        .table-primary {
            background-color: #c2e0d3;
        }
        
        .table-success {
            background-color: #d1e7dd;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #134226;
            border-color: #134226;
        }
        
        .table th {
            white-space: nowrap;
        }
        
        .badge {
            font-size: 0.85rem;
            padding: 0.35em 0.65em;
        }
        
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        
        .badge-completed {
            background-color: #198754;
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 8px 8px 0 0;
        }
        
        .modal-title {
            font-weight: 600;
        }
        
        .modal-footer {
            border-top: 1px solid #dee2e6;
            padding: 1rem;
        }
        
        .request-info {
            border-left: 4px solid var(--primary-color);
            padding-left: 15px;
            margin-bottom: 15px;
        }
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
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Log Out</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <!-- Pending Requests -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-clock me-2"></i> Pending Requests
                </div>
                <span class="badge bg-warning text-dark">
                    <?php
                        $count_query = mysqli_query($con, "SELECT COUNT(*) as total FROM `reqform` WHERE `status`='Pending'");
                        $count_result = mysqli_fetch_assoc($count_query);
                        echo $count_result['total'];
                    ?>
                </span>
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
                                <th scope="col">Status</th>
                                <th scope="col" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $sql = mysqli_query($con, "SELECT * FROM `reqform` WHERE `status`='Pending' ORDER BY `controlnumber` ASC");
                                while($fetch = mysqli_fetch_assoc($sql)){
                                    $controlnumber = $fetch['controlnumber'];
                                    $reqp = $fetch['ReqPersonel'];
                                    $cmail = $fetch['Email'];
                                    $div = $fetch['Division'];
                                    $reqt = $fetch['ReqType'];
                                    $brandm = $fetch['BrandModel'];
                                    $SerNo = $fetch['SerialNo'];
                                    $PropNo = $fetch['PropertyNo'];
                                    $jobdesc = $fetch['JobDescription'];
                                    $date = $fetch['date_filed'];
                                    $status = $fetch['status'];
                                    $pdate = $fetch['p_date']; // New field
                                    $resperson = $fetch['resperson']; // New field
                                    
                                    echo "<tr>
                                            <td>{$fetch['controlnumber']}</td>
                                            <td>{$fetch['ReqPersonel']}</td>
                                            <td>{$fetch['Division']}</td>
                                            <td>{$fetch['ReqType']}</td>
                                            <td>" . date("M d, Y", strtotime($fetch['date_filed'])) . "</td>
                                            <td><span class='badge badge-pending'>Pending</span></td>
                                            <td class='text-center'>
                                                <button type='button' class='btn btn-sm btn-primary' data-bs-toggle='modal' data-bs-target='#viewModal' 
                                                data-controlnumber='$controlnumber' 
                                                data-reqp='$reqp' 
                                                data-cmail='$cmail' 
                                                data-div='$div'
                                                data-reqt='$reqt'
                                                data-brandm='$brandm' 
                                                data-serno='$SerNo' 
                                                data-propno='$PropNo' 
                                                data-jobdesc='$jobdesc' 
                                                data-date='$date'
                                                data-pdate='$pdate'
                                                data-resperson='$resperson'>
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
        
        <!-- Completed Requests (Similar modifications for this section) -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-check-circle me-2"></i> Completed Requests
                </div>
                <span class="badge bg-success">
                    <?php
                        $count_query = mysqli_query($con, "SELECT COUNT(*) as total FROM `reqform` WHERE `status`='Done'");
                        $count_result = mysqli_fetch_assoc($count_query);
                        echo $count_result['total'];
                    ?>
                </span>
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
                                <th scope="col">Status</th>
                                <th scope="col" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $sql = mysqli_query($con, "SELECT * FROM `reqform` WHERE `status`='Done' ORDER BY `controlnumber` ASC");
                                while($fetch = mysqli_fetch_assoc($sql)){
                                    $controlnumber = $fetch['controlnumber'];
                                    $reqp = $fetch['ReqPersonel'];
                                    $cmail = $fetch['Email'];
                                    $div = $fetch['Division'];
                                    $reqt = $fetch['ReqType'];
                                    $brandm = $fetch['BrandModel'];
                                    $SerNo = $fetch['SerialNo'];
                                    $PropNo = $fetch['PropertyNo'];
                                    $jobdesc = $fetch['JobDescription'];
                                    $date = $fetch['date_filed'];
                                    $pdate = $fetch['p_date']; // New field
                                    $resperson = $fetch['resperson']; // New field
                                    
                                    echo "<tr>
                                            <td>{$fetch['controlnumber']}</td>
                                            <td>{$fetch['ReqPersonel']}</td>
                                            <td>{$fetch['Division']}</td>
                                            <td>{$fetch['ReqType']}</td>
                                            <td>" . date("M d, Y", strtotime($fetch['date_filed'])) . "</td>
                                            <td><span class='badge badge-completed'>Completed</span></td>
                                            <td class='text-center'>
                                                <button type='button' class='btn btn-sm btn-outline-primary' data-bs-toggle='modal' data-bs-target='#viewModal' 
                                                data-controlnumber='$controlnumber' 
                                                data-reqp='$reqp' 
                                                data-cmail='$cmail' 
                                                data-div='$div'
                                                data-reqt='$reqt'
                                                data-brandm='$brandm' 
                                                data-serno='$SerNo' 
                                                data-propno='$PropNo' 
                                                data-jobdesc='$jobdesc' 
                                                data-date='$date'
                                                data-pdate='$pdate'
                                                data-resperson='$resperson'>
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
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel"><i class="fas fa-info-circle me-2"></i>Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="request-info">
                        <p class="mb-2"><strong><i class="fas fa-hashtag me-2"></i>Control Number:</strong> <span id="modal-controlnumber"></span></p>
                        <p class="mb-2"><strong><i class="fas fa-user me-2"></i>Requesting Personnel:</strong> <span id="modal-reqp"></span></p>
                        <p class="mb-2"><strong><i class="fas fa-envelope me-2"></i>Email:</strong> <span id="modal-cmail"></span></p>
                        <p class="mb-2"><strong><i class="fas fa-building me-2"></i>Division:</strong> <span id="modal-div"></span></p>
                        <p class="mb-2"><strong><i class="fas fa-clipboard-list me-2"></i>Request Type:</strong> <span id="modal-reqt"></span></p>
                    </div>
                    
                    <div class="request-info">
                        <p class="mb-2"><strong><i class="fas fa-laptop me-2"></i>Brand/Model:</strong> <span id="modal-brandm"></span></p>
                        <p class="mb-2"><strong><i class="fas fa-barcode me-2"></i>Serial Number:</strong> <span id="modal-serno"></span></p>
                        <p class="mb-2"><strong><i class="fas fa-tag me-2"></i>Property Number:</strong> <span id="modal-propno"></span></p>
                        <p class="mb-2"><strong><i class="fas fa-calendar-check me-2"></i>Purchase Date:</strong> <span id="modal-pdate"></span></p>
                        <p class="mb-2"><strong><i class="fas fa-user-shield me-2"></i>Responsible Person:</strong> <span id="modal-resperson"></span></p>
                    </div>
                    
                    <div class="request-info">
                        <p class="mb-2"><strong><i class="fas fa-tasks me-2"></i>Job Description:</strong></p>
                        <p class="p-2 bg-light rounded" id="modal-jobdesc"></p>
                        <p class="mb-0"><strong><i class="fas fa-calendar-alt me-2"></i>Date Filed:</strong> <span id="modal-date"></span></p>
                    </div>
                </div>
                <div class="modal-footer">
    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
        <i class="fas fa-times me-1"></i> Close
    </button>
    <a id="print-link" href="#"> 
        <button type="button" class="btn btn-warning d-print-none">
            <i class="fas fa-print me-1"></i> Print
        </button>
    </a>
    <form action="action.php" method="POST">
        <input type="hidden" name="controlnumber" id="action-controlnumber">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-check me-1"></i> Process Request
        </button>
    </form>
</div>
            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
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

    




        var modalControlNumber = viewModal.querySelector('#modal-controlnumber');
        var modalReqP = viewModal.querySelector('#modal-reqp');
        var modalCmail = viewModal.querySelector('#modal-cmail');
        var modalDiv = viewModal.querySelector('#modal-div');
        var modalReqt = viewModal.querySelector('#modal-reqt');
        var modalBrandM = viewModal.querySelector('#modal-brandm');
        var modalSerNo = viewModal.querySelector('#modal-serno');
        var modalPropNo = viewModal.querySelector('#modal-propno');
        var modalJobDesc = viewModal.querySelector('#modal-jobdesc');
        var modalDate = viewModal.querySelector('#modal-date');
        var modalPDate = viewModal.querySelector('#modal-pdate'); // New field
        var modalRespPerson = viewModal.querySelector('#modal-resperson'); // New field

        var actionControlNumber = viewModal.querySelector('#action-controlnumber');

        modalControlNumber.textContent = controlnumber;
        modalReqP.textContent = reqp;
        modalCmail.textContent = cmail;
        modalDiv.textContent = div;
        modalReqt.textContent = reqt;
        modalBrandM.textContent = brandm;
        modalSerNo.textContent = serno;
        modalPropNo.textContent = propno;
        modalJobDesc.textContent = jobdesc;
        modalDate.textContent = new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        var printLink = viewModal.querySelector('#print-link');
    printLink.href = 'print-view.php?id=' + controlnumber;

    if (isPending) {
        printLink.style.display = 'none';
    } else {
        printLink.style.display = 'inline-block';
        printLink.href = 'print-view.php?id=' + controlnumber;
    }

        // Handle new fields
        modalPDate.textContent = pdate ? new Date(pdate).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : 'N/A';
        modalRespPerson.textContent = resperson || 'Not specified';

        actionControlNumber.value = controlnumber;
    });
    </script>

</body>
</html>