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

    if (empty($title) || empty($type)) {
        header("Location: ../study-material.php?error=missing_fields");
        exit();
    }

    // Lookup CourseID if course code provided
    $courseId = null;
    if (!empty($courseCode)) {
        $stmt = $pdo->prepare("SELECT CourseID FROM course WHERE CourseCode = ?");
        $stmt->execute([$courseCode]);
        $course = $stmt->fetch();
        if ($course) {
            $courseId = $course['CourseID'];
        }
    }

    if ($type === 'Link') {
        $url_or_path = $_POST['link_url'] ?? '';
        if (!filter_var($url_or_path, FILTER_VALIDATE_URL)) {
             header("Location: ../study-material.php?error=invalid_url");
             exit();
        }
    } else {
        // File Upload
        if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = basename($_FILES['file_upload']['name']);
            // Safe filename
            $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
            $targetPath = $uploadDir . time() . '_' . $fileName;
            
            // Validate file type
            $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'ppt', 'pptx', 'txt'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExt, $allowedTypes)) {
                header("Location: ../study-material.php?error=invalid_type");
                exit();
            }

            if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $targetPath)) {
                $url_or_path = 'uploads/' . basename($targetPath); // Store relative path
            } else {
                header("Location: ../study-material.php?error=upload_failed");
                exit();
            }
        } else {
            header("Location: ../study-material.php?error=no_file");
            exit();
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO study_material (StudentID, CourseID, Title, Type, URL_or_Path) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $courseId, $title, $type, $url_or_path]);
        header("Location: ../study-material.php?success=uploaded");
    } catch (PDOException $e) {
         // header("Location: ../study-material.php?error=db_error");
          echo $e->getMessage();
    }
}
?>
