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
  <title>Add Class Session - CMMS</title>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body>
  <header>
    <div class="header-container">
      <a href="index.html" class="logo">CMMS</a>
      <nav class="header-nav">
        <a href="student-dashboard.php">Dashboard</a>
        <a href="class-session.php" class="active">Add Session</a>
        <a href="backend/logout.php" style="color:var(--error);">Logout</a>
      </nav>
    </div>
  </header>

  <main>
    <div class="form-card">
      <div class="section-title" style="justify-content:center; margin-bottom:0.5rem;">
        <span>📅</span> Add Class Session
      </div>
      <p style="text-align:center; color:var(--text-muted); margin-bottom:2rem;">Configure your weekly schedule for a
        course.</p>

      <form action="backend/add-session.php" method="POST">
        <!-- Course Info -->
        <h3
          style="color:var(--primary-cyan); margin-bottom:1rem; font-size:1.1rem; border-bottom:1px solid var(--border-color); padding-bottom:0.5rem;">
          Course Details</h3>

        <div class="form-group">
          <label for="course_code">Course Code</label>
          <div class="field-wrapper">
            <input type="text" id="course_code" name="course_code" placeholder="e.g. CSE370" required>
          </div>
        </div>

        <!-- Session 1 -->
        <h3
          style="color:var(--primary-cyan); margin:1.5rem 0 1rem; font-size:1.1rem; border-bottom:1px solid var(--border-color); padding-bottom:0.5rem;">
          Session 1</h3>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
          <div class="form-group">
            <label>Day</label>
            <div class="field-wrapper">
              <select name="day1" required>
                <option value="" disabled selected>Select Day</option>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
                <option value="Saturday">Saturday</option>
                <option value="Sunday">Sunday</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Start Time</label>
            <div class="field-wrapper">
              <input type="time" name="time1" required>
            </div>
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
          <div class="form-group">
            <label>Room / Link</label>
            <div class="field-wrapper">
              <input type="text" name="room1" placeholder="Room 302 or Zoom Link">
            </div>
          </div>
          <div class="form-group">
            <label>Mode</label>
            <div class="field-wrapper">
              <select name="type1">
                <option value="offline">Offline</option>
                <option value="online">Online</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Session 2 -->
        <h3
          style="color:var(--primary-cyan); margin:1.5rem 0 1rem; font-size:1.1rem; border-bottom:1px solid var(--border-color); padding-bottom:0.5rem;">
          Session 2 (Optional)</h3>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
          <div class="form-group">
            <label>Day</label>
            <div class="field-wrapper">
              <select name="day2">
                <option value="" disabled selected>Select Day</option>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
                <option value="Saturday">Saturday</option>
                <option value="Sunday">Sunday</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Start Time</label>
            <div class="field-wrapper">
              <input type="time" name="time2">
            </div>
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
          <div class="form-group">
            <label>Room / Link</label>
            <div class="field-wrapper">
              <input type="text" name="room2" placeholder="Room 302 or Zoom Link">
            </div>
          </div>
          <div class="form-group">
            <label>Mode</label>
            <div class="field-wrapper">
              <select name="type2">
                <option value="offline" selected>Offline</option>
                <option value="online">Online</option>
              </select>
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; margin-top:1rem;">Save Schedule</button>
      </form>
    </div>
  </main>

  <script>
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error')) {
      const card = document.querySelector('.form-card .section-title');
      const alert = document.createElement('div');
      alert.className = 'alert alert-error';
      alert.textContent = 'Please fill in required fields for at least Session 1.';
      card.parentNode.insertBefore(alert, card.nextSibling);
    }
  </script>
</body>

</html>