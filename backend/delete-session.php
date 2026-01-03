<?php
session_start();
require 'db.php';

// ============================================
// AUTHENTICATION CHECK
// ============================================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// ============================================
// HANDLE DELETE REQUEST
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['user_id'];
    $session_id = $_POST['session_id'] ?? null;
    $enrollment_id = $_POST['enrollment_id'] ?? null;

    // Validate required fields
    if (!$session_id || !$enrollment_id) {
        header("Location: ../course-details.php?enrollment_id=$enrollment_id&error=missing_data");
        exit();
    }

    // ============================================
    // SECURITY: Verify Ownership
    // ============================================
    // Double-check that this session belongs to an enrollment owned by this student
    $stmt = $pdo->prepare("
        SELECT cs.ClassSessionID 
        FROM CLASS_SESSION cs
        JOIN ENROLLMENT e ON cs.EnrollmentID = e.EnrollmentID
        WHERE cs.ClassSessionID = ? 
        AND e.EnrollmentID = ? 
        AND e.StudentID = ?
    ");
    $stmt->execute([$session_id, $enrollment_id, $student_id]);
    
    if (!$stmt->fetch()) {
        // Session doesn't belong to this student - deny access
        header("Location: ../course-details.php?enrollment_id=$enrollment_id&error=access_denied");
        exit();
    }

    // ============================================
    // DELETE SESSION
    // ============================================
    try {
        $stmt = $pdo->prepare("DELETE FROM CLASS_SESSION WHERE ClassSessionID = ?");
        $stmt->execute([$session_id]);
        
        // Success - redirect back to course details
        header("Location: ../course-details.php?enrollment_id=$enrollment_id&success=session_deleted");
        exit();
        
    } catch (PDOException $e) {
        // Database error
        error_log("Delete session error: " . $e->getMessage());
        header("Location: ../course-details.php?enrollment_id=$enrollment_id&error=delete_failed");
        exit();
    }
    
} else {
    // Not a POST request
    header("Location: ../course-details.php");
    exit();
}
?>
