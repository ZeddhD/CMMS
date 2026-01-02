<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname   = $_POST['firstname'] ?? '';
    $lastname    = $_POST['lastname'] ?? '';
    $name        = trim($firstname . ' ' . $lastname);
    $email       = strtolower(trim($_POST['email'] ?? ''));
    $password    = trim($_POST['password'] ?? '');
    $confirmpassword = $_POST['confirm_password'] ?? '';
    $role        = 'student'; // Hardcoded - only students can register
    $university_name = trim($_POST['university'] ?? '');

    $terms       = $_POST['terms'] ?? '';

    if (!$name || !$email || !$password || !$terms) {
        header("Location: ../register.php?error=missing_fields");
        exit;
    }

    if ($password !== $confirmpassword) {
        header("Location: ../register.php?error=password_mismatch");
        exit;
    }

    // Check if email already exists (in both tables)
    $check_student = $pdo->prepare("SELECT * FROM STUDENT WHERE Email = ?");
    $check_student->execute([$email]);
    $check_admin = $pdo->prepare("SELECT * FROM ADMIN WHERE Email = ?");
    $check_admin->execute([$email]);
    if ($check_student->fetch() || $check_admin->fetch()) {
        header("Location: ../register.php?error=email_exists");
        exit;
    }

    // Password Validation
    if (strlen($password) < 8 || 
        !preg_match('/[A-Z]/', $password) || 
        !preg_match('/[a-z]/', $password) || 
        !preg_match('/[0-9]/', $password) || 
        !preg_match('/[^A-Za-z0-9]/', $password)) {
        header("Location: ../register.php?error=weak_password");
        exit;
    }

    // Registration is student-only
    // Find UniID from university name if provided
    $uni_id = null;
    if ($university_name) {
        $stmt = $pdo->prepare("SELECT UniID FROM UNIVERSITY WHERE UniName = ?");
        $stmt->execute([$university_name]);
        $university = $stmt->fetch();
        if (!$university) {
            header("Location: ../register.php?error=university_not_found");
            exit;
        }
        $uni_id = $university['UniID'];
    }

    // Hash the password before storing
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insert student
    $stmt = $pdo->prepare("INSERT INTO STUDENT (UniID, Name, Email, PasswordHash, RegistrationDate)
                           VALUES (?, ?, ?, ?, CURDATE())");
    $stmt->execute([$uni_id, $name, $email, $passwordHash]);

    // Store session with the new StudentID
    $student_id = $pdo->lastInsertId();
    $_SESSION['user_id']      = $student_id;
    $_SESSION['student_id']   = $student_id;
    $_SESSION['role']         = 'student';
    $_SESSION['student_name'] = $name;

    // Redirect to dashboard
    header("Location: ../student-dashboard.php");
    exit;
} else {
    echo "Invalid request.";
}
