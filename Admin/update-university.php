<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require '../backend/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);

    if (!$id || !$name || !$location) {
        die("All fields required.");
    }

    // Check for duplicate name (excluding current university)
    $check = $pdo->prepare("SELECT * FROM UNIVERSITY WHERE UniName = ? AND UniID != ?");
    $check->execute([$name, $id]);
    if ($check->fetch()) {
        die("Another university with this name already exists.");
    }

    // Update the university
    $stmt = $pdo->prepare("UPDATE UNIVERSITY SET UniName = ?, Location = ? WHERE UniID = ?");
    $stmt->execute([$name, $location, $id]);

    // Success
    ?>
    <!DOCTYPE html>
    <html>
    <head>
      <title>Success</title>
      <link rel="stylesheet" href="../assets/style.css">
    </head>
    <body style="display:flex; justify-content:center; align-items:center; height:100vh; background:var(--bg-dark);">
      <div class="auth-card" style="text-align:center;">
        <h2 style="color:var(--success);">✅ Updated Successfully</h2>
        <p>University <strong><?= htmlspecialchars($name) ?></strong> has been updated.</p>
        <br>
        <a href="manage-universities.php" class="btn btn-primary">Back to Universities</a>
        <a href="../admin-dashboard.php" class="btn btn-ghost">Dashboard</a>
      </div>
    </body>
    </html>
    <?php
} else {
    echo "Invalid request.";
}
?>
