<?php
    include_once "sql.php";
    include "config.php";

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
    $purchaseDate = $row['p_date'] ?? ''; // Ensure correct field
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
    <title>ICT Service Request Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px; /* Increased from 11px */
        }
        .container {
            width: 100%;
            max-width: 8.5in; /* Folio width */
            height: 13in; /* Folio height */
            margin: auto;
            padding: 15px; /* Increased from 10px */
            box-sizing: border-box;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            margin-bottom: 15px; /* Increased from 10px */
        }
        .logo {
            max-height: 70px; /* Increased from 60px */
            margin: 0 12px;
        }
        h2 {
            font-size: 14px; /* Increased from 12px */
            margin: 3px 0;
        }
        h3 {
            font-size: 12px; /* Increased from 10px */
            font-weight: normal;
            margin: 3px 0;
        }
        .form-group {
            margin-bottom: 10px; /* Increased from 8px */
        }
        .form-row {
            display: flex;
            gap: 10px; /* Increased from 8px */
            margin-bottom: 10px; /* Increased from 8px */
        }
        .form-col {
            flex: 1;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 3px; /* Increased from 2px */
            font-size: 12px; /* Increased from 10px */
        }
        .data-field {
            width: 100%;
            padding: 5px; /* Increased from 4px */
            box-sizing: border-box;
            border: 1px solid #eee;
            background-color: #f9f9f9;
            min-height: 28px; /* Increased from 24px */
        }
        .textarea-field {
            width: 100%;
            min-height: 50px; /* Increased from 40px */
            padding: 5px; /* Increased from 4px */
            box-sizing: border-box;
            border: 1px solid #eee;
            background-color: #f9f9f9;
        }
        /* Adjust textarea heights for better space usage */
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
        .print-button {
            width: 100%;
            padding: 8px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 12px; /* Increased from 10px */
        }
        .print-button:hover {
            background-color: #0056b3;
        }
        .signatures {
            margin-top: 12px; /* Increased from 8px */
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
            font-size: 12px; /* Increased from 10px */
        }
        
        /* Compact the form layout when printing */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 10px;
                border: none;
                box-shadow: none;
            }
            .print-button {
                display: none;
            }
            .form-row, .form-group {
                page-break-inside: avoid;
                margin-bottom: 10px; /* Slightly reduced for print */
            }
            .textarea-field {
                min-height: 50px; /* Slightly reduced for print */
            }
            #jobDescription {
                min-height: 57px; /* Slightly reduced for print */
            }
            /* Ensure exact folio size when printing */
            @page {
                size: 8.5in 13in;
                margin: 0.25in;
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
                    <div id="controlNo" class="data-field"><?php echo htmlspecialchars($cnum); ?></div>
                </div>
                <div class="form-col">
                    <label for="dateRequest">DATE OF REQUEST:</label>
                    <div id="dateRequest" class="data-field"><?php echo htmlspecialchars($dateRequest); ?></div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <label for="division">DIVISION:</label>
                    <div id="division" class="data-field"><?php echo htmlspecialchars($division); ?></div>
                </div>
                <div class="form-col">
                    <label for="requestType">REQUEST TYPE:</label>
                    <div id="requestType" class="data-field"><?php echo htmlspecialchars($requestType); ?></div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <label for="purchaseDate">DATE OF PURCHASE:</label>
                    <div id="purchaseDate" class="data-field"><?php echo htmlspecialchars($purchaseDate); ?></div>
                </div>
                <div class="form-col">
                    <label for="responsiblePerson">RESPONSIBLE PERSON:</label>
                    <div id="responsiblePerson" class="data-field"><?php echo htmlspecialchars($responsiblePerson); ?></div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <label for="brandModel">BRAND/MODEL:</label>
                    <div id="brandModel" class="data-field"><?php echo htmlspecialchars($brandModel); ?></div>
                </div>
                <div class="form-col">
                    <label for="serialNo">SERIAL NO.:</label>
                    <div id="serialNo" class="data-field"><?php echo htmlspecialchars($serialNo); ?></div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <label for="propertyNo">PROPERTY NO.:</label>
                    <div id="propertyNo" class="data-field"><?php echo htmlspecialchars($propertyNo); ?></div>
                </div>
                <div class="form-col">
                    <label for="requestingPersonnel">REQUESTING PERSONNEL:</label>
                    <div id="requestingPersonnel" class="data-field"><?php echo htmlspecialchars($requestingPersonnel); ?></div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="jobDescription">JOB REQUEST DESCRIPTION:</label>
                <div id="jobDescription" class="textarea-field"><?php echo htmlspecialchars($jobDescription); ?></div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <label for="initialFindings">INITIAL FINDINGS:</label>
                    <div id="initialFindings" class="textarea-field"><?php echo htmlspecialchars($initialFindings); ?></div>
                </div>
                <div class="form-col">
                    <label for="actionTaken">ACTION TAKEN:</label>
                    <div id="actionTaken" class="textarea-field"><?php echo htmlspecialchars($actionTaken); ?></div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="recommendation">RECOMMENDATION:</label>
                <div id="recommendation" class="textarea-field"><?php echo htmlspecialchars($recommendation); ?></div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <label for="dateFinished">DATE FINISHED:</label>
                    <div id="dateFinished" class="data-field"><?php echo htmlspecialchars($dateFinished); ?></div>
                </div>
                <div class="form-col">
                    <label for="feedbackRating">FEEDBACK RATING:</label>
                    <div id="feedbackRating" class="data-field"><?php echo htmlspecialchars($feedbackRating); ?></div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="comments">COMMENTS:</label>
                <div id="comments" class="textarea-field"><?php echo htmlspecialchars($comments); ?></div>
            </div>
            
            <div class="signatures">
                <div class="form-row">
                    <div class="form-col signature-line">
                        <p><strong>Requesting Personnel:</strong></p>
                        <p style="text-transform: uppercase;"><u><?php echo htmlspecialchars($requestingPersonnel); ?></u></p>
                    </div>
                    <div class="form-col signature-line">
                        <p><strong>Technical Support:</strong></p>
                        <p>_________________</p>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col"></div>
                    <div class="form-col signature-line">
                        <p class="signature-name">BENJAMIN A. CALUB</p>
                        <p class="signature-title">Information System Analyst II</p>
                    </div>
                </div>
            </div>

            <!-- Print Button -->
            <button class="print-button" onclick="printForm()">Print Form</button>
        </div>
    </div>

    <script>
        function printForm() {
            window.print();
        }
    </script>
</body>
</html>