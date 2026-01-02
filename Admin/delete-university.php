<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require '../backend/db.php';

if (isset($_GET['id'])) {
    $uniID = intval($_GET['id']);
    
    // Check if university exists
    $check = $pdo->prepare("SELECT UniName FROM UNIVERSITY WHERE UniID = ?");
    $check->execute([$uniID]);
    $university = $check->fetch();
    
    if (!$university) {
        die("University not found.");
    }
    
    // Delete the university
    try {
        $stmt = $pdo->prepare("DELETE FROM UNIVERSITY WHERE UniID = ?");
        $stmt->execute([$uniID]);
        
        // Success page
        ?>
        <!DOCTYPE html>
        <html>
        <head>
          <title>Deleted</title>
          <link rel="stylesheet" href="../assets/style.css">
        </head>
        <body style="display:flex; justify-content:center; align-items:center; height:100vh; background:var(--bg-dark);">
          <div class="auth-card" style="text-align:center;">
            <h2 style="color:var(--success);">✅ Deleted Successfully</h2>
            <p>University <strong><?= htmlspecialchars($university['UniName']) ?></strong> has been removed from the system.</p>
            <br>
            <a href="manage-universities.php" class="btn btn-primary">Back to Universities</a>
            <a href="../admin-dashboard.php" class="btn btn-ghost">Dashboard</a>
          </div>
        </body>
        </html>
        <?php
    } catch (PDOException $e) {
        // Handle foreign key constraint or other errors
        ?>
        <!DOCTYPE html>
        <html>
        <head>
          <title>Error</title>
          <link rel="stylesheet" href="../assets/style.css">
        </head>
        <body style="display:flex; justify-content:center; align-items:center; height:100vh; background:var(--bg-dark);">
          <div class="auth-card" style="text-align:center;">
            <h2 style="color:var(--error);">❌ Cannot Delete</h2>
            <p>This university cannot be deleted because it has associated records (students, courses, etc.).</p>
            <p style="color:var(--text-muted); font-size:0.9rem; margin-top:1rem;">Please remove all related data first.</p>
            <br>
            <a href="manage-universities.php" class="btn btn-primary">Back to Universities</a>
          </div>
        </body>
        </html>
        <?php
    }
} else {
    echo "Invalid request.";
}
?>
