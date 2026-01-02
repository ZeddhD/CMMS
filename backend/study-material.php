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
    $title      = $_POST['title'];
    $link       = $_POST['link'];
    $type       = $_POST['type'];

    $stmt = $pdo->prepare("
        INSERT INTO STUDY_MATERIAL (StudentID, CourseID, Title, MaterialType, URL_or_Path, UploadDate)
        VALUES (?, ?, ?, ?, ?, CURDATE())
    ");
    $stmt->execute([$student_id, $course_id, $title, $type, $link]);

    header("Location: ../study-material.php?success=1");
    exit;
} else {
    echo "Invalid request";
}
