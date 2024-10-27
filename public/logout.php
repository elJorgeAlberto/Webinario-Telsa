<?php
session_start();
$_SESSION = array();
session_destroy();
session_start();
$_SESSION['logout_message'] = "Has cerrado sesión correctamente.";
header("location: login.php");
exit;
?>
