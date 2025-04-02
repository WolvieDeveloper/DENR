<?php
// accept.php
include_once "sql.php";
session_start();

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['id'])) {
    header("location:admin-login.html?notif=SessionExpired");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $controlnumber = $_POST['controlnumber'];

    // Begin a transaction to ensure data integrity
    mysqli_begin_transaction($con);

    try {
        // Fetch the full record from temp-reqform
        $query = "SELECT * FROM `temp-reqform` WHERE `controlnumber` = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $controlnumber);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $request = mysqli_fetch_assoc($result);

        if ($request) {
            // Insert into permanent reqform table
            $insert_query = "INSERT INTO `reqform` 
                (controlnumber, ReqPersonel, Email, Division, ReqType, 
                BrandModel, SerialNo, PropertyNo, JobDescription, 
                date_filed, p_date, resperson, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
            
            $insert_stmt = mysqli_prepare($con, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, "ssssssssssss", 
                $request['controlnumber'], $request['ReqPersonel'], 
                $request['Email'], $request['Division'], $request['ReqType'],
                $request['BrandModel'], $request['SerialNo'], 
                $request['PropertyNo'], $request['JobDescription'], 
                $request['date_filed'], $request['p_date'], 
                $request['resperson']
            );
            mysqli_stmt_execute($insert_stmt);

            // Delete from temp-reqform
            $delete_query = "DELETE FROM `temp-reqform` WHERE `controlnumber` = ?";
            $delete_stmt = mysqli_prepare($con, $delete_query);
            mysqli_stmt_bind_param($delete_stmt, "s", $controlnumber);
            mysqli_stmt_execute($delete_stmt);

            // Commit the transaction
            mysqli_commit($con);

            // Redirect with success notification
            header("location:admin-newreq.php?notif=RequestAccepted");
            exit;
        } else {
            throw new Exception("Request not found");
        }
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        mysqli_rollback($con);
        header("location:admin-newreq.php?notif=Error");
        exit;
    }
}

?>