<?php
require 'db.php';
session_start();

$student_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_code      = trim($_POST['course_code'] ?? '');
    $course_title     = trim($_POST['course_title'] ?? '');
    $semester         = $_POST['semester'] ?? '';
    $total_quiz       = $_POST['total_quiz'] ?? '';
    $total_assignment = $_POST['total_assignment'] ?? '';
    $best_of_quiz     = $_POST['best_of_quiz'] ?? '';

    if (!$student_id || !$course_code || !$semester || !$total_quiz || !$total_assignment || !$best_of_quiz) {
    if (!$student_id || !$course_code || !$semester || !$total_quiz || !$total_assignment || !$best_of_quiz) {
        header("Location: ../enroll.html?error=missing_fields");
        exit;
    }
    }

    // Use course_title if provided, else use course_code as title
    $course_title = $course_title ?: $course_code;

    // ✅ Check if course already exists
    $stmt = $pdo->prepare("SELECT CourseID FROM COURSE WHERE CourseCode = ? AND CourseTitle = ?");
    $stmt->execute([$course_code, $course_title]);
    $course = $stmt->fetch();

    // ✅ If not found, insert it
    if (!$course) {
        $insert = $pdo->prepare("INSERT INTO COURSE (CourseCode, CourseTitle) VALUES (?, ?)");
        $insert->execute([$course_code, $course_title]);
        $course_id = $pdo->lastInsertId();
    } else {
        $course_id = $course['CourseID'];
    }

    // Defaults
    $quiz_done = 0;
    $assignment_done = 0;

    // ✅ Enroll student in course
    $stmt = $pdo->prepare("
        INSERT INTO ENROLLMENT 
        (StudentID, CourseID, Semester, BestOfQuizCount, TotalQuizPlanned, TotalAssignmentPlanned, QuizCompletedCount, AssignmentCompletedCount)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $student_id,
        $course_id,
        $semester,
        $best_of_quiz,
        $total_quiz,
        $total_assignment,
        $quiz_done,
        $assignment_done
    ]);

    header("Location: ../student-dashboard.php");
    exit;
} else {
    echo "Invalid request.";
}
