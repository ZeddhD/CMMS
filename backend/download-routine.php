<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Access denied");
}

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['name'] ?? 'Student';

// Fetch class sessions
$stmt = $pdo->prepare("
    SELECT c.CourseTitle, c.CourseCode, cs.DayOfWeek, cs.StartTime, cs.EndTime, cs.Room, cs.Mode
    FROM ENROLLMENT e
    JOIN COURSE c ON e.CourseID = c.CourseID
    JOIN CLASS_SESSION cs ON cs.EnrollmentID = e.EnrollmentID
    WHERE e.StudentID = ?
    ORDER BY 
        CASE cs.DayOfWeek
            WHEN 'Sat' THEN 1 WHEN 'Sun' THEN 2 WHEN 'Mon' THEN 3 
            WHEN 'Tue' THEN 4 WHEN 'Wed' THEN 5 WHEN 'Thu' THEN 6 WHEN 'Fri' THEN 7
        END,
        cs.StartTime
");
$stmt->execute([$student_id]);
$sessions = $stmt->fetchAll();

$dayMap = [
    'Mon' => 'Monday', 
    'Tue' => 'Tuesday', 
    'Wed' => 'Wednesday', 
    'Thu' => 'Thursday', 
    'Fri' => 'Friday', 
    'Sat' => 'Saturday', 
    'Sun' => 'Sunday'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Routine - <?php echo htmlspecialchars($student_name); ?></title>
    <style>
        @media print {
            body { margin: 0; padding: 20px; }
            .no-print { display: none; }
        }
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0 0 10px 0;
            color: #1e40af;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #333;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #1e40af;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f3f4f6;
        }
        .mode-online {
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .mode-offline {
            background: #dcfce7;
            color: #16a34a;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .download-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #1e40af;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .download-btn:hover {
            background: #1e3a8a;
        }
        @page {
            margin: 20mm;
        }
    </style>
</head>
<body>
    <button class="download-btn no-print" onclick="window.print()">📥 Download PDF</button>
    
    <div class="header">
        <h1>📅 Class Schedule</h1>
        <p><strong><?php echo htmlspecialchars($student_name); ?></strong></p>
        <p>Generated on: <?php echo date('F j, Y - g:i A'); ?></p>
    </div>

    <?php if (count($sessions) > 0): ?>
        <table>
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
                <?php foreach ($sessions as $session): ?>
                    <?php
                    $day = $dayMap[$session['DayOfWeek']] ?? $session['DayOfWeek'];
                    $time = date('H:i', strtotime($session['StartTime']));
                    if ($session['EndTime']) {
                        $time .= ' - ' . date('H:i', strtotime($session['EndTime']));
                    }
                    $modeClass = $session['Mode'] === 'online' ? 'mode-online' : 'mode-offline';
                    ?>
                    <tr>
                        <td><strong><?php echo $day; ?></strong></td>
                        <td><?php echo $time; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($session['CourseTitle']); ?></strong><br>
                            <small style="color:#666;"><?php echo htmlspecialchars($session['CourseCode']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($session['Room']); ?></td>
                        <td><span class="<?php echo $modeClass; ?>"><?php echo strtoupper($session['Mode']); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align:center; padding:40px; color:#666; font-size:18px;">No classes scheduled yet.</p>
    <?php endif; ?>

    <script>
        // Auto-trigger print dialog for PDF download
        window.onload = function() {
            // Small delay to ensure page is fully rendered
            setTimeout(function() {
                window.print();
            }, 500);
        };
        
        // Close window after printing (optional)
        window.onafterprint = function() {
            // Optional: close the window after printing
            // window.close();
        };
    </script>
</body>
</html>
