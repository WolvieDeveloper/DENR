<?php
session_start();
session_destroy();
header("location:admin-login.php?notif=LoggedOut");
exit;
