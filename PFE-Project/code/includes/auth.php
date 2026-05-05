<?php


// Masquer les erreurs pour les utilisateurs
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Ton code ensuite...


session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.html");
    exit();
}
?>