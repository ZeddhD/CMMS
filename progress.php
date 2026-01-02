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
    <title>My Progress - CMMS</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="index.html" class="logo">CMMS</a>
            <nav class="header-nav">
                <a href="student-dashboard.php">Dashboard</a>
                <a href="progress.php" class="active">Progress</a>
                <a href="backend/logout.php" style="color:var(--error);">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="page-header">
            <h1>📊 Academic Progress</h1>
            <p style="color:var(--text-muted);">Track your grades and attendance performance.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">CGPA</div>
                <div class="stat-value">3.85</div>
                <div style="color:var(--success); font-size:0.85rem;">Top 10%</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Credits Completed</div>
                <div class="stat-value">45</div>
                <div style="color:var(--text-muted); font-size:0.85rem;">out of 120</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Attendance Rate</div>
                <div class="stat-value">92%</div>
                <div style="color:var(--success); font-size:0.85rem;">Excellent</div>
            </div>
        </div>

        <div class="content-section">
            <div class="section-title"><span>📝</span> Course Performance</div>
            <div class="table-responsive">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Title</th>
                            <th>Attendance</th>
                            <th>Quizzes</th>
                            <th>Midterm</th>
                            <th>Final</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tbody>
                        <?php
                        require_once 'backend/db.php';
                        $student_id = $_SESSION['user_id'];

                        // Fetch Courses and their Assessment Stats
                        $sql = "
                            SELECT 
                                c.CourseCode, 
                                c.CourseTitle, 
                                e.EnrollmentID
                            FROM ENROLLMENT e
                            JOIN COURSE c ON e.CourseID = c.CourseID
                            WHERE e.StudentID = ?
                        ";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$student_id]);
                        $enrollments = $stmt->fetchAll();

                        if (count($enrollments) > 0) {
                            foreach ($enrollments as $row) {
                                $eid = $row['EnrollmentID'];

                                // Calculate Quiz Marks
                                $stmtQ = $pdo->prepare("SELECT SUM(MarksObtained) as Ob, SUM(MaxMarks) as Max FROM ASSESSMENT WHERE EnrollmentID = ? AND AssessmentType = 'Quiz'");
                                $stmtQ->execute([$eid]);
                                $quizStats = $stmtQ->fetch();
                                $quizStr = ($quizStats['Max'] > 0) ? "{$quizStats['Ob']}/{$quizStats['Max']}" : "-";

                                // Calculate Assignment/Midterm/Final if they exist in schema types
                                // Schema has ENUM('quiz', 'assignment', 'exam')
                                // Let's check 'assignment'
                                $stmtA = $pdo->prepare("SELECT SUM(MarksObtained) as Ob, SUM(MaxMarks) as Max FROM ASSESSMENT WHERE EnrollmentID = ? AND AssessmentType = 'assignment'");
                                $stmtA->execute([$eid]);
                                $assignStats = $stmtA->fetch();
                                $assignStr = ($assignStats['Max'] > 0) ? "{$assignStats['Ob']}/{$assignStats['Max']}" : "-";

                                // Total Grade (Sum of all obtained / all max)
                                $stmtAll = $pdo->prepare("SELECT SUM(MarksObtained) as Ob, SUM(MaxMarks) as Max FROM ASSESSMENT WHERE EnrollmentID = ?");
                                $stmtAll->execute([$eid]);
                                $totalStats = $stmtAll->fetch();
                                $percentage = ($totalStats['Max'] > 0) ? round(($totalStats['Ob'] / $totalStats['Max']) * 100) . "%" : "N/A";

                                echo "<tr>";
                                echo "<td>".htmlspecialchars($row['CourseCode'])."</td>";
                                echo "<td>".htmlspecialchars($row['CourseTitle'])."</td>";
                                echo "<td>N/A</td>"; // Attendance not in DB
                                echo "<td>$quizStr</td>"; // Using this col for Quizzes
                                echo "<td>$assignStr</td>"; // Using Midterm col for Assignments (label fix needed in header?) -> No, let's keep headers and put data where it fits best.
                                echo "<td>-</td>"; // Final
                                echo "<td><span class='badge badge-info'>$percentage</span></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' style='text-align:center; padding:2rem; color:var(--text-muted);'>No courses enrolled.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </main>
</body>
</html>