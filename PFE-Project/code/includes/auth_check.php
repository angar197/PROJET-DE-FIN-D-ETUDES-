<?php
session_start();

if (!isset($_SESSION['user_logged_in']) && !isset($_SESSION['admin_logged_in'])) {
    header('Location: ../auth/login.php');
    exit;
}
?>
