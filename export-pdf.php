<?php
include 'sql.php'; // Include your database connection file

// Require the mPDF library
require_once __DIR__ . '/vendor/autoload.php';

// SQL query
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
$result = $con->query($query);

// Create new mPDF instance
$mpdf = new \Mpdf\Mpdf([
    'orientation' => 'L', // Landscape
    'format' => 'A4',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 15,
    'margin_bottom' => 15,
    'margin_header' => 10,
    'margin_footer' => 10,
]);

// Set document information
$mpdf->SetTitle('Database Export');
$mpdf->SetAuthor('System Administrator');
$mpdf->SetCreator('mPDF PHP Library');

// Add a header
$mpdf->SetHTMLHeader('
<div style="text-align: center; font-weight: bold; font-size: 16pt;">
    Database Export Report
</div>
<div style="text-align: center; font-style: italic; font-size: 9pt;">
    Generated on ' . date('Y-m-d H:i:s') . '
</div><br><br><br><br>');

// Add a footer with page numbers
$mpdf->SetHTMLFooter('
<div style="text-align: center; font-size: 9pt;">
    Page {PAGENO} of {nbpg}
</div>');

// Get fields for headers
$fields = $result->fetch_fields();
$headers = [];
foreach($fields as $field) {
    $headers[] = $field->name;
}

// Start HTML for the table
$html = '
<style>
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9pt;
    }
    th {
        background-color: #f2f2f2;
        font-weight: bold;
        text-align: center;
        padding: 5px;
        border: 1px solid #000000;
    }
    td {
        padding: 5px;
        border: 1px solid #000000;
        vertical-align: top;
    }
    .center {
        text-align: center;
    }
    .nowrap {
        white-space: nowrap;
    }
</style>

<table autosize="1">
    <thead>
        <tr>';

// Add headers
foreach($headers as $header) {
    $html .= '<th>' . htmlspecialchars($header) . '</th>';
}

$html .= '
        </tr>
    </thead>
    <tbody>';

// Reset result pointer
$result->data_seek(0);

// Add data rows
while($row = $result->fetch_assoc()) {
    $html .= '<tr>';
    foreach($row as $key => $value) {
        // Add appropriate CSS classes based on content type
        $class = '';
        if (preg_match('/date/', $key)) {
            $class = ' class="center nowrap"';
        } elseif (in_array($key, ['controlnumber', 'rating'])) {
            $class = ' class="center"';
        }
        
        $html .= '<td' . $class . '>' . htmlspecialchars($value) . '</td>';
    }
    $html .= '</tr>';
}

$html .= '
    </tbody>
</table>';

// Write HTML to the PDF
$mpdf->WriteHTML($html);

// Output the PDF for download
$mpdf->Output('database_export.pdf', 'D');

// Close database connection
$con->close();
?>