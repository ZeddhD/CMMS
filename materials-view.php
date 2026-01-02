<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Materials - CMMS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <a href="index.html" class="logo">CMMS</a>
            <nav class="header-nav">
                <a href="student-dashboard.php">Dashboard</a>
                <a href="study-material.php">Resources</a>
                <a href="backend/logout.php" style="color:var(--error);">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="page-header">
            <h1>📂 Course Materials</h1>
            <p style="color:var(--text-muted);">Browse all uploaded documents for your courses.</p>
        </div>

        <!-- Filter / Search Mockup -->
        <div class="content-section" style="padding:1.5rem;">
            <div style="display:flex; gap:1rem; max-width:600px;">
                <div class="field-wrapper" style="flex:1;">
                    <input type="text" placeholder="Search by title or course...">
                </div>
                <button class="btn btn-primary">Search</button>
            </div>
        </div>

        <div class="content-section">
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
                        require_once 'backend/db.php';
                        // Need to know WHOSE materials to show. 
                        // Usually students see materials for courses they are enrolled in.
                        // Or all materials uploaded by them?
                        // Context: "Browse all uploaded documents for your courses."
                        // So: SELECT * FROM STUDY_MATERIAL sm JOIN ENROLLMENT e ON sm.CourseID = e.CourseID WHERE e.StudentID = ?
                        $student_id = $_SESSION['user_id'];
                        
                        $sql = "
                            SELECT sm.Title, sm.MaterialType, sm.UploadDate, sm.URL_or_Path, c.CourseCode, u.Name as UploaderName
                            FROM STUDY_MATERIAL sm
                            JOIN COURSE c ON sm.CourseID = c.CourseID
                            JOIN ENROLLMENT e ON c.CourseID = e.CourseID
                            LEFT JOIN STUDENT u ON sm.StudentID = u.StudentID 
                            WHERE e.StudentID = ?
                            ORDER BY sm.UploadDate DESC
                        ";
                        // Note: Uploader might be admin/teacher, but schema only links StudentID. 
                        // If StudyMaterial.StudentID is the uploader, use it. If null, maybe admin?
                        
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$student_id]);
                        $materials = $stmt->fetchAll();

                        if (count($materials) > 0) {
                            foreach ($materials as $mat) {
                                $icon = '📄';
                                if (stripos($mat['MaterialType'], 'pdf') !== false) $icon = '📕';
                                elseif (stripos($mat['MaterialType'], 'video') !== false) $icon = '🎥';
                                elseif (stripos($mat['MaterialType'], 'image') !== false) $icon = '🖼️';

                                $date = date('M d, Y', strtotime($mat['UploadDate']));
                                $uploader = htmlspecialchars($mat['UploaderName'] ?? 'Unknown');
                                
                                echo "<tr>";
                                echo "<td><span style='font-size:1.5rem;'>$icon</span></td>";
                                echo "<td><strong>".htmlspecialchars($mat['Title'])."</strong></td>";
                                echo "<td><span class='badge badge-info'>".htmlspecialchars($mat['CourseCode'])."</span></td>";
                                echo "<td>$uploader</td>";
                                echo "<td>$date</td>";
                                echo "<td><a href='".htmlspecialchars($mat['URL_or_Path'])."' class='btn btn-sm btn-secondary' target='_blank'>Download</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding:2rem; color:var(--text-muted);'>No materials found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
