<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enroll in Course - CMMS</title>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body>
  <header>
    <div class="header-container">
      <a href="index.html" class="logo">CMMS</a>
      <nav class="header-nav">
        <a href="student-dashboard.php">Dashboard</a>
        <a href="enroll.php" class="active">Enroll</a>
        <a href="backend/logout.php" style="color:var(--error);">Logout</a>
      </nav>
    </div>
  </header>

  <main>
    <div class="form-card">
      <div class="section-title" style="justify-content:center; margin-bottom:0.5rem;">
        <span>📝</span> Enroll in Course
      </div>
      <p style="text-align:center; color:var(--text-muted); margin-bottom:2rem;">Add a new course to your semester
        schedule.</p>

      <form action="backend/enroll.php" method="POST">
        <div class="form-group">
          <label for="course_code">Course Code</label>
          <div class="field-wrapper">
            <input type="text" id="course_code" name="course_code" placeholder="e.g. CSE370" required>
          </div>
        </div>

        <div class="form-group">
          <label for="course_title">Course Title</label>
          <div class="field-wrapper">
            <input type="text" id="course_title" name="course_title" placeholder="e.g. Database Systems" required>
          </div>
        </div>

        <div class="form-group">
          <label for="semester">Semester</label>
          <div class="field-wrapper">
            <input type="text" id="semester" name="semester" placeholder="e.g. Fall 2024" required>
          </div>
        </div>

        <div class="form-group">
          <label>Assessment Plan</label>
          <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
            <div class="field-wrapper">
              <input type="number" name="quizzes" placeholder="Quizzes (count)" min="0">
            </div>
            <div class="field-wrapper">
              <input type="number" name="assignments" placeholder="Assignments (count)" min="0">
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;">Enroll Now</button>
      </form>
    </div>
  </main>

  <script>
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error')) {
      const card = document.querySelector('.form-card .section-title');
      const alert = document.createElement('div');
      alert.className = 'alert alert-error';
      alert.textContent = 'Please fill in all required fields.';
      card.parentNode.insertBefore(alert, card.nextSibling);
    }
  </script>
</body>

</html>