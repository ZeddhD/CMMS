<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Unauthorized access");
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['user_id'] ?? null;
    if (!$student_id) {
        die("❌ You must be logged in to perform this action.");
    }
    $course_code = trim($_REQUEST['course_code'] ?? '');
    $course_title = trim($_REQUEST['course_title'] ?? '');
    $stmt = $pdo->prepare("SELECT CourseID FROM COURSE WHERE CourseCode = ? AND CourseTitle = ?");
    $stmt->execute([$course_code, $course_title]);
    $course = $stmt->fetch();
    if (!$course) {
        die("❌ Course not found.");
    }
    $course_id = $course['CourseID'];

    // Fetch enrollment ID
    $stmt = $pdo->prepare("SELECT EnrollmentID, BestOfQuizCount FROM ENROLLMENT WHERE StudentID = ? AND CourseID = ?");
    $stmt->execute([$student_id, $course_id]);
    $enrollment = $stmt->fetch();

    if (!$enrollment) {
        echo "Enrollment not found.";
        exit;
    }

    $enrollment_id = $enrollment['EnrollmentID'];
    $best_of = $enrollment['BestOfQuizCount'] ?? 3;

    // Fetch assessments
    $stmt = $pdo->prepare("
        SELECT * FROM ASSESSMENT
        WHERE EnrollmentID = ?
        ORDER BY DueDate ASC
    ");
    $stmt->execute([$enrollment_id]);
    $assessments = $stmt->fetchAll();


    $quiz_completed = 0;
    $total_quizzes = 0;

    foreach ($assessments as $a) {
        if ($a['AssessmentType'] === 'Quiz') {
            $total_quizzes++;
            if ($a['Status'] === 'completed') {
                $quiz_completed++;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assessment Progress</title>
    <link rel="stylesheet" href="../assets/style.css">

    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>

<nav>
  <a href="../student-dashboard.php">← Back to Dashboard</a>
</nav>

<main class="container">
  <section style="padding:32px 0">

<h2>📈 Assessment Progress</h2>

<?php if (!isset($assessments)) { ?>
    <p>Invalid access. Please select a course from the progress overview.</p>
<?php } else { ?>

    <p>Course: <?php echo htmlspecialchars($course_title); ?> (<?php echo htmlspecialchars($course_code); ?>)</p>

    <?php if ($_GET['updated'] ?? null): ?>
        <p style="color: green;">✅ Assessment marked as completed.</p>
    <?php endif; ?>

    <table>
        <tr>
            <th>Type</th>
            <th>Title</th>
            <th>Max Marks</th>
            <th>Marks Obtained</th>
            <th>Status</th>
            <th>Mandatory</th>
            <th>Action</th>
        </tr>

        <?php foreach ($assessments as $a): ?>
            <tr>
                <td><?php echo $a['AssessmentType']; ?></td>
                <td><?php echo $a['Title']; ?></td>
                <td><?php echo $a['MaxMarks']; ?></td>
                <td><?php echo $a['MarksObtained'] ?? '-'; ?></td>
                <td><?php echo $a['Status']; ?></td>
                <td><?php echo $a['IsMandatory'] ? 'Yes' : 'No'; ?></td>
                <td>
                    <?php if ($a['Status'] !== 'completed'): ?>
                        <form method="POST" action="mark-complete.php" style="display:inline;">
                            <input type="hidden" name="assessment_id" value="<?php echo $a['AssessmentID']; ?>">
                            <button type="submit">Mark as Completed</button>
                        </form>
                    <?php else: ?>
                        ✅ Done
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <br><br>

    <h3>� Add New Assessment</h3>
    <form method="POST" action="add-assessment.php">
        <input type="hidden" name="course_code" value="<?php echo htmlspecialchars($course_code); ?>">
        <input type="hidden" name="course_title" value="<?php echo htmlspecialchars($course_title); ?>">
        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
            <select name="type" required>
                <option value="">Type</option>
                <option value="quiz">Quiz</option>
                <option value="assignment">Assignment</option>
                <option value="exam">Exam</option>
            </select>
            <input type="text" name="title" placeholder="Title" required>
            <input type="number" name="max_marks" placeholder="Max Marks" required>
            <input type="number" name="weight" placeholder="Weight" required>
            <input type="date" name="due_date" required>
            <input type="time" name="due_time" required>
            <select name="status" required>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
            </select>
            <select name="mandatory" required>
                <option value="1">Mandatory</option>
                <option value="0">Optional</option>
            </select>
            <input type="number" name="marks_obtained" placeholder="Marks Obtained (optional)">
        </div>
        <button type="submit">Add Assessment</button>
    </form>

<?php } ?>

  </section>
</main>

</body>
</html>
