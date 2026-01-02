<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html>
<head>
  <title>Initial Setup</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>

  <h2>Welcome, <?php echo htmlspecialchars($name); ?> 👋</h2>

  <p>
    This is your first time here.  
    Please complete the steps below to set up your semester.
  </p>

  <hr>

  <h3>Step 1: Enroll in Your Courses</h3>
  <a href="enroll.php"><button>Enroll in Course</button></a>

  <h3>Step 2: Add Class Routine</h3>
  <a href="class-session.php"><button>Add Class Routine</button></a>

  <h3>Step 3: Add Quizzes, Assignments & Exams</h3>
  <a href="assessment.php"><button>Add Assessments</button></a>

  <h3>Step 4: Upload Study Materials</h3>
  <a href="study-material.php"><button>Upload Study Materials</button></a>

  <hr>

  <h3>All done?</h3>
  <p>
    When you have finished setting up, go to your dashboard.
  </p>

  <a href="student-dashboard.php">
    <button>Go to Dashboard</button>
  </a>

  <br><br>

  <a href="backend/logout.php">Logout</a>

</body>
</html>
