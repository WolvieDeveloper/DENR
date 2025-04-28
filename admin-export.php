<?php
include 'sql.php'; // Include your database connection file
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

// Output headers
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="export.csv"');

// Create a file pointer
$output = fopen('php://output', 'w');

// Output the column headings
$fields = $result->fetch_fields();
$headers = array();
foreach($fields as $field) {
    $headers[] = $field->name;
}
fputcsv($output, $headers);

// Output each row of the data
while($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
$con->close();

// header('Location: admin-dash.php'); // Redirect to admin page after download
?>