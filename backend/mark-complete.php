<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessment_id = $_POST['assessment_id'] ?? null;
    $student_id = $_SESSION['user_id'];

    if (!$assessment_id) {
        die("Invalid request.");
    }

    // Verify the assessment belongs to the student
    $stmt = $pdo->prepare("
        SELECT A.AssessmentID
        FROM ASSESSMENT A
        JOIN ENROLLMENT E ON A.EnrollmentID = E.EnrollmentID
        WHERE A.AssessmentID = ? AND E.StudentID = ?
    ");
    $stmt->execute([$assessment_id, $student_id]);
    if (!$stmt->fetch()) {
        die("Assessment not found or not yours.");
    }

    // Update status to completed
    $stmt = $pdo->prepare("UPDATE ASSESSMENT SET Status = 'completed' WHERE AssessmentID = ?");
    $stmt->execute([$assessment_id]);

    // Redirect back with success
    header("Location: view-progress.php?updated=1");
    exit;
} else {
    die("Invalid request.");
}
?>