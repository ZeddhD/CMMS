<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        die("All fields are required.");
    }

    // Hardcoded admin login
    if ($email === 'admin' && $password === '1234') {
        $_SESSION['user_id']    = 'admin';
        $_SESSION['admin_id']   = 'admin';
        $_SESSION['admin_name'] = 'Admin';
        $_SESSION['role']       = 'admin';
        header("Location: ../admin-dashboard.php");
        exit;
    }

    // 1) Try student login
    $stmt = $pdo->prepare("SELECT StudentID, Name, Email, PasswordHash FROM STUDENT WHERE Email = ?");
    $stmt->execute([$email]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student && password_verify($password, $student['PasswordHash'])) {
        $_SESSION['user_id']      = $student['StudentID']; // For generic app logic
        $_SESSION['student_id']   = $student['StudentID']; // For specific user request compliance
        $_SESSION['student_name'] = $student['Name'];
        $_SESSION['role']         = 'student';

        // go to student dashboard
        header("Location: ../student-dashboard.php");
        exit;
    }

    // 2) Try admin login (optional; only if you use admins)
    $stmt = $pdo->prepare("SELECT AdminID, Name, Email, PasswordHash FROM ADMIN WHERE Email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['PasswordHash'])) {
        $_SESSION['user_id']    = $admin['AdminID']; // For generic app logic
        $_SESSION['admin_id']   = $admin['AdminID']; // For specific user request compliance
        $_SESSION['admin_name'] = $admin['Name'];
        $_SESSION['role']       = 'admin';

        // go to admin dashboard
        header("Location: ../admin-dashboard.php");
        exit;
    }

    // If neither student nor admin matched:
    header("Location: ../login.php?error=invalid");
    exit;
} else {
    echo "Invalid request.";
}
