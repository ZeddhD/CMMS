<?php
session_start();
require '../backend/db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Verify student ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage-students.php?error=invalid_id");
    exit();
}

$student_id = (int)$_GET['id'];

try {
    // Start transaction for data integrity
    $pdo->beginTransaction();
    
    // Delete in correct order due to foreign key constraints
    
    // 1. Delete study materials uploaded by this student
    $stmt = $pdo->prepare("DELETE FROM study_material WHERE StudentID = ?");
    $stmt->execute([$student_id]);
    
    // 2. Get all enrollments for this student
    $stmt = $pdo->prepare("SELECT EnrollmentID FROM ENROLLMENT WHERE StudentID = ?");
    $stmt->execute([$student_id]);
    $enrollments = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($enrollments)) {
        $placeholders = implode(',', array_fill(0, count($enrollments), '?'));
        
        // 3. Delete assessments for these enrollments
        $stmt = $pdo->prepare("DELETE FROM ASSESSMENT WHERE EnrollmentID IN ($placeholders)");
        $stmt->execute($enrollments);
        
        // 4. Delete class sessions for these enrollments
        $stmt = $pdo->prepare("DELETE FROM CLASS_SESSION WHERE EnrollmentID IN ($placeholders)");
        $stmt->execute($enrollments);
    }
    
    // 5. Delete enrollments
    $stmt = $pdo->prepare("DELETE FROM ENROLLMENT WHERE StudentID = ?");
    $stmt->execute([$student_id]);
    
    // 6. Finally, delete the student
    $stmt = $pdo->prepare("DELETE FROM STUDENT WHERE StudentID = ?");
    $stmt->execute([$student_id]);
    
    // Commit transaction
    $pdo->commit();
    
    // Redirect with success message
    header("Location: manage-students.php?success=deleted");
    exit();
    
} catch (Exception $e) {
    // Rollback on error
    $pdo->rollBack();
    header("Location: manage-students.php?error=delete_failed");
    exit();
}
?>
