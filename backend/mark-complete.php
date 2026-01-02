<?php
session_start();
require '../includes/db.php';

$assessment_id = $_POST['assessment_id'] ?? null;
$return_url = $_POST['return_url'] ?? '../student-dashboard.php';
$student_id = $_SESSION['student_id'] ?? null;

if ($assessment_id && $student_id) {
    // Verify ownership - ensure this assessment belongs to this student's enrollment
    $stmt = $pdo->prepare("
        SELECT a.AssessmentID 
        FROM ASSESSMENT a
        JOIN ENROLLMENT e ON a.EnrollmentID = e.EnrollmentID
        WHERE a.AssessmentID = ? AND e.StudentID = ?
    ");
    $stmt->execute([$assessment_id, $student_id]);
    
    if ($stmt->fetch()) {
        // Update status to completed
        $stmt = $pdo->prepare("UPDATE ASSESSMENT SET Status = 'completed' WHERE AssessmentID = ?");
        $stmt->execute([$assessment_id]);
    }
}

// Redirect back to where we came from
header("Location: " . $return_url);
exit;
?>