<?php

require_once '../config.php';

unset($_SESSION["user"]);
session_destroy();

/* redirect to login inside auth folder */
header("Location: login.php");
exit();
