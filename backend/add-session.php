<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['user_id'] ?? null;
    if (!$student_id) {
        die("❌ You must be logged in to perform this action.");
    }

    $enrollment_id = $_POST['enrollment_id'] ?? null;
    
    // Validate required fields
    $day   = $_POST['day'] ?? null;
    $start = $_POST['start_time'] ?? null;
    $end   = $_POST['end_time'] ?? null;
    $room  = $_POST['room'] ?? null;
    $mode  = $_POST['mode'] ?? null;

    if (!$enrollment_id || !$day || !$start || !$end || !$room || !$mode) {
        header("Location: ../course-details.php?enrollment_id=$enrollment_id&error=missing_fields");
        exit;
    }

    // Verify ownership
    $stmt = $pdo->prepare("SELECT EnrollmentID FROM ENROLLMENT WHERE EnrollmentID = ? AND StudentID = ?");
    $stmt->execute([$enrollment_id, $student_id]);
    if (!$stmt->fetch()) {
        die("❌ Access denied.");
    }

    $stmt = $pdo->prepare("
        INSERT INTO CLASS_SESSION (EnrollmentID, DayOfWeek, StartTime, EndTime, Room, Mode)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$enrollment_id, $day, $start, $end, $room, $mode]);

    header("Location: ../course-details.php?enrollment_id=$enrollment_id&success=session_added");
    exit;
} else {
    echo "Invalid request.";
}
?>
