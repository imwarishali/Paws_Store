<?php
session_start();

session_unset();
session_destroy();

/* redirect to login inside auth folder */
header("Location: login.php");
exit();
