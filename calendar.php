<?php
session_start();
require 'backend/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['name'] ?? 'Student';

// --- Date Calculation ---
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Month navigation logic
if ($month < 1) {
    $month = 12;
    $year--;
} elseif ($month > 12) {
    $month = 1;
    $year++;
}

// Next/Prev links
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

// Calendar metrics
$firstDayTimestamp = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDayTimestamp);
$monthName = date('F', $firstDayTimestamp);
// Note: 'N' returns 1 (Mon) to 7 (Sun). 'w' returns 0 (Sun) to 6 (Sat).
// Our DB uses 'Sat', 'Sun', 'Mon'...
// Let's standardise the week start. Let's start Monday (ISO-8601).
// 'N': 1 (Mon) - 1 = 0.
$firstDayIndex = date('N', $firstDayTimestamp) - 1; 

// --- FETCH DATA ---

// 1. Weekly Class Sessions
// We need to fetch the routine: CourseCode, DayOfWeek, Time
$weekly_sessions = [];
$sql_sessions = "
    SELECT 
        c.CourseCode,
        cs.DayOfWeek,
        cs.StartTime,
        cs.Room,
        cs.Mode
    FROM ENROLLMENT e
    JOIN COURSE c ON e.CourseID = c.CourseID
    JOIN CLASS_SESSION cs ON e.EnrollmentID = cs.EnrollmentID
    WHERE e.StudentID = ?
";
$stmt = $pdo->prepare($sql_sessions);
$stmt->execute([$student_id]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    // DB DayOfWeek: Sat, Sun, Mon, Tue, Wed, Thu, Fri
    // PHP 'D' format returns same 3-letter abbreviations
    $weekly_sessions[$row['DayOfWeek']][] = $row;
}

// 2. Scheduled Quizzes/Assessments for this specific month
$assessments_by_date = [];
$start_date_str = "$year-$month-01";
$end_date_str = "$year-$month-$daysInMonth";

$sql_assessments = "
    SELECT 
        c.CourseCode,
        a.Title,
        a.DueDate,
        a.AssessmentType,
        a.DueTime
    FROM ENROLLMENT e
    JOIN COURSE c ON e.CourseID = c.CourseID
    JOIN ASSESSMENT a ON e.EnrollmentID = a.EnrollmentID
    WHERE e.StudentID = ? 
    AND a.DueDate BETWEEN ? AND ?
    AND a.AssessmentType = 'Quiz'
";
// Note: Ensure AssessmentType matches exact DB string (case sensitive?)
// Schema defines ENUM('quiz', 'assignment', 'exam'). Let's match lowercase 'quiz'.

// Actually, check if it's 'quiz' or 'Quiz'. The schema said 'quiz'. Let's relax it or use lowercase.
// But earlier inserts might have used 'Quiz'.
// Let's adjust query to be safe or just fetch 'quiz'. 
// Actually, let's just fetch all types and filter or show them. User asked for "Quizzes".
// Let's rely on the query to filter for now.
$sql_assessments = "
    SELECT 
        c.CourseCode,
        a.Title,
        a.DueDate,
        a.AssessmentType,
        a.DueTime
    FROM ENROLLMENT e
    JOIN COURSE c ON e.CourseID = c.CourseID
    JOIN ASSESSMENT a ON e.EnrollmentID = a.EnrollmentID
    WHERE e.StudentID = ? 
    AND a.DueDate BETWEEN ? AND ?
    AND (a.AssessmentType = 'quiz' OR a.AssessmentType = 'Quiz')
";

$stmt2 = $pdo->prepare($sql_assessments);
$stmt2->execute([$student_id, $start_date_str, $end_date_str]);
$result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
foreach ($result2 as $row) {
    $assessments_by_date[$row['DueDate']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Calendar - CMMS</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Calendar Specific Styles */
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 12px;
            margin-top: 1rem;
        }
        
        .day-header {
            text-align: center;
            font-weight: 700;
            padding: 12px;
            background: rgba(255,255,255,0.03);
            border-radius: 8px;
            color: var(--primary-cyan);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.85rem;
        }

        .calendar-day {
            min-height: 140px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 12px;
            position: relative;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .calendar-day:hover {
            border-color: rgba(6, 182, 212, 0.4);
            transform: translateY(-2px);
        }

        .calendar-day.empty {
            background: transparent;
            border: none;
            pointer-events: none;
        }

        .calendar-day.today {
            background: rgba(6, 182, 212, 0.05); /* Slight tint for today */
            border-color: var(--primary-cyan);
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.1);
        }

        .date-number {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-light);
            display: flex;
            justify-content: space-between;
        }

        .event-item {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 600;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .event-class {
            background: rgba(6, 182, 212, 0.12);
            color: var(--primary-cyan-bright);
            border-left: 2px solid var(--primary-cyan);
        }

        .event-quiz {
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
            border-left: 2px solid var(--error);
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="index.html" class="logo">CMMS</a>
            <nav class="header-nav">
                <a href="student-dashboard.php">Dashboard</a>
                <a href="routine-download.php">Routine</a>
                <a href="calendar.php" class="active">Calendar</a>
                <a href="backend/logout.php" style="color:var(--error);">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <!-- Calendar Header / Controls -->
        <div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h1>🗓️ <?php echo "$monthName $year"; ?></h1>
                <p style="color:var(--text-muted);">Manage your academic schedule.</p>
            </div>
            <div class="button-group">
                <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="btn btn-secondary btn-sm">&larr; Prev</a>
                <a href="?month=<?php echo date('m'); ?>&year=<?php echo date('Y'); ?>" class="btn btn-secondary btn-sm">Today</a>
                <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="btn btn-secondary btn-sm">Next &rarr;</a>
            </div>
        </div>

        <div class="content-section">
            <div class="calendar-grid">
                <!-- Day Headers -->
                <div class="day-header">Mon</div>
                <div class="day-header">Tue</div>
                <div class="day-header">Wed</div>
                <div class="day-header">Thu</div>
                <div class="day-header">Fri</div>
                <div class="day-header">Sat</div>
                <div class="day-header">Sun</div>

                <?php
                // Empty slots before 1st of month
                for ($i = 0; $i < $firstDayIndex; $i++) {
                    echo '<div class="calendar-day empty"></div>';
                }

                // Days of month
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $currentDateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $timestamp = mktime(0, 0, 0, $month, $day, $year);
                    $dayAbbrev = date('D', $timestamp); // Mon, Tue...
                    
                    $isToday = ($currentDateStr === date('Y-m-d'));
                    $todayClass = $isToday ? 'today' : '';

                    echo "<div class='calendar-day $todayClass'>";
                    echo "<div class='date-number'>$day</div>";

                    // 1. Render Quizzes (Top Priority)
                    if (isset($assessments_by_date[$currentDateStr])) {
                        foreach ($assessments_by_date[$currentDateStr] as $quiz) {
                            $title = htmlspecialchars($quiz['CourseCode']);
                            echo "<div class='event-item event-quiz' title='Quiz'>";
                            echo "<span>📝</span> $title Quiz";
                            echo "</div>";
                        }
                    }

                    // 2. Render Classes calling logic
                    // If this day of week exists in our weekly routine, show the class
                    // BUT only if we assume classes run every week.
                    // (Simplification: We assume routines apply to the whole month)
                    if (isset($weekly_sessions[$dayAbbrev])) {
                        foreach ($weekly_sessions[$dayAbbrev] as $session) {
                            $course = htmlspecialchars($session['CourseCode']);
                            $time = date('H:i', strtotime($session['StartTime']));
                            echo "<div class='event-item event-class' title='Class'>";
                            echo "<span>🏫</span> $course ($time)";
                            echo "</div>";
                        }
                    }

                    echo "</div>"; // end calendar-day
                }
                ?>
            </div>
        </div>
    </main>
</body>
</html>
