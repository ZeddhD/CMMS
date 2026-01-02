<?php
session_start();
require 'backend/db.php';
require 'vendor/dompdf/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Access denied");
}

$student_id = $_SESSION['user_id'];

// Fetch class sessions
$stmt = $pdo->prepare("
    SELECT C.CourseTitle, C.CourseCode, CS.DayOfWeek, CS.StartTime, CS.EndTime, CS.Room
    FROM ENROLLMENT E
    JOIN COURSE C ON E.CourseID = C.CourseID
    JOIN CLASS_SESSION CS ON CS.EnrollmentID = E.EnrollmentID
    WHERE E.StudentID = ?
");
$stmt->execute([$student_id]);
$sessions = $stmt->fetchAll();

// Build HTML content
$html = "<h2>Class Routine - {$_SESSION['name']}</h2>";
$html .= "<table border='1' cellspacing='0' cellpadding='8'>";
$html .= "<tr><th>Course</th><th>Day</th><th>Start</th><th>End</th><th>Room</th></tr>";

foreach ($sessions as $s) {
    $html .= "<tr>
        <td>{$s['CourseTitle']} ({$s['CourseCode']})</td>
        <td>{$s['DayOfWeek']}</td>
        <td>{$s['StartTime']}</td>
        <td>{$s['EndTime']}</td>
        <td>{$s['Room']}</td>
    </tr>";
}

$html .= "</table>";

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Routine.pdf", array("Attachment" => true)); // force download
