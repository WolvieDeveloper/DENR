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
         :root {
            --primary-color: #157347; /* Define your primary color here */
        }
        
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
        
        .search-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .search-title {
            margin-bottom: 15px;
            color: #333;
        }
    </style>
</head>
<body> <nav class="navbar navbar-expand-lg sticky-top" data-bs-theme="dark">
  <div class="container">
    <a class="navbar-brand" href="index.html">
        <img src="denr.png" alt="DENR Logo" width="40px">
        <span>Department of Environment and Natural Resources</span>
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
        <h1 class="text-center mb-4">Request Status</h1>
        
        <!-- Search Form -->
        <div class="row justify-content-center no-print">
            <div class="col-md-8">
                <div class="search-container">
                    <h4 class="search-title text-center">Search For Your Request</h4>
                    <form method="GET" action="" class="mb-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <select name="search_type" class="form-select">
                                    <option value="controlnumber" <?php echo (isset($_GET['search_type']) && $_GET['search_type'] == 'controlnumber') ? 'selected' : ''; ?>>Control Number</option>
                                    <!-- <option value="name" <?php echo (isset($_GET['search_type']) && $_GET['search_type'] == 'ReqPersonel') ? 'selected' : ''; ?>>Name</option> -->
                                    <option value="ReqPersonel" <?php echo (isset($_GET['search_type']) && $_GET['search_type'] == 'ReqPersonel') ? 'selected' : ''; ?>>Name</option>
                                    <!-- <option value="ReqType" <?php echo (isset($_GET['search_type']) && $_GET['search_type'] == 'ReqType') ? 'selected' : ''; ?>>Request Type</option> -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="search_term" class="form-control" placeholder="Enter search term" value="<?php echo isset($_GET['search_term']) ? htmlspecialchars($_GET['search_term']) : ''; ?>" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>
                    <p class="text-center text-muted small">Don't know your control number? You can search by your name, request type, or personnel instead.</p>
                </div>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-md-10">
                <?php
                // Check if search was performed
                if (isset($_GET['search_term']) && !empty($_GET['search_term'])) {
                    $search_term = mysqli_real_escape_string($con, $_GET['search_term']);
                    $search_type = isset($_GET['search_type']) ? $_GET['search_type'] : 'controlnumber';
                    
                    // Validate search type
                    $valid_search_types = ['controlnumber', 'name', 'ReqPersonel', 'ReqType'];
                    if (!in_array($search_type, $valid_search_types)) {
                        $search_type = 'controlnumber'; // Default to control number if invalid
                    }
                    
                    $query = "SELECT * FROM reqform WHERE $search_type LIKE '%$search_term%' ORDER BY date_filed DESC";
                    $search_type_display = str_replace(['controlnumber', 'ReqPersonel', 'ReqType'], ['Control Number', 'Personnel', 'Request Type'], $search_type);
                    
                    echo '<div class="alert alert-info no-print">Showing results for ' . htmlspecialchars($search_type_display) . ': "' . htmlspecialchars($search_term) . '"</div>';
                    
                    $result = mysqli_query($con, $query);
                    
                    // Check if there are any results
                    if (mysqli_num_rows($result) > 0) {
                    ?>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Control Number</th>
                                <th>Request Type</th>
                                <th>Request Person</th>
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
                                echo "<td>" . $row['ReqType'] . "</td>";
                                echo "<td>" . (isset($row['ReqPersonel']) ? $row['ReqPersonel'] : 'N/A') . "</td>";
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
                        echo '<div class="alert alert-warning">No requests found matching your search criteria.</div>';
                    }
                } else {
                    // No search performed, display instructions
                    echo '<div class="alert alert-info text-center">Please use the search form above to find your request status.</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>