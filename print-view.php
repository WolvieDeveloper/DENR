<?php
    include_once "sql.php";
    include "config.php";
    
    // Require mPDF library (make sure you've installed it via Composer)
    require_once __DIR__ . '/vendor/autoload.php';

    // Validate 'id' parameter
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header('Location: index.php');
        exit;
    }

    // Use a parameterized query
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
    JOIN feedback ON reqform.controlnumber = feedback.controlnumber
    WHERE reqform.controlnumber = ?";

    if ($stmt = mysqli_prepare($con, $query)) {
        mysqli_stmt_bind_param($stmt, "s", $_GET['id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    } else {
        die("Database query failed: " . mysqli_error($con));
    }

    if (!$row) {
        header('Location: index.php?error=notfound');
        exit;
    }

    // Assign variables
    $cnum = $row['controlnumber'] ?? '';
    $dateRequest = $row['date_filed'] ?? '';
    $division = $row['Division'] ?? '';
    $requestType = $row['ReqType'] ?? '';
    $equipmentType = $row['BrandModel'] ?? '';
    $purchaseDate = $row['p_date'] ?? '';
    $responsiblePerson = $row['resperson'] ?? '';
    $brandModel = $row['BrandModel'] ?? '';
    $serialNo = $row['SerialNo'] ?? '';
    $propertyNo = $row['PropertyNo'] ?? '';
    $requestingPersonnel = $row['ReqPersonel'] ?? '';
    $jobDescription = $row['JobDescription'] ?? '';
    $initialFindings = $row['initialFindings'] ?? '';
    $recommendation = $row['recommendation'] ?? '';
    $dateFinished = $row['date_finished'] ?? '';
    $feedbackRating = $row['rating'] ?? '';
    $comments = $row['comment'] ?? '';
    $actionTaken = $row['actionTaken'] ?? '';

    // Check if PDF generation is requested
    $generatePdf = isset($_GET['pdf']) && $_GET['pdf'] == '1';

    // HTML content for both display and PDF
    $html = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ICT Service Request Form</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                font-size: 12px;
            }
            .container {
                width: 100%;
                max-width: 8.5in; /* Folio width */
                margin: auto;
                padding: 15px;
                box-sizing: border-box;
            }
            .header {
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                margin-bottom: 15px;
            }
            .logo {
                max-height: 70px;
                margin: 0 12px;
            }
            h2 {
                font-size: 14px;
                margin: 3px 0;
            }
            h3 {
                font-size: 12px;
                font-weight: normal;
                margin: 3px 0;
            }
            .form-group {
                margin-bottom: 10px;
            }
            .form-row {
                display: flex;
                gap: 10px;
                margin-bottom: 10px;
            }
            .form-col {
                flex: 1;
            }
            label {
                font-weight: bold;
                display: block;
                margin-bottom: 3px;
                font-size: 12px;
            }
            .data-field {
                width: 100%;
                padding: 5px;
                box-sizing: border-box;
                border: 1px solid #eee;
                background-color: #f9f9f9;
                min-height: 28px;
            }
            .textarea-field {
                width: 100%;
                min-height: 50px;
                padding: 5px;
                box-sizing: border-box;
                border: 1px solid #eee;
                background-color: #f9f9f9;
            }
            #jobDescription {
                min-height: 60px;
            }
            #initialFindings, #actionTaken {
                min-height: 50px;
            }
            #recommendation {
                min-height: 50px;
            }
            #comments {
                min-height: 50px;
            }
            .print-button, .pdf-button {
                width: 100%;
                padding: 8px;
                background-color: #007bff;
                color: white;
                border: none;
                cursor: pointer;
                margin-top: 12px;
                text-align: center;
            }
            .print-button:hover, .pdf-button:hover {
                background-color: #0056b3;
            }
            .signatures {
                margin-top: 12px;
            }
            .signature-line {
                text-align: center;
            }
            .signature-name {
                margin-top: 0;
                margin-bottom: 0;
                font-weight: bold;
                text-decoration: underline;
            }
            .signature-title {
                margin-top: 0;
                font-size: 12px;
            }
            
            @media print {
                .no-print {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <img src="DENR.png" alt="" class="logo">
                <div>
                    <h2>Department of Environment and Natural Resources</h2>
                    <h3>REGIONAL OFFICE NO.VIII, Tacloban City, Leyte</h3>
                    <h2>ICT Service Request Form</h2>
                </div>
                <img src="BPP Logo.png" class="logo" style="width: 70px;" alt="">
            </div>
            
            <div class="form">
                <div class="form-row">
                    <div class="form-col">
                        <label for="controlNo">CONTROL NO.:</label>
                        <div id="controlNo" class="data-field">'.htmlspecialchars($cnum).'</div>
                    </div>
                    <div class="form-col">
                        <label for="dateRequest">DATE OF REQUEST:</label>
                        <div id="dateRequest" class="data-field">'.htmlspecialchars($dateRequest).'</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <label for="division">DIVISION:</label>
                        <div id="division" class="data-field">'.htmlspecialchars($division).'</div>
                    </div>
                    <div class="form-col">
                        <label for="requestType">REQUEST TYPE:</label>
                        <div id="requestType" class="data-field">'.htmlspecialchars($requestType).'</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <label for="purchaseDate">DATE OF PURCHASE:</label>
                        <div id="purchaseDate" class="data-field">'.htmlspecialchars($purchaseDate).'</div>
                    </div>
                    <div class="form-col">
                        <label for="responsiblePerson">RESPONSIBLE PERSON:</label>
                        <div id="responsiblePerson" class="data-field">'.htmlspecialchars($responsiblePerson).'</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <label for="brandModel">BRAND/MODEL:</label>
                        <div id="brandModel" class="data-field">'.htmlspecialchars($brandModel).'</div>
                    </div>
                    <div class="form-col">
                        <label for="serialNo">SERIAL NO.:</label>
                        <div id="serialNo" class="data-field">'.htmlspecialchars($serialNo).'</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <label for="propertyNo">PROPERTY NO.:</label>
                        <div id="propertyNo" class="data-field">'.htmlspecialchars($propertyNo).'</div>
                    </div>
                    <div class="form-col">
                        <label for="requestingPersonnel">REQUESTING PERSONNEL:</label>
                        <div id="requestingPersonnel" class="data-field">'.htmlspecialchars($requestingPersonnel).'</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="jobDescription">JOB REQUEST DESCRIPTION:</label>
                    <div id="jobDescription" class="textarea-field">'.htmlspecialchars($jobDescription).'</div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <label for="initialFindings">INITIAL FINDINGS:</label>
                        <div id="initialFindings" class="textarea-field">'.htmlspecialchars($initialFindings).'</div>
                    </div>
                    <div class="form-col">
                        <label for="actionTaken">ACTION TAKEN:</label>
                        <div id="actionTaken" class="textarea-field">'.htmlspecialchars($actionTaken).'</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="recommendation">RECOMMENDATION:</label>
                    <div id="recommendation" class="textarea-field">'.htmlspecialchars($recommendation).'</div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <label for="dateFinished">DATE FINISHED:</label>
                        <div id="dateFinished" class="data-field">'.htmlspecialchars($dateFinished).'</div>
                    </div>
                    <div class="form-col">
                        <label for="feedbackRating">FEEDBACK RATING:</label>
                        <div id="feedbackRating" class="data-field">'.htmlspecialchars($feedbackRating).'</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="comments">COMMENTS:</label>
                    <div id="comments" class="textarea-field">'.htmlspecialchars($comments).'</div>
                </div>
                
                <div class="signatures">
                    <div class="form-row">
                        <div class="form-col signature-line">
                            <p><strong>Requesting Personnel:</strong></p>
                            <p style="text-transform: uppercase;"><u><b>'.htmlspecialchars($requestingPersonnel).'</b></u></p>
                        </div>
                        <div class="form-col signature-line">
                            <p><strong>Technical Support:</strong></p>
                            <p contenteditable="true" style="text-transform: uppercase;"><b><u>_________________</u></b></p>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col"></div>
                        <div class="form-col signature-line">
                            <b><p class="signature-name" style="text-transform: uppercase;" contenteditable="true">BENJAMIN A. CALUB</p>
                            <p class="signature-title" contenteditable="true">Information System Analyst II</p></b>
                        </div>
                    </div>
                </div>';

    // Only show buttons in the HTML version (not in PDF)
    if (!$generatePdf) {
        $html .= '
                <!-- Action Buttons -->
                <div class="form-row no-print">
                    <div class="form-col">
                        <button class="print-button" onclick="printForm()">Print Form</button>
                    </div>
                    <div class="form-col">
                        <a href="?id='.htmlspecialchars($_GET['id']).'&pdf=1" class="pdf-button" style="display: block; text-decoration: none;">Download PDF</a>
                    </div>
                </div>';
    }

    $html .= '
            </div>
        </div>

        <script>
            function printForm() {
                window.print();
            }
        </script>
    </body>
    </html>';

    // If PDF generation is requested
    if ($generatePdf) {
        // Create an instance of mPDF
        $mpdf = new \Mpdf\Mpdf([
            'format' => 'Folio',         // Use Folio size (8.5" x 13")
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);

        // Set document title
        $mpdf->SetTitle('ICT Service Request Form - ' . $requestingPersonnel);
        
        // Generate PDF
        $mpdf->WriteHTML($html);
        
        // Output PDF for download
        $mpdf->Output('ICT_ServiceRequest_' . $requestingPersonnel . '.pdf', 'D');
        exit;
    } else {
        // Display HTML version
        echo $html;
    }
?>