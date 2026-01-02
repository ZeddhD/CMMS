<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $type = $_POST['type'] ?? '';
    $courseCode = $_POST['course_code'] ?? '';
    $userId = $_SESSION['user_id'];
    $url_or_path = '';
    $return_url = $_POST['return_url'] ?? '../study-material.php';

    if (empty($title) || empty($type)) {
        header("Location: $return_url?error=missing_fields");
        exit();
    }

    // Lookup CourseID if course code provided
    $courseId = null;
    if (!empty($courseCode)) {
        $stmt = $pdo->prepare("SELECT CourseID FROM COURSE WHERE CourseCode = ?");
        $stmt->execute([$courseCode]);
        $course = $stmt->fetch();
        if ($course) {
            $courseId = $course['CourseID'];
        }
    }

    if ($type === 'Link') {
        $url_or_path = $_POST['link_url'] ?? '';
        if (!filter_var($url_or_path, FILTER_VALIDATE_URL)) {
             header("Location: $return_url?error=invalid_url");
             exit();
        }
    } else {
        // File Upload - check both field names
        $fileField = isset($_FILES['file_upload']) ? 'file_upload' : 'file';
        
        if (isset($_FILES[$fileField]) && $_FILES[$fileField]['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = basename($_FILES[$fileField]['name']);
            // Safe filename
            $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
            $targetPath = $uploadDir . time() . '_' . $fileName;
            
            // Allow any file type - no validation
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (move_uploaded_file($_FILES[$fileField]['tmp_name'], $targetPath)) {
                $url_or_path = 'uploads/' . basename($targetPath); // Store relative path
            } else {
                header("Location: $return_url?error=upload_failed");
                exit();
            }
        } else {
            // Check if file was uploaded but with error
            $errorCode = $_FILES[$fileField]['error'] ?? UPLOAD_ERR_NO_FILE;
            header("Location: $return_url?error=no_file&code=$errorCode");
            exit();
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO STUDY_MATERIAL (StudentID, CourseID, Title, MaterialType, URL_or_Path, UploadDate) VALUES (?, ?, ?, ?, ?, CURDATE())");
        $stmt->execute([$userId, $courseId, $title, $type, $url_or_path]);
        
        header("Location: $return_url?success=uploaded");
    } catch (PDOException $e) {
        header("Location: $return_url?error=database");
        exit();
    }
}
?>
