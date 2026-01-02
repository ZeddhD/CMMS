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
        // Course not found, create it
        $stmt_uni = $pdo->prepare("SELECT UniID FROM STUDENT WHERE StudentID = ?");
        $stmt_uni->execute([$student_id]);
        $student = $stmt_uni->fetch();
        if (!$student) {
            die("❌ Student not found.");
        }
        $uni_id = $student['UniID'];
        $stmt_insert = $pdo->prepare("INSERT INTO COURSE (UniID, CourseCode, CourseTitle) VALUES (?, ?, ?)");
        $stmt_insert->execute([$uni_id, $course_code, $course_title]);
        $course_id = $pdo->lastInsertId();
    } else {
        $course_id = $course['CourseID'];
    }

    // Lookup enrollment_id
    $stmt = $pdo->prepare("SELECT EnrollmentID FROM ENROLLMENT WHERE StudentID = ? AND CourseID = ?");
    $stmt->execute([$student_id, $course_id]);
    $enrollment = $stmt->fetch();
    if (!$enrollment) {
        // Enrollment not found, create it
        $stmt_enroll = $pdo->prepare("INSERT INTO ENROLLMENT (StudentID, CourseID, Semester, BestOfQuizCount, TotalQuizPlanned, TotalAssignmentPlanned, QuizCompletedCount, AssignmentCompletedCount) VALUES (?, ?, 'Fall 2025', 3, 4, 6, 0, 0)");
        $stmt_enroll->execute([$student_id, $course_id]);
        $enrollment_id = $pdo->lastInsertId();
    } else {
        $enrollment_id = $enrollment['EnrollmentID'];
    }

    // First session data
    $day1        = $_POST['day1'];
    $start1      = $_POST['start_time1'];
    $end1        = $_POST['end_time1'];
    $room1       = $_POST['room1'];
    $mode1       = $_POST['mode1'];

    // Second session data
    $day2        = $_POST['day2'];
    $start2      = $_POST['start_time2'];
    $end2        = $_POST['end_time2'];
    $room2       = $_POST['room2'];
    $mode2       = $_POST['mode2'];

    // Validate
    if (!$day1 || !$start1 || !$end1 || !$room1 || !$mode1 || !$day2 || !$start2 || !$end2 || !$room2 || !$mode2) {
    if (!$day1 || !$start1 || !$end1 || !$room1 || !$mode1 || !$day2 || !$start2 || !$end2 || !$room2 || !$mode2) {
        header("Location: ../class-session.html?error=missing_fields");
        exit;
    }
    }

    $stmt = $pdo->prepare("
        INSERT INTO CLASS_SESSION (EnrollmentID, DayOfWeek, StartTime, EndTime, Room, Mode)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    // Insert both sessions
    $stmt->execute([$enrollment_id, $day1, $start1, $end1, $room1, $mode1]);
    $stmt->execute([$enrollment_id, $day2, $start2, $end2, $room2, $mode2]);

    header("Location: ../student-dashboard.php");
    exit;
} else {
    echo "Invalid request.";
}
