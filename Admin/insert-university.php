<?php
require '../backend/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);

    if (!$name || !$location) {
        die("All fields required.");
    }

    // Check for duplicate
    $check = $pdo->prepare("SELECT * FROM UNIVERSITY WHERE UniName = ?");
    $check->execute([$name]);
    if ($check->fetch()) {
        die("University already exists.");
    }

    $stmt = $pdo->prepare("INSERT INTO UNIVERSITY (UniName, Location) VALUES (?, ?)");
    $stmt->execute([$name, $location]);

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
        <h2 style="color:var(--success);">✅ Success</h2>
        <p>University <strong><?= htmlspecialchars($name) ?></strong> added successfully.</p>
        <br>
        <a href="add-university.php" class="btn btn-primary">Add Another</a>
        <a href="../admin-dashboard.php" class="btn btn-ghost">Back to Dashboard</a>
      </div>
    </body>
    </html>
    <?php
} else {
    echo "Invalid request.";
}
