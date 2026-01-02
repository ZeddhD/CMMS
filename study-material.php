<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$pageTitle = "Study Materials";
include 'includes/header.php';
require_once 'includes/db.php';
?>

<main>
    <div class="page-header">
        <div class="header-content">
            <h1>📚 Study Materials</h1>
            <p>Browse course documents uploaded by students.</p>
        </div>
    </div>

    <!-- Materials List -->
    <div class="content-section">
        <div class="section-title">Available Resources</div>
        
        <div class="table-responsive">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Course</th>
                        <th>Uploaded By</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch all materials from the database grouped by course
                    $sql = "
                        SELECT sm.*, c.CourseCode, c.CourseTitle, s.Name as StudentName 
                        FROM STUDY_MATERIAL sm
                        LEFT JOIN COURSE c ON sm.CourseID = c.CourseID
                        LEFT JOIN STUDENT s ON sm.StudentID = s.StudentID
                        ORDER BY c.CourseCode, sm.UploadDate DESC
                    ";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $materials = $stmt->fetchAll();

                    if (count($materials) > 0) {
                        foreach ($materials as $mat) {
                            $icon = $mat['MaterialType'] === 'Link' ? '🔗' : ($mat['MaterialType'] === 'Image' ? '🖼️' : '📄');
                            $date = date('M d, Y', strtotime($mat['UploadDate']));
                            $course = $mat['CourseCode'] ? "<span class='badge badge-info' style='background:rgba(var(--primary-cyan-rgb),0.1); color:var(--primary-cyan); padding:0.3rem 0.6rem; border-radius:4px;'>{$mat['CourseCode']}</span>" : "<span class='badge badge-warning' style='background:rgba(255,193,7,0.1); color:#ffc107; padding:0.3rem 0.6rem; border-radius:4px;'>General</span>";
                            $studentName = $mat['StudentName'] ? htmlspecialchars($mat['StudentName']) : "<span style='color:var(--text-muted);'>Unknown</span>";
                            
                            echo "<tr>";
                            echo "<td><span style='font-size:1.2rem;'>$icon</span></td>";
                            echo "<td>" . htmlspecialchars($mat['Title']) . "</td>";
                            echo "<td>$course</td>";
                            echo "<td>$studentName</td>";
                            echo "<td>$date</td>";
                            echo "<td><a href='" . htmlspecialchars($mat['URL_or_Path']) . "' class='btn btn-sm btn-primary' target='_blank' style='padding:0.4rem 0.8rem; font-size:0.85rem;'>Open Resource</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center p-4'>No materials available yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

