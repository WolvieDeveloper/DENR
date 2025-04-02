<?php
include_once "sql.php";
session_start();

// Check if the session ID is set
if (!isset($_SESSION['id'])) {
    header("location:admin-login.php?notif=SessionExpired");
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
    header("location:admin-login.html?notif=UserNotFound");
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
    <title>View</title>
    <style>
        .main {
            margin: 20px;
            width: 80%;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    
<nav class="navbar navbar-expand-lg bg-body-tertiary bg-dark border-bottom border-body sticky-top" data-bs-theme="dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="admin-dash.php"><img src="denr.png" alt="" width="40px"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="admin-dash.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="logout.php">Log Out</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<div class="main">
    <h2 class="text-white">Pending Requests</h2>
    <table class="table table-dark table-striped">
        <tr class="table-primary">
            <th scope="col">Control#</th>
            <th scope="col">Requesting Personnel</th>
            <th scope="col">Division</th>
            <th scope="col">Request Type</th>
            <th scope="col">Date</th>
            <th scope="col">Status</th>
            <th scope="col">View</th>
        </tr>
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
                echo "<tr>
                        <td>{$fetch['controlnumber']}</td>
                        <td>{$fetch['ReqPersonel']}</td>
                        <td>{$fetch['Division']}</td>
                        <td>{$fetch['ReqType']}</td>
                        <td>{$fetch['date_filed']}</td>
                        <td>{$fetch['status']}</td>
                        <td><button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#viewModal' data-controlnumber='$controlnumber' data-reqp='$reqp' data-cmail='$cmail' data-brandm='$brandm' data-serno='$SerNo' data-propno='$PropNo' data-jobdesc='$jobdesc' data-date='$date'>View</button></td>
                      </tr>";
            }
        ?>
    </table>
    
    <h2 class="text-white">Completed Requests</h2>
    <table class="table table-dark table-striped">
        <tr class="table-success">
            <th scope="col">Control#</th>
            <th scope="col">Requesting Personnel</th>
            <th scope="col">Division</th>
            <th scope="col">Request Type</t
            <th scope="col">Date</th>
            <th scope="col">Status</th>
            <th scope="col">View</th>
        </tr>
        <?php
            $sql = mysqli_query($con, "SELECT * FROM `reqform` WHERE `status`='Done' ORDER BY `controlnumber` ASC");
            while($fetch = mysqli_fetch_assoc($sql)){
                echo "<tr>
                        <td>{$fetch['controlnumber']}</td>
                        <td>{$fetch['ReqPersonel']}</td>
                        <td>{$fetch['Division']}</td>
                        <td>{$fetch['ReqType']}</td>
                        <td>{$fetch['date_filed']}</td>
                        <td>{$fetch['status']}</td>
                        <td><button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#viewModal' data-controlnumber='$controlnumber' data-reqp='$reqp' data-cmail='$cmail' data-brandm='$brandm' data-serno='$SerNo' data-propno='$PropNo' data-jobdesc='$jobdesc' data-date='$date'>View</button></td>
                      </tr>";
            }
        ?>
    </table>
</div>


<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Control Number:</strong> <span id="modal-controlnumber"></span></p>
                    <p><strong>Requesting Personnel:</strong> <span id="modal-reqp"></span></p>
                    <p><strong>Email:</strong> <span id="modal-cmail"></span></p>
                    <p><strong>Brand/Model:</strong> <span id="modal-brandm"></span></p>
                    <p><strong>Serial Number:</strong> <span id="modal-serno"></span></p>
                    <p><strong>Property Number:</strong> <span id="modal-propno"></span></p>
                    <p><strong>Job Description:</strong> <span id="modal-jobdesc"></span></p>
                    <p><strong>Date:</strong> <span id="modal-date"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <form action="action.php" method="POST">
                        <input type="hidden" name="controlnumber" id="action-controlnumber">
                        <button type="submit" class="btn btn-primary">Action</button>
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
            var brandm = button.getAttribute('data-brandm');
            var serno = button.getAttribute('data-serno');
            var propno = button.getAttribute('data-propno');
            var jobdesc = button.getAttribute('data-jobdesc');
            var date = button.getAttribute('data-date');

            var modalControlNumber = viewModal.querySelector('#modal-controlnumber');
            var modalReqP = viewModal.querySelector('#modal-reqp');
            var modalCmail = viewModal.querySelector('#modal-cmail');
            var modalBrandM = viewModal.querySelector('#modal-brandm');
            var modalSerNo = viewModal.querySelector('#modal-serno');
            var modalPropNo = viewModal.querySelector('#modal-propno');
            var modalJobDesc = viewModal.querySelector('#modal-jobdesc');
            var modalDate = viewModal.querySelector('#modal-date');

            var actionControlNumber = viewModal.querySelector('#action-controlnumber');

            modalControlNumber.textContent = controlnumber;
            modalReqP.textContent = reqp;
            modalCmail.textContent = cmail;
            modalBrandM.textContent = brandm;
            modalSerNo.textContent = serno;
            modalPropNo.textContent = propno;
            modalJobDesc.textContent = jobdesc;
            modalDate.textContent = date;

            actionControlNumber.value = controlnumber;
        });
    </script>

</body>
</html>