<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['user_id'] ?? null;
    if (!$student_id) {
        die("❌ You must be logged in to perform this action.");
    }
    $course_code = trim($_POST['course_code'] ?? '');
    $course_title = trim($_POST['course_title'] ?? '');
    $stmt = $pdo->prepare("SELECT CourseID FROM COURSE WHERE CourseCode = ? AND CourseTitle = ?");
    $stmt->execute([$course_code, $course_title]);
    $course = $stmt->fetch();
    if (!$course) {
        die("❌ Course not found.");
    }
    $course_id = $course['CourseID'];

    // Lookup enrollment_id
    $stmt = $pdo->prepare("SELECT EnrollmentID FROM ENROLLMENT WHERE StudentID = ? AND CourseID = ?");
    $stmt->execute([$student_id, $course_id]);
    $enrollment = $stmt->fetch();
    if (!$enrollment) {
        die("❌ Enrollment not found.");
    }
    $enrollment_id = $enrollment['EnrollmentID'];

    $type           = $_POST['type'];
    $title          = $_POST['title'];
    $max_marks      = $_POST['max_marks'];
    $weight         = 0; // Default weight to 0 if not provided
    $due_date       = $_POST['due_date'];
    $due_time       = $_POST['due_time'] ?? null; // Optional for quizzes
    $status         = $_POST['status'];
    $is_mandatory   = $_POST['mandatory'];
    $marks_obtained = $_POST['marks_obtained'] ?? null;

    if (!$type || !$title || !$max_marks || !$due_date || !$status || $is_mandatory === '') {
        header("Location: ../student-dashboard.php?error=missing_fields");
        exit;
    }


    $stmt = $pdo->prepare("
        INSERT INTO ASSESSMENT 
        (EnrollmentID, AssessmentType, Title, MaxMarks, WeightInBestOf, DueDate, DueTime, Status, IsMandatory, MarksObtained)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $enrollment_id,
        $type,
        $title,
        $max_marks,
        $weight,
        $due_date,
        $due_time,
        $status,
        $is_mandatory,
        $marks_obtained
    ]);

    header("Location: ../course-details.php?enrollment_id=$enrollment_id");
    exit;
} else {
    echo "Invalid request.";
}
