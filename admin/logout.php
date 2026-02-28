<?php
// admin/logout.php
session_start();
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_email']);
header('Location: login.php');
exit;
