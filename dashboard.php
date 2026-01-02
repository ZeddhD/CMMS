<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$role = $_SESSION['role'];
if ($role === 'student') {
    header("Location: student-dashboard.php");
    exit;
} elseif ($role === 'admin') {
    header("Location: admin-dashboard.php");
    exit;
} else {
    // Unknown role, redirect to login
    header("Location: login.html");
    exit;
}
?>