<?php
include_once "sql.php";
session_start();

// Check if the session ID is set
if (!isset($_SESSION['id'])) {
    header("location:admin-login.php?notif=SessionExpired");
    exit;
}

// Get report parameters
$searchTerm = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$filterBy = isset($_GET['filter']) ? mysqli_real_escape_string($con, $_GET['filter']) : '';
$dateFrom = isset($_GET['date_from']) ? mysqli_real_escape_string($con, $_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? mysqli_real_escape_string($con, $_GET['date_to']) : '';
$reportFormat = isset($_GET['format']) ? $_GET['format'] : 'pdf';
$reportTitle = isset($_GET['title']) ? $_GET['title'] : 'DENR Completed Requests Report';
$includeSummary = isset($_GET['include_summary']) ? true : false;
$includeCharts = isset($_GET['include_charts']) ? true : false;
$includeFilters = isset($_GET['include_filters']) ? true : false;

// Get the current date for the filename
$currentDate = date('Y-m-d');
$fileTitle = preg_replace('/[^A-Za-z0-9_-]/', '_', $reportTitle) . '_' . $currentDate;

// =========================================
// Data Collection for Report
// =========================================

// Main data query for completed requests
$mainQuery = "SELECT r.control_no, r.ReqPersonel as name, r.ReqType as request_type, r.date_filed as date_created, 
         a.action_date, a.action_taken, a.remarks, r.id as reqform_id
         FROM actions a 
         JOIN reqform r ON a.reqform_id = r.id";

// Add WHERE clause conditions based on filters
$whereConditions = [];

if (!empty($searchTerm)) {
    $whereConditions[] = "(r.ReqPersonel LIKE '%$searchTerm%' OR r.control_no LIKE '%$searchTerm%' OR r.ReqType LIKE '%$searchTerm%')";
}

if (!empty($filterBy)) {
    $whereConditions[] = "r.ReqType = '$filterBy'";
}

if (!empty($dateFrom) && !empty($dateTo)) {
    $whereConditions[] = "(a.action_date BETWEEN '$dateFrom' AND '$dateTo')";
} else if (!empty($dateFrom)) {
    $whereConditions[] = "a.action_date >= '$dateFrom'";
} else if (!empty($dateTo)) {
    $whereConditions[] = "a.action_date <= '$dateTo'";
}

// Combine WHERE conditions if any exist
if (!empty($whereConditions)) {
    $mainQuery .= " WHERE " . implode(" AND ", $whereConditions);
}

// Complete the query with ORDER BY
$mainQuery .= " ORDER BY a.action_date DESC";
$mainResult = mysqli_query($con, $mainQuery);

// Check if we have data
$totalRecords = mysqli_num_rows($mainResult);
if ($totalRecords == 0) {
    header("location:completed.php?notif=NoDataFound");
    exit;
}

// =========================================
// Generate Statistical Data For Summary
// =========================================
$statsData = [];

if ($includeSummary) {
    // Request types distribution
    $typeQuery = "SELECT r.ReqType as type, COUNT(*) as count 
                  FROM actions a 
                  JOIN reqform r ON a.reqform_id = r.id";
    
    if (!empty($whereConditions)) {
        $typeQuery .= " WHERE " . implode(" AND ", $whereConditions);
    }
    
    $typeQuery .= " GROUP BY r.ReqType ORDER BY count DESC";
    $typeResult = mysqli_query($con, $typeQuery);
    
    $requestTypes = [];
    while ($row = mysqli_fetch_assoc($typeResult)) {
        $requestTypes[$row['type']] = $row['count'];
    }
    $statsData['requestTypes'] = $requestTypes;
    
    // Monthly trend data
    $trendQuery = "SELECT DATE_FORMAT(a.action_date, '%Y-%m') as month, COUNT(*) as count 
                   FROM actions a 
                   JOIN reqform r ON a.reqform_id = r.id";
    
    if (!empty($whereConditions)) {
        $trendQuery .= " WHERE " . implode(" AND ", $whereConditions);
    }
    
    $trendQuery .= " GROUP BY DATE_FORMAT(a.action_date, '%Y-%m') ORDER BY month ASC";
    $trendResult = mysqli_query($con, $trendQuery);
    
    $monthlyTrend = [];
    while ($row = mysqli_fetch_assoc($trendResult)) {
        $monthName = date('M Y', strtotime($row['month'] . '-01'));
        $monthlyTrend[$monthName] = $row['count'];
    }
    $statsData['monthlyTrend'] = $monthlyTrend;
    
    // Processing time statistics
    $timeQuery = "SELECT 
                    AVG(DATEDIFF(a.action_date, r.date_filed)) as avg_processing_time,
                    MIN(DATEDIFF(a.action_date, r.date_filed)) as min_processing_time,
                    MAX(DATEDIFF(a.action_date, r.date_filed)) as max_processing_time
                  FROM actions a 
                  JOIN reqform r ON a.reqform_id = r.id";
    
    if (!empty($whereConditions)) {
        $timeQuery .= " WHERE " . implode(" AND ", $whereConditions);
    }
    
    $timeResult = mysqli_query($con, $timeQuery);
    $timeStats = mysqli_fetch_assoc($timeResult);
    $statsData['processingTime'] = $timeStats;
}

// =========================================
// Generate PDF Report
// =========================================
if ($reportFormat == 'pdf') {
    // Require mPDF library 
    require_once 'vendor/autoload.php';
    
    // Initialize PDF with custom settings for a professional report
    $mpdf = new \Mpdf\Mpdf([
        'margin_top' => 30,
        'margin_bottom' => 30,
        'margin_left' => 15,
        'margin_right' => 15
    ]);
    
    // Set document metadata
    $mpdf->SetTitle($reportTitle);
    $mpdf->SetAuthor('DENR Admin Portal');
    $mpdf->SetCreator('DENR Report Generator');
    
    // Add custom header with logo
    $header = '
    <div style="text-align: center; border-bottom: 1px solid #dddddd; padding-bottom: 10px;">
        <table width="100%">
            <tr>
                <td width="15%" style="text-align: left;">
                    <img src="denr.png" width="60">
                </td>
                <td width="70%" style="text-align: center;">
                    <h2 style="margin: 0; color: #2d6a4f;">Department of Environment and Natural Resources</h2>
                    <h3 style="margin: 5px 0; color: #40916c;">' . htmlspecialchars($reportTitle) . '</h3>
                </td>
                <td width="15%" style="text-align: right; font-size: 9pt; color: #666;">
                    Generated on: ' . date('F d, Y') . '<br>
                    Time: ' . date('h:i A') . '
                </td>
            </tr>
        </table>
    </div>
    ';
    $mpdf->SetHTMLHeader($header);
    
    // Set footer with page numbers
    $footer = '
    <div style="text-align: center; font-size: 9pt; color: #666; border-top: 1px solid #dddddd; padding-top: 5px;">
        <table width="100%">
            <tr>
                <td width="33%" style="text-align: left;">DENR Admin Portal</td>
                <td width="33%" style="text-align: center;">Confidential - For Internal Use</td>
                <td width="33%" style="text-align: right;">Page {PAGENO} of {nb}</td>
            </tr>
        </table>
    </div>
    ';
}
    $mpdf->SetHTMLFooter($footer);
    