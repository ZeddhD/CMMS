<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Routine - CMMS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <a href="index.html" class="logo">CMMS</a>
            <nav class="header-nav">
                <a href="student-dashboard.php">Dashboard</a>
                <a href="routine-download.php" class="active">Routine</a>
                <a href="calendar.php">Calendar</a>
                <a href="backend/logout.php" style="color:var(--error);">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h1 style="font-size:1.8rem; margin-bottom:0.5rem;">📅 Class Schedule</h1>
                <p style="color:var(--text-muted);">View your weekly class routine.</p>
            </div>
            <a href="backend/download-routine.php" class="btn btn-primary">📥 Download Routine</a>
        </div>

        <div class="content-section">
            <div class="table-responsive">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Course</th>
                            <th>Room / Link</th>
                            <th>Mode</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tbody>
                        <?php
                        // Fetch Routine
                        require_once 'backend/db.php';
                        $student_id = $_SESSION['user_id'];
                        
                        $sql = "
                            SELECT cs.DayOfWeek, cs.StartTime, cs.EndTime, cs.Room, cs.Mode, c.CourseTitle, c.CourseCode
                            FROM CLASS_SESSION cs
                            JOIN ENROLLMENT e ON cs.EnrollmentID = e.EnrollmentID
                            JOIN COURSE c ON e.CourseID = c.CourseID
                            WHERE e.StudentID = ?
                            ORDER BY 
                                CASE cs.DayOfWeek
                                    WHEN 'Sat' THEN 1 WHEN 'Sun' THEN 2 WHEN 'Mon' THEN 3 
                                    WHEN 'Tue' THEN 4 WHEN 'Wed' THEN 5 WHEN 'Thu' THEN 6 WHEN 'Fri' THEN 7
                                END,
                                cs.StartTime
                        ";
                        
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$student_id]);
                        $sessions = $stmt->fetchAll();

                        if (count($sessions) > 0) {
                            foreach ($sessions as $session) {
                                $day = htmlspecialchars($session['DayOfWeek']); // Maps to 'Mon', 'Tue' etc.
                                // Map short day to full day name if desired, or keep as is. Let's make it look nice.
                                $dayMap = ['Mon'=>'Monday', 'Tue'=>'Tuesday', 'Wed'=>'Wednesday', 'Thu'=>'Thursday', 'Fri'=>'Friday', 'Sat'=>'Saturday', 'Sun'=>'Sunday'];
                                $fullDay = $dayMap[$day] ?? $day;

                                $time = date('H:i', strtotime($session['StartTime']));
                                if ($session['EndTime']) {
                                    $time .= ' - ' . date('H:i', strtotime($session['EndTime']));
                                }
                                
                                $modeBadge = ($session['Mode'] === 'online') 
                                    ? '<span class="badge badge-info">Online</span>' 
                                    : '<span class="badge badge-success">Offline</span>';

                                echo "<tr>";
                                echo "<td><strong>$fullDay</strong></td>";
                                echo "<td>$time</td>";
                                echo "<td>
                                        <div style='font-weight:700;'>".htmlspecialchars($session['CourseTitle'])."</div>
                                        <div style='font-size:0.85rem; color:var(--text-muted);'>".htmlspecialchars($session['CourseCode'])."</div>
                                      </td>";
                                echo "<td>".htmlspecialchars($session['Room'])."</td>";
                                echo "<td>$modeBadge</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align:center; padding:2rem; color:var(--text-muted);'>No classes scheduled yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
