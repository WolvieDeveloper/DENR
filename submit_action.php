<?php
include 'sql.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $controlnumber = $_POST['controlnumber'];

    $query_verify = "SELECT 1 FROM actions WHERE controlnumber = ?";
$stmt = mysqli_prepare($con, $query_verify);
mysqli_stmt_bind_param($stmt, "s", $controlnumber);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    header('Location: request.php?notif=ActionAlreadySubmitted');
    exit();
}

mysqli_stmt_close($stmt);

    $initialFindings = $_POST['initialFindings'];
    $actionTaken = $_POST['actionTaken'];
    $recommendation = $_POST['recommendation'];
    $date = date("Y-m-d");
    // Update the status to "Done" in the reqform table
    $update_query = "UPDATE reqform SET status = 'Done' WHERE controlnumber = '$controlnumber'";
    $result = mysqli_query($con, $update_query);

    if ($result) {
        // Retrieve the requester's email
        $email_query = "SELECT Email FROM reqform WHERE controlnumber = '$controlnumber'";
        $email_result = mysqli_query($con, $email_query);
        $email_row = mysqli_fetch_assoc($email_result);
        $requester_email = $email_row['Email'];
        $name = $email_row['ReqPersonel'];

        // Send email notification
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'jeromemarino.aclc@gmail.com';
            $mail->Password = 'oulmlicmiepscxfh';
            $mail->Port = 465;
            $mail->SMTPSecure = 'ssl';
            $mail->isHTML(true);
            $mail->addAddress($requester_email); // The requester's email
            $mail->setFrom('marinojerome1603@gmail.com', 'DENR VIII');
            $mail->Subject = 'Request Status Update';
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;'>
                <!-- Header with Logo -->
                <div style='text-align: center; margin-bottom: 20px; padding: 15px; background-color: #0c713d; border-radius: 5px 5px 0 0;'>
                    <img src='https://denr.gov.ph/wp-content/uploads/2023/04/denr-logo-png-1-1.png' alt='DENR Logo' style='height: 60px; width: auto;' />
                    <h2 style='color: #ffffff; margin: 10px 0 0 0;'>Service Request Completion</h2>
                </div>
                
                <!-- Main Content -->
                <div style='background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>
                    <p style='font-size: 16px; line-height: 1.5;'>Hello Sir/Ma'am <strong>{$name}</strong>,</p>
                    
                    <div style='background-color: #f0f7f3; border-left: 4px solid #0c713d; padding: 10px 15px; margin: 15px 0;'>
                        <h3 style='color: #0c713d; margin: 0 0 5px 0; font-size: 16px;'>Control Number</h3>
                        <p style='font-size: 18px; font-weight: bold; margin: 0; color: #333;'>{$controlnumber}</p>
                    </div>
                    
                    <p style='font-size: 16px;'>We're pleased to inform you that your service request has been <span style='color: #0c713d; font-weight: bold;'>successfully completed</span>.</p>
                    
                    <div style='margin: 20px 0; border-top: 1px solid #eee; padding-top: 15px;'>
                        <div style='margin-bottom: 15px;'>
                            <h3 style='color: #0c713d; font-size: 16px; margin: 0 0 10px 0; border-bottom: 1px solid #eee; padding-bottom: 5px;'>Initial Findings</h3>
                            <p style='margin: 0; line-height: 1.5;'>{$initialFindings}</p>
                        </div>
                        
                        <div style='margin-bottom: 15px;'>
                            <h3 style='color: #0c713d; font-size: 16px; margin: 0 0 10px 0; border-bottom: 1px solid #eee; padding-bottom: 5px;'>Action Taken</h3>
                            <p style='margin: 0; line-height: 1.5;'>{$actionTaken}</p>
                        </div>
                        
                        <div style='margin-bottom: 15px;'>
                            <h3 style='color: #0c713d; font-size: 16px; margin: 0 0 10px 0; border-bottom: 1px solid #eee; padding-bottom: 5px;'>Recommendation</h3>
                            <p style='margin: 0; line-height: 1.5;'>{$recommendation}</p>
                        </div>
                    </div>
                    
                    <div style='background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin-top: 20px;'>
                        <p style='margin: 0 0 15px 0;'>Your feedback is important to us. Please take a moment to rate our service.</p>
                        <a href='http://192.168.1.38/DENR/servicerequest/feedback.php?id=$controlnumber' style='display: inline-block; background-color: #0c713d; color: white; text-decoration: none; padding: 10px 20px; border-radius: 4px; font-weight: bold;'>Provide Feedback</a>
                    </div>
                </div>
                
                <!-- Footer -->
                <div style='text-align: center; padding: 15px; margin-top: 20px; font-size: 12px; color: #666; border-top: 1px solid #ddd;'>
                    <p style='margin: 0 0 10px 0;'>This is an automated email. Please do not reply.</p>
                    <p style='margin: 0;'>Department of Environment and Natural Resources</p>
                    <p style='margin: 5px 0 0 0;'>&copy; " . date('Y') . " DENR. All rights reserved.</p>
                </div>
            </div>
        ";


            $mail->send();
        } catch (Exception $e) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        }

        // Optionally insert the action details into another table
        $action_query = "INSERT INTO actions (controlnumber, initialFindings, actionTaken, recommendation , date_finished) VALUES ('$controlnumber', '$initialFindings', '$actionTaken', '$recommendation', '$date')";
        mysqli_query($con, $action_query);

        // Redirect to a success page
        header('Location: request.php?notif=ActionSubmitted');
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}
?>
