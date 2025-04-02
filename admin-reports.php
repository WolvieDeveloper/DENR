<?php
// Include database connection
session_start();
include 'sql.php';

if (!isset($_SESSION['id'])) {
    header("location:admin-login.php?notif=SessionExpired");
    exit;
}
// Execute the query
$query = "SELECT 
        reqform.*, 
        actions.initialFindings, 
        actions.actionTaken, 
        actions.recommendation,
        actions.date_finished,
        feedback.comment, 
        feedback.rating, 
        feedback.datefeedbacked
    FROM reqform
    JOIN actions ON reqform.controlnumber = actions.controlnumber
    JOIN feedback ON reqform.controlnumber = feedback.controlnumber";

$result = mysqli_query($con, $query);

// Check if query was successful
if (!$result) {
    die("Query failed: " . mysqli_error($con));
}

// Array to store monthly ratings
$monthlyRatings = [];

// Arrays for chart data
$chartLabels = [];
$chartData = [];

// Get current sort order (default to chronological)
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'asc';
$nextSortOrder = ($sortOrder == 'asc') ? 'desc' : 'asc';

// Fetch all rows
$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
    
    // Extract month from datefeedbacked
    if (!empty($row['datefeedbacked'])) {
        $date = new DateTime($row['datefeedbacked']);
        $month = $date->format('Y-m');
        $monthDisplay = $date->format('M Y');
        $monthSortKey = $date->format('Ym'); // For easy sorting
        
        // Add rating to monthly array
        if (!isset($monthlyRatings[$month])) {
            $monthlyRatings[$month] = [
                'sum' => 0, 
                'count' => 0, 
                'display' => $monthDisplay,
                'sortKey' => $monthSortKey
            ];
        }
        $monthlyRatings[$month]['sum'] += $row['rating'];
        $monthlyRatings[$month]['count']++;
    }
}

// Calculate monthly averages
$monthlyAverages = [];
foreach ($monthlyRatings as $month => $data) {
    $monthlyAverages[$month] = [
        'average' => $data['sum'] / $data['count'],
        'count' => $data['count'],
        'display' => $data['display'],
        'sortKey' => $data['sortKey']
    ];
}

// Sort months based on user preference
if ($sortOrder == 'asc') {
    // Sort chronologically (ascending)
    ksort($monthlyAverages);
} else {
    // Sort reverse chronologically (descending)
    krsort($monthlyAverages);
}

// Prepare chart data after sorting
foreach ($monthlyAverages as $data) {
    $chartLabels[] = $data['display'];
    $chartData[] = round($data['average'], 2);
}

// Function to get all column names
function getColumnNames($result) {
    $columns = [];
    while ($fieldInfo = mysqli_fetch_field($result)) {
        $columns[] = $fieldInfo->name;
    }
    return $columns;
}

// Get column names
$columns = getColumnNames($result);

// Function to get a more readable column display name
function getDisplayName($column) {
    // Replace camelCase with spaces
    $name = preg_replace('/(?<!^)[A-Z]/', ' $0', $column);
    // Replace underscores with spaces
    $name = str_replace('_', ' ', $name);
    // Capitalize first letter of each word
    return ucwords($name);
}


$query2 = "select * from reqform";
$result2 = mysqli_query($con, $query2);
$totalRequests = mysqli_num_rows($result2);
// You can still fetch $row2 after if needed
$row2 = mysqli_fetch_assoc($result2);
$completedRequests = 0;
$avgRating = 0;
$ratingSum = 0;
$ratingCount = 0;

foreach ($rows as $row) {
    if (!empty($row['date_finished'])) {
        $completedRequests++;
    }
    
    if (!empty($row['rating'])) {
        $ratingSum += $row['rating'];
        $ratingCount++;
    }
}

$avgRating = $ratingCount > 0 ? $ratingSum / $ratingCount : 0;
$completionRate = $totalRequests > 0 ? ($completedRequests / $totalRequests) * 100 : 0;

// Function to get rating color
function getRatingColor($rating) {
    if ($rating >= 4.5) return '#28a745'; // Excellent - Green
    if ($rating >= 4.0) return '#5cb85c'; // Very Good - Light Green
    if ($rating >= 3.5) return '#ffc107'; // Good - Yellow
    if ($rating >= 3.0) return '#ff9800'; // Average - Orange
    return '#dc3545'; // Below Average - Red
}

// Function to get sort icon
function getSortIcon($currentOrder) {
    if ($currentOrder == 'asc') {
        return '<i class="fas fa-sort-up ms-1"></i>';
    } else {
        return '<i class="fas fa-sort-down ms-1"></i>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Management Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-bg: #f8f9fa;
            --dark-bg: #343a40;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .dashboard-header {
            background-color: var(--secondary-color);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .dashboard-title {
            font-weight: 300;
            margin: 0;
            padding: 0 20px;
        }
        
        .stats-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .stat-card {
            flex: 1;
            min-width: 200px;
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .stat-card .icon {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }
        
        .stat-card .stat-title {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .stat-card .stat-value {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            border-radius: 8px;
        }
        
        .card-header {
            background-color: var(--light-bg);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 600;
            padding: 15px 20px;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            background-color: var(--light-bg);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            border-top: none;
            white-space: nowrap;
        }
        
        .table td {
            vertical-align: middle;
            padding: 12px 15px;
            font-size: 0.9rem;
        }
        
        .rating-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: 600;
            color: white;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 30px;
        }
        
        .tabs-container {
            margin-bottom: 30px;
        }
        
        
        .navbar-brand {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .navbar-brand img {
            width: 40px;
            height: auto;
        }
        
        .nav-link {
            font-weight: 500;
            padding: 0.8rem 1rem !important;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
        
        
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        
        .form-control {
            border-radius: 4px;
            padding: 8px 12px;
            border: 1px solid #ced4da;
        }
        
        .search-container {
            margin-bottom: 20px;
        }
        
        .sort-link {
            cursor: pointer;
            color: var(--primary-color);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .sort-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .stats-container {
                flex-direction: column;
            }
            
            .stat-card {
                width: 100%;
                margin-bottom: 15px;
            }
            
            .table-responsive {
                border: none;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    
    <div class="container">
        <!-- Stats Overview -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="icon"><i class="fas fa-clipboard-list"></i></div>
                <div class="stat-title">Total Requests</div>
                <div class="stat-value"><?php echo $totalRequests; ?></div>
            </div>
            
            <div class="stat-card">
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-title">Completed Requests</div>
                <div class="stat-value"><?php echo $completedRequests; ?></div>
            </div>
            
            <div class="stat-card">
                <div class="icon"><i class="fas fa-percentage"></i></div>
                <div class="stat-title">Completion Rate</div>
                <div class="stat-value"><?php echo number_format($completionRate, 1); ?>%</div>
            </div>
            
            <div class="stat-card">
                <div class="icon"><i class="fas fa-star"></i></div>
                <div class="stat-title">Average Rating</div>
                <div class="stat-value" style="color: <?php echo getRatingColor($avgRating); ?>">
                    <?php echo number_format($avgRating, 1); ?>
                </div>
            </div>
        </div>
        
        <!-- Tabs Navigation -->
        <div class="tabs-container">
            <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="overview-tab" data-bs-toggle="tab" href="#overview" role="tab">Overview</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="data-tab" data-bs-toggle="tab" href="#data" role="tab">Full Data</a>
                </li>
            </ul>
            
            <div class="tab-content" id="dashboardTabsContent">
                <!-- Overview Tab -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    <!-- Monthly Ratings Chart -->
                    <div class="card mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Monthly Satisfaction Ratings</span>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="chartOptionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    Options
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="chartOptionsDropdown">
                                    <li><a class="dropdown-item" href="#" id="downloadChartBtn"><i class="fas fa-download me-2"></i>Download Chart</a></li>
                                    <li><a class="dropdown-item" href="#" id="refreshChartBtn"><i class="fas fa-sync-alt me-2"></i>Refresh Data</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="ratingsChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Monthly Ratings Table -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Monthly Average Ratings</span>
                            <a href="?sort=<?php echo $nextSortOrder; ?>" class="sort-link">
                                Sort by Month <?php echo getSortIcon($sortOrder); ?>
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>Average Rating</th>
                                            <th>Number of Ratings</th>
                                            <th>Performance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($monthlyAverages as $month => $data): ?>
                                        <tr>
                                            <td><?php echo $data['display']; ?></td>
                                            <td>
                                                <span class="rating-badge" style="background-color: <?php echo getRatingColor($data['average']); ?>">
                                                    <?php echo number_format($data['average'], 2); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $data['count']; ?></td>
                                            <td>
                                                <?php
                                                    if ($data['average'] >= 4.5) echo '<span class="text-success">Excellent</span>';
                                                    elseif ($data['average'] >= 4.0) echo '<span class="text-success">Very Good</span>';
                                                    elseif ($data['average'] >= 3.5) echo '<span class="text-warning">Good</span>';
                                                    elseif ($data['average'] >= 3.0) echo '<span class="text-warning">Average</span>';
                                                    else echo '<span class="text-danger">Below Average</span>';
                                                ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Full Data Tab -->
                <div class="tab-pane fade" id="data" role="tabpanel">
                    <!-- Search and Filters -->
                    <div class="search-container mt-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search requests...">
                                    <button class="btn btn-primary" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                <div class="btn-group me-2">
                                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><h6 class="dropdown-header">Filter by Month</h6></li>
                                        <?php 
                                        // Get unique months
                                        $uniqueMonths = [];
                                        foreach ($rows as $row) {
                                            if (!empty($row['datefeedbacked'])) {
                                                $date = new DateTime($row['datefeedbacked']);
                                                $monthKey = $date->format('M Y');
                                                $uniqueMonths[$monthKey] = true;
                                            }
                                        }
                                        
                                        // Output month filters
                                        foreach (array_keys($uniqueMonths) as $month) {
                                            echo '<li><a class="dropdown-item month-filter" href="#" data-month="' . $month . '">' . $month . '</a></li>';
                                        }
                                        ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item month-filter" href="#" data-month="all">Show All</a></li>
                                    </ul>
                                </div>
                                <button class="btn btn-outline-secondary">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- All Request Data -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>All Request Data</span>
                            <span id="filterIndicator" class="badge bg-primary d-none">Filtered</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover" id="requestDataTable">
                                    <thead>
                                        <tr>
                                            <?php foreach ($columns as $column): ?>
                                            <th><?php echo getDisplayName($column); ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rows as $row): ?>
                                        <tr data-month="<?php 
                                            // Add month data attribute for filtering
                                            if (!empty($row['datefeedbacked'])) {
                                                $date = new DateTime($row['datefeedbacked']);
                                                echo $date->format('M Y');
                                            }
                                        ?>">
                                            <?php foreach ($columns as $column): ?>
                                            <td>
                                                <?php 
                                                    $value = htmlspecialchars($row[$column] ?? '');
                                                    
                                                    // Format based on column type
                                                    if ($column === 'rating' && !empty($value)) {
                                                        echo '<span class="rating-badge" style="background-color: ' . 
                                                             getRatingColor($value) . '">' . $value . '</span>';
                                                    } else if (strpos($column, 'date') !== false && !empty($value)) {
                                                        $date = new DateTime($value);
                                                        echo $date->format('M d, Y');
                                                    } else {
                                                        echo $value;
                                                    }
                                                ?>
                                            </td>
                                            <?php endforeach; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                       
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        // Chart initialization
        document.addEventListener('DOMContentLoaded', function() {
            // Chart data
            const chartLabels = <?php echo json_encode($chartLabels); ?>;
            const chartData = <?php echo json_encode($chartData); ?>;
            
            const ctx = document.getElementById('ratingsChart').getContext('2d');
            const gradientFill = ctx.createLinearGradient(0, 0, 0, 400);
            gradientFill.addColorStop(0, 'rgba(52, 152, 219, 0.3)');
            gradientFill.addColorStop(1, 'rgba(52, 152, 219, 0.0)');
            
            const ratingsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Average Rating',
                        data: chartData,
                        borderColor: 'rgba(52, 152, 219, 1)',
                        backgroundColor: gradientFill,
                        tension: 0.3,
                        borderWidth: 3,
                        pointBackgroundColor: 'rgba(52, 152, 219, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: Math.min(...chartData) > 2 ? Math.floor(Math.min(...chartData)) - 1 : 0,
                            max: 5,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            padding: 10,
                            titleFont: {
                                size: 14
                            },
                            bodyFont: {
                                size: 14
                            },
                            callbacks: {
                                label: function(context) {
                                    return `Rating: ${context.parsed.y}`;
                                }
                            }
                        }
                    }
                }
            });
            
            // Simple search functionality
            $('#searchInput').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $('#requestDataTable tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
            
            // Month filtering
            $('.month-filter').on('click', function(e) {
                e.preventDefault();
                const month = $(this).data('month');
                const rows = $('#requestDataTable tbody tr');
                
                if (month === 'all') {
                    // Show all rows
                    rows.show();
                    $('#filterIndicator').addClass('d-none');
                } else {
                    // Filter by selected month
                    rows.hide();
                    rows.filter(`[data-month="${month}"]`).show();
                    $('#filterIndicator').removeClass('d-none');
                }
            });
            
            // Chart download functionality
            $('#downloadChartBtn').on('click', function(e) {
                e.preventDefault();
                
                // Get the canvas as an image
                const canvas = document.getElementById('ratingsChart');
                const image = canvas.toDataURL('image/png');
                
                // Create a temporary link and trigger download
                const link = document.createElement('a');
                link.href = image;
                link.download = 'monthly_ratings_chart.png';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
            
            // Refresh chart (simulated)
            $('#refreshChartBtn').on('click', function(e) {
                e.preventDefault();
                
                // Show loading effect
                $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...');
                
                // Simulate refresh with a timeout
                setTimeout(() => {
                    $(this).html('<i class="fas fa-sync-alt me-2"></i>Refresh Data');
                    alert('Chart data refreshed successfully!');
                }, 1500);
            });
        });
    </script>
</body>
</html>