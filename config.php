<?php
    include_once 'sql.php';

;
    
    if (isset($_POST['register'])) {
        $user_n = $_POST['email'];
        $user_p = $_POST['pword'];
        $user_p = password_hash($user_p, PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO login (email, pword) VALUES ('$user_n', '$user_p')";
        mysqli_query($con, $sql);
        
        header('location: admin-login.php?notif=Registered');
        exit;
    }
    
    if (isset($_POST['login'])) {
        $user_n = $_POST['email'];
        $user_p = $_POST['pword'];
        
        $sql = mysqli_query($con, "SELECT * FROM login WHERE email = '$user_n'");
        $fetcher = mysqli_fetch_assoc($sql);
        
        if ($fetcher) {
            $user_n_fetch = $fetcher['email'];
            $user_p_fetch = $fetcher['pword'];
            $id = $fetcher['id'];
            
            if (password_verify($user_p, $user_p_fetch)) {
                session_start();
                $_SESSION['id'] = $id;
                header('location: admin-dash.php');
                exit;
            } else {
                header('location: admin-login.php?notif=LoginFailed');
                exit;
            }
        } else {
            header('location: admin-login.php?notif=LoginFailed');
            exit;
        }
    }
    

   

    if(isset($_POST['Submit'])){
        // Get today's date in YYMMDD format
        $today = date("ymd");
    
        // Ensure the count increments correctly by locking the table row
        $count_query = "SELECT COUNT(*) AS count
FROM (
    SELECT date_filed FROM reqform WHERE DATE(date_filed) = CURDATE()
    UNION ALL
    SELECT date_filed FROM `temp-reqform` WHERE DATE(date_filed) = CURDATE()
) AS combined FOR UPDATE";
        $count_result = mysqli_query($con, $count_query);
        $count_fetch = mysqli_fetch_assoc($count_result);
        $count = $count_fetch['count'] + 1;
    
        // Generate the unique control number
        $controlnumber = $today . '-' . str_pad($count, 4, "0", STR_PAD_LEFT); // Ensures 4-digit request number
    
        // Retrieve form data
        $reqp = $_POST['ReqPersonel'];
        $cmail = $_POST['CMail'];
        $div = $_POST['Divisions'];
        $reqt = $_POST['ReqType'];
        $beandm = $_POST['BrandModel'];
        $SerNo = $_POST['SerialNo'];
        $PropNo = $_POST['PropertyNo'];
        $jobdesc = $_POST['JobDescription'];
        $date = date("Y-m-d");
        $purchedate = $_POST['datepurchase'];
        $resperson = $_POST['ResPerson'];
    
        // Insert into database
        $query = "INSERT INTO `temp-reqform`(`controlnumber`, `ReqPersonel`, `Email`, `Division`, `ReqType`, `BrandModel`, `SerialNo`, `PropertyNo`, `JobDescription`, `date_filed`, `p_date`, `resperson`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "ssssssssssss", $controlnumber, $reqp, $cmail, $div, $reqt, $beandm, $SerNo, $PropNo, $jobdesc, $date, $purchedate, $resperson);
$insert_result = mysqli_stmt_execute($stmt);


    
        if ($insert_result) {
            header('location: success.html?notif=success');
        } else {
            die("Error inserting data: " . mysqli_error($con));
        }
    
        mysqli_stmt_close($stmt);
    }
    
    

    if(isset($_POST['feedback_btn'])){
        $currentdate = date("Y-m-d");
        $controlnumber = $_POST['controlnumber'];
        $comment = $_POST['feedback'];
        $rating = $_POST['feedbackRating'];
        $date = $_POST['dateFinished'];

        $query = "INSERT INTO feedback (controlnumber, comment, rating, datefeedbacked) VALUES ('$controlnumber', '$comment', '$rating', '$currentdate')";
        $result = mysqli_query($con, $query);


        // SELECT reqform.*, actions.initialFindings, actions.actionTaken, actions.recommendation,actions.date_finished
        // FROM reqform
        // JOIN actions ON reqform.controlnumber = actions.controlnumber
        // WHERE reqform.controlnumber = '250228-0002';

        
    header('location: index.html');
    exit;
    }



    if(isset($_POST['admin-sub'])){
        // Start a transaction
        mysqli_begin_transaction($con);
        
        try {
            // Get today's date in YYMMDD format
            $today = date("ymd");
            $dateRequest = $_POST['dateRequest'];
            
            // More reliable way to get the next sequence number
            $count_query = "SELECT IFNULL(MAX(SUBSTRING_INDEX(controlnumber, '-', -1)), 0) + 1 AS next_number 
                            FROM `reqform` 
                            WHERE controlnumber LIKE '$today-%'";
            $count_result = mysqli_query($con, $count_query);
            $count_fetch = mysqli_fetch_assoc($count_result);
            $count = $count_fetch['next_number'];
            
            // Generate the unique control number
            $controlnumber = $today . '-' . str_pad($count, 4, "0", STR_PAD_LEFT);
            
            $division = $_POST['division'];
            $requestType = $_POST['requestType'];
            $purchaseDate = $_POST['purchaseDate'];
            $responsiblePerson = $_POST['responsiblePerson'];
            $brandModel = $_POST['brandModel'];
            $serialNo = $_POST['serialNo'];
            $propertyNo = $_POST['propertyNo'];
            $requestingPersonnel = $_POST['requestingPersonnel'];
            $jobDescription = $_POST['jobDescription'];
            $initialFindings = $_POST['initialFindings'];
            $recommendation = $_POST['recommendation'];
            $dateFinished = $_POST['dateFinished'];
            $feedbackRating = $_POST['feedbackRating'];
            $comments = $_POST['comments'];
            $status = "Done";
            
            // Use null coalescing to avoid undefined index errors
            $email = $_POST['email'] ?? '';
            $actionTaken = $_POST['ActionTaken'] ?? '';
            
            // Prepare statements to prevent SQL injection
            $stmt1 = mysqli_prepare($con, "INSERT INTO `reqform` 
                                           (`controlnumber`, `ReqPersonel`, `Email`, `Division`, `ReqType`, 
                                           `BrandModel`, `SerialNo`, `PropertyNo`, `JobDescription`, 
                                           `date_filed`, `status`, `p_date`, `resperson`) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            mysqli_stmt_bind_param($stmt1, 'sssssssssssss', 
                                  $controlnumber, $requestingPersonnel, $email, $division, $requestType, 
                                  $brandModel, $serialNo, $propertyNo, $jobDescription, 
                                  $dateRequest, $status, $purchaseDate, $responsiblePerson);
            
            $stmt2 = mysqli_prepare($con, "INSERT INTO `actions` 
                                           (`controlnumber`, `initialFindings`, `actionTaken`, `recommendation`, `date_finished`) 
                                           VALUES (?, ?, ?, ?, ?)");
            
            mysqli_stmt_bind_param($stmt2, 'sssss', 
                                  $controlnumber, $initialFindings, $actionTaken, $recommendation, $dateFinished);
            
            $stmt3 = mysqli_prepare($con, "INSERT INTO `feedback` 
                                           (`controlnumber`, `comment`, `rating`, `datefeedbacked`) 
                                           VALUES (?, ?, ?, ?)");
            
            mysqli_stmt_bind_param($stmt3, 'ssss', 
                                  $controlnumber, $comments, $feedbackRating, $dateFinished);
            
            // Execute statements
            $result1 = mysqli_stmt_execute($stmt1);
            $result2 = mysqli_stmt_execute($stmt2);
            $result3 = mysqli_stmt_execute($stmt3);
            
            if($result1 && $result2 && $result3) {
                // Commit the transaction
                mysqli_commit($con);
                header('location: admin-dash.php?notif=success');
                exit;
            } else {
                // Rollback on error
                mysqli_rollback($con);
                echo "Error: " . mysqli_error($con);
            }
        } catch (mysqli_sql_exception $exception) {
            // Rollback the transaction on exception
            mysqli_rollback($con);
            
            // Check if it's a duplicate key error
            if ($exception->getCode() == 1062) {
                // If it's a duplicate, we can retry with a new sequence number
                echo "Duplicate control number detected. Please try again.";
            } else {
                echo "Error: " . $exception->getMessage();
            }
        }
    }