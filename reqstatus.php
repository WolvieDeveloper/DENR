<?php 
include_once 'sql.php';
// include_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="DENR.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="style/reqstatus.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Status</title>
    <style>
        /* Print-only styles */


        .navbar-brand {
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        @media print {
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
        }
        .print-only {
            display: none;
        }
    </style>
</head>
<body> 
<nav class="navbar navbar-expand-lg sticky-top" data-bs-theme="dark">
  <div class="container">
    <a class="navbar-brand" href="index.html">
        <img src="denr.png" alt="DENR Logo" width="40px">
        <span>DENR ICT Services</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="index.html">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="reqstatus.php">Request Status</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Contact Support</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
    
    <div class="container mt-5">
        <h1 class="text-center">Request Status</h1>
        
        <!-- Search Form -->
        <div class="row justify-content-center mb-4 no-print">
            <div class="col-md-6">
                <form method="GET" action="" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search by Control Number" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php
                // Check if search was performed
                if (isset($_GET['search']) && !empty($_GET['search'])) {
                    $search = mysqli_real_escape_string($con, $_GET['search']);
                    $query = "SELECT * FROM reqform WHERE controlnumber LIKE '%$search%'";
                    echo '<div class="alert alert-info no-print">Showing results for control number: "' . htmlspecialchars($search) . '"</div>';
                } else {
                    $query = "SELECT * FROM reqform ORDER BY date_filed DESC";
                }
                
                $result = mysqli_query($con, $query);
                
                // Check if there are any results
                if (mysqli_num_rows($result) > 0) {
                ?>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Control Number</th>
                            <th>Request ID</th>
                            <th>Request Type</th>
                            <th>Status</th>
                            <th>Date Submitted</th>
                            <th class="no-print">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . (isset($row['controlnumber']) ? $row['controlnumber'] : 'N/A') . "</td>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . $row['ReqType'] . "</td>";
                            echo "<td>" . $row['status'] . "</td>";
                            echo "<td>" . $row['date_filed'] . "</td>";
                            echo "<td class='no-print'>";
                            if (strtolower($row['status']) === 'done') {
                                echo "<a href='print-view.php?id=" . $row['controlnumber'] . "' class='btn btn-success btn-sm'><i class='fas fa-print'></i> Print</a>";
                            } else {
                                echo "<span class='text-muted'><i class='fas fa-print'></i> Print</span>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <?php
                } else {
                    // No results found
                    echo '<div class="alert alert-warning no-print">No requests found with the specified control number.</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>