<?php
session_start();
require_once "auth.php";
clear_remember_token($conn);
session_unset();
session_destroy();

header("Location: index.php");
exit();
?>
