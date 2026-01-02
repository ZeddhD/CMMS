<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add University - CMMS Admin</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
  <header>
    <div class="header-container">
      <a href="../index.html" class="logo">CMMS <span
          style="font-size:0.8rem; opacity:0.7; margin-left:5px;">ADMIN</span></a>
      <nav class="header-nav">
        <a href="../admin-dashboard.php">Dashboard</a>
        <a href="manage-universities.php" class="active">Universities</a>
        <a href="manage-students.php">Students</a>
        <a href="../backend/logout.php" style="color:var(--error);">Logout</a>
      </nav>
    </div>
  </header>

  <main>
    <div class="form-card">
      <div class="section-title" style="justify-content:center; margin-bottom:0.5rem;">
        <span>🏛️</span> Add University
      </div>
      <p style="text-align:center; color:var(--text-muted); margin-bottom:2rem;">Register a new institution in the
        system.</p>

      <form action="insert-university.php" method="POST">
        <div class="form-group">
          <label for="name">University Name</label>
          <div class="field-wrapper">
            <input type="text" id="name" name="name" placeholder="e.g. Dhaka University" required>
          </div>
        </div>

        <div class="form-group">
          <label for="location">Location</label>
          <div class="field-wrapper">
            <input type="text" id="location" name="location" placeholder="e.g. Dhaka, Bangladesh" required>
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;">Add University</button>
      </form>

      <div style="text-align:center; margin-top:1.5rem;">
        <a href="manage-universities.php" style="color:var(--text-muted); font-size:0.9rem;">&larr; Back to List</a>
      </div>
    </div>
  </main>
</body>

</html>