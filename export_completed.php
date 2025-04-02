<?php
include_once "sql.php";
session_start();

// Check if the session ID is set
if (!isset($_SESSION['id'])) {
    header("location:admin-login.php?notif=SessionExpired");
    exit;
}

// Get filter parameters
$searchTerm = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$filterBy = isset($_GET['filter']) ? mysqli_real_escape_string($con, $_GET['filter']) : '';
$dateFrom = isset($_GET['date_from']) ? mysqli_real_escape_string($con, $_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? mysqli_real_escape_string($con, $_GET['date_to']) : '';
$reportFormat = isset($_GET['format']) ? $_GET['format'] : 'csv';

// Start building the base query - Get all fields needed for the report
$query = "SELECT r.control_no, r.ReqPersonel as name, r.ReqType as request_type, r.date_filed as date_created, 
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
    $query .= " WHERE " . implode(" AND ", $whereConditions);
}

// Complete the query with ORDER BY
$query .= " ORDER BY a.action_date DESC";
$result = mysqli_query($con, $query);

// Get the current date for the filename
$currentDate = date('Y-m-d');
$fileTitle = "DENR_Completed_Requests_Report_" . $currentDate;

// Handle different export formats
switch($reportFormat) {
    case 'csv':
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $fileTitle . '.csv"');
        
        // Create file pointer connected to PHP output
        $output = fopen('php://output', 'w');
        
        // Write column headers
        fputcsv($output, ['Reference No', 'Requester', 'Request Type', 'Date Created', 'Date Actioned', 'Action Taken', 'Remarks']);
        
        // Write data rows
        while ($row = mysqli_fetch_assoc($result)) {
            $csvRow = [
                'REQ-' . str_pad($row['reqform_id'], 4, '0', STR_PAD_LEFT),
                $row['name'],
                $row['request_type'],
                date('M d, Y', strtotime($row['date_created'])),
                date('M d, Y', strtotime($row['action_date'])),
                $row['action_taken'],
                $row['remarks']
            ];
            fputcsv($output, $csvRow);
        }
        
        // Close file pointer
        fclose($output);
        break;
        
    case 'pdf':
        // Require mPDF library 
        // Note: You need to install this via Composer first
        // composer require mpdf/mpdf
        require_once 'vendor/autoload.php';
        
        // Initialize PDF
        $mpdf = new \Mpdf\Mpdf([
            'margin_top' => 25,
            'margin_bottom' => 25
        ]);
        
        // Set document metadata
        $mpdf->SetTitle('DENR Completed Requests Report');
        $mpdf->SetAuthor('DENR Admin Portal');
        
        // Add header
        $header = '
        <div style="text-align: center;">
            <img src="denr.png" width="60" style="margin-bottom: 10px;">
            <h2 style="margin: 0;">Department of Environment and Natural Resources</h2>
            <h3 style="margin: 5px 0;">Completed Requests Report</h3>
            <p style="margin: 5px 0;">Generated on: ' . date('F d, Y') . '</p>
        </div>
        ';
        $mpdf->WriteHTML($header);
        
        // Build filter summary if any filters were applied
        $filterSummary = [];
        if (!empty($searchTerm)) $filterSummary[] = "Search: " . htmlspecialchars($searchTerm);
        if (!empty($filterBy)) $filterSummary[] = "Request Type: " . htmlspecialchars($filterBy);
        if (!empty($dateFrom)) $filterSummary[] = "From: " . date('M d, Y', strtotime($dateFrom));
        if (!empty($dateTo)) $filterSummary[] = "To: " . date('M d, Y', strtotime($dateTo));
        
        if (!empty($filterSummary)) {
            $mpdf->WriteHTML('<div style="margin: 15px 0; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">
                <p style="margin: 0;"><strong>Filters Applied:</strong> ' . implode(' | ', $filterSummary) . '</p>
            </div>');
        }
        
        // Start table HTML
        $html = '
        <table border="1" cellpadding="6" cellspacing="0" style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <thead style="background-color: #40916c; color: white;">
                <tr>
                    <th>Reference No</th>
                    <th>Requester</th>
                    <th>Request Type</th>
                    <th>Date Created</th>
                    <th>Date Actioned</th>
                    <th>Action Taken</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>';
        
        // Reset result pointer
        mysqli_data_seek($result, 0);
        
        // Add data rows
        $rowCount = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $rowStyle = $rowCount % 2 == 0 ? 'background-color: #f2f2f2;' : '';
            $html .= '<tr style="' . $rowStyle . '">
                <td>REQ-' . str_pad($row['reqform_id'], 4, '0', STR_PAD_LEFT) . '</td>
                <td>' . htmlspecialchars($row['name']) . '</td>
                <td>' . htmlspecialchars($row['request_type']) . '</td>
                <td>' . date('M d, Y', strtotime($row['date_created'])) . '</td>
                <td>' . date('M d, Y', strtotime($row['action_date'])) . '</td>
                <td>' . htmlspecialchars($row['action_taken']) . '</td>
                <td>' . htmlspecialchars($row['remarks']) . '</td>
            </tr>';
            $rowCount++;
        }
        
        $html .= '</tbody></table>';
        
        // Add footer with total count
        $html .= '<div style="margin-top: 20px; text-align: right;">
            <p><strong>Total Records:</strong> ' . $rowCount . '</p>
        </div>';
        
        // Add footer with page numbers
        $mpdf->SetFooter('Page {PAGENO} of {nb}');
        
        // Write HTML to PDF
        $mpdf->WriteHTML($html);
        
        // Output PDF for download
        $mpdf->Output($fileTitle . '.pdf', 'D');
        break;
        
    case 'excel':
        // Require PhpSpreadsheet library
        // Note: You need to install this via Composer first
        // composer require phpoffice/phpspreadsheet
        require_once 'vendor/autoload.php';
        
        use PhpOffice\PhpSpreadsheet\Spreadsheet;
        use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
        
        // Create new spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set spreadsheet metadata
        $spreadsheet->getProperties()
            ->setCreator('DENR Admin Portal')
            ->setLastModifiedBy('DENR Admin')
            ->setTitle('DENR Completed Requests Report')
            ->setSubject('Completed Requests')
            ->setDescription('Report generated from DENR Admin Portal');
        
        // Add headers
        $sheet->setCellValue('A1', 'DENR Completed Requests Report');
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A2', 'Generated on: ' . date('F d, Y'));
        $sheet->mergeCells('A2:G2');
        
        // Apply formatting to headers
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setItalic(true);
        
        // Add filter summary if any filters were applied
        $filterRow = 3;
        if (!empty($searchTerm) || !empty($filterBy) || !empty($dateFrom) || !empty($dateTo)) {
            $filterText = "Filters Applied: ";
            if (!empty($searchTerm)) $filterText .= "Search: " . $searchTerm . " | ";
            if (!empty($filterBy)) $filterText .= "Type: " . $filterBy . " | ";
            if (!empty($dateFrom)) $filterText .= "From: " . date('M d, Y', strtotime($dateFrom)) . " | ";
            if (!empty($dateTo)) $filterText .= "To: " . date('M d, Y', strtotime($dateTo));
            $filterText = rtrim($filterText, " | ");
            
            $sheet->setCellValue('A3', $filterText);
            $sheet->mergeCells('A3:G3');
            $filterRow = 4;
        }
        
        // Column headers
        $sheet->setCellValue('A' . $filterRow, 'Reference No');
        $sheet->setCellValue('B' . $filterRow, 'Requester');
        $sheet->setCellValue('C' . $filterRow, 'Request Type');
        $sheet->setCellValue('D' . $filterRow, 'Date Created');
        $sheet->setCellValue('E' . $filterRow, 'Date Actioned');
        $sheet->setCellValue('F' . $filterRow, 'Action Taken');
        $sheet->setCellValue('G' . $filterRow, 'Remarks');
        
        // Style the column headers
        $headerStyle = $sheet->getStyle('A' . $filterRow . ':G' . $filterRow);
        $headerStyle->getFont()->setBold(true);
        
        // Auto size columns for better readability
        foreach(range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Add data rows
        $rowNum = $filterRow + 1;
        mysqli_data_seek($result, 0);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $sheet->setCellValue('A' . $rowNum, 'REQ-' . str_pad($row['reqform_id'], 4, '0', STR_PAD_LEFT));
            $sheet->setCellValue('B' . $rowNum, $row['name']);
            $sheet->setCellValue('C' . $rowNum, $row['request_type']);
            $sheet->setCellValue('D' . $rowNum, date('M d, Y', strtotime($row['date_created'])));
            $sheet->setCellValue('E' . $rowNum, date('M d, Y', strtotime($row['action_date'])));
            $sheet->setCellValue('F' . $rowNum, $row['action_taken']);
            $sheet->setCellValue('G' . $rowNum, $row['remarks']);
            $rowNum++;
        }
        
        // Create summary row
        $totalRow = $rowNum + 1;
        $sheet->setCellValue('A' . $totalRow, 'Total Records:');
        $sheet->setCellValue('B' . $totalRow, $rowNum - ($filterRow + 1));
        $sheet->getStyle('A' . $totalRow . ':B' . $totalRow)->getFont()->setBold(true);
        
        // Create the Excel file
        $writer = new Xlsx($spreadsheet);
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileTitle . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        // Save to output
        $writer->save('php://output');
        break;
    
    default:
        // Redirect back if invalid format
        header("location:completed.php?error=InvalidFormat");
        exit;
}