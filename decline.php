<?php

include_once "sql.php";
session_start();

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['id'])) {
    header("location:admin-login.html?notif=SessionExpired");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $controlnumber = $_POST['controlnumber'];

    // Prepare and execute delete query
    $query = "DELETE FROM `temp-reqform` WHERE `controlnumber` = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $controlnumber);
    
    if (mysqli_stmt_execute($stmt)) {
        // Redirect with success notification
        header("location:admin-newreq.php?notif=RequestDeclined");
        exit;
    } else {
        // Redirect with error notification
        header("location:admin-newreq.php?notif=Error");
        exit;
    }
}


?>