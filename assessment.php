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
  <title>Add Assessment - CMMS</title>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body>
  <header>
    <div class="header-container">
      <a href="index.html" class="logo">CMMS</a>
      <nav class="header-nav">
        <a href="student-dashboard.php">Dashboard</a>
        <a href="assessment.php" class="active">Add Assessment</a>
        <a href="backend/logout.php" style="color:var(--error);">Logout</a>
      </nav>
    </div>
  </header>

  <main>
    <div class="form-card">
      <div class="section-title" style="justify-content:center; margin-bottom:0.5rem;">
        <span>📝</span> Add Assessment
      </div>
      <p style="text-align:center; color:var(--text-muted); margin-bottom:2rem;">Log a new quiz, assignment, or exam.
      </p>

      <form action="backend/add-assessment.php" method="POST">
        <div class="form-group">
          <label for="course_code">Course Code</label>
          <div class="field-wrapper">
            <input type="text" id="course_code" name="course_code" placeholder="e.g. CSE370" required>
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
          <div class="form-group">
            <label>Assessment Type</label>
            <div class="field-wrapper">
              <select name="type" required>
                <option value="Quiz">Quiz</option>
                <option value="Assignment">Assignment</option>
                <option value="Lab">Lab Task</option>
                <option value="Project">Project</option>
                <option value="Midterm">Midterm</option>
                <option value="Final">Final</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Weight (%)</label>
            <div class="field-wrapper">
              <input type="number" name="weight" placeholder="e.g. 10" min="0" max="100">
            </div>
          </div>
        </div>

        <div class="form-group">
          <label>Title / Topic</label>
          <div class="field-wrapper">
            <input type="text" name="title" placeholder="e.g. Chapter 1 Quiz" required>
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
          <div class="form-group">
            <label>Due Date</label>
            <div class="field-wrapper">
              <input type="date" name="due_date" required>
            </div>
          </div>
          <div class="form-group">
            <label>Total Marks</label>
            <div class="field-wrapper">
              <input type="number" name="total_marks" placeholder="e.g. 20">
            </div>
          </div>
        </div>

        <div class="form-group">
          <label>Status</label>
          <div class="field-wrapper">
            <select name="status">
              <option value="Pending">Pending</option>
              <option value="Completed">Completed</option>
              <option value="Graded">Graded</option>
            </select>
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;">Add Assessment</button>
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