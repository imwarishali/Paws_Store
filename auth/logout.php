<?php
session_start();

unset($_SESSION["user"]);

/* redirect to login inside auth folder */
header("Location: login.php");
exit();
