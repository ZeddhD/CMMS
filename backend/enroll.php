<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['student_id'];
    $course_code = trim($_POST['course_code'] ?? '');
    $course_title = trim($_POST['course_title'] ?? '');
    $semester = trim($_POST['semester'] ?? 'Fall 2025');
    $total_quiz = intval($_POST['total_quiz'] ?? 4);
    $total_assignment = intval($_POST['total_assignment'] ?? 3);
    $best_of_quiz = intval($_POST['best_of_quiz'] ?? 3);

    if (empty($course_code) || empty($course_title)) {
        header("Location: ../enroll.php?error=missing_fields");
        exit();
    }

    // Get student's university to associate the new course correctly
    $stmt = $pdo->prepare("SELECT UniID FROM STUDENT WHERE StudentID = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
    $uni_id = $student['UniID'] ?? null;

    // Check if course exists by Code
    $stmt = $pdo->prepare("SELECT CourseID FROM COURSE WHERE CourseCode = ?");
    $stmt->execute([$course_code]);
    $course = $stmt->fetch();

    if ($course) {
        $course_id = $course['CourseID'];
    } else {
        // Create new course
        $stmt = $pdo->prepare("INSERT INTO COURSE (CourseCode, CourseTitle, UniID) VALUES (?, ?, ?)");
        $stmt->execute([$course_code, $course_title, $uni_id]);
        $course_id = $pdo->lastInsertId();
    }

    // Check if already enrolled
    $stmt = $pdo->prepare("SELECT EnrollmentID FROM ENROLLMENT WHERE StudentID = ? AND CourseID = ?");
    $stmt->execute([$student_id, $course_id]);
    if ($stmt->fetch()) {
        header("Location: ../enroll.php?error=already_enrolled");
        exit();
    }

    // Create enrollment
    $stmt = $pdo->prepare("
        INSERT INTO ENROLLMENT (StudentID, CourseID, Semester, BestOfQuizCount, TotalQuizPlanned, TotalAssignmentPlanned, QuizCompletedCount, AssignmentCompletedCount)
        VALUES (?, ?, ?, ?, ?, ?, 0, 0)
    ");
    $stmt->execute([$student_id, $course_id, $semester, $best_of_quiz, $total_quiz, $total_assignment]);

    header("Location: ../student-dashboard.php?enrolled=1");
    exit();
} else {
    header("Location: ../enroll.php");
    exit();
}
?>
