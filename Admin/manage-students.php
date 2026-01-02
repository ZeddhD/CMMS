<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require '../backend/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - CMMS Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <a href="../index.html" class="logo">CMMS <span style="font-size:0.8rem; opacity:0.7; margin-left:5px;">ADMIN</span></a>
            <nav class="header-nav">
                <a href="../admin-dashboard.php">Dashboard</a>
                <a href="manage-universities.php">Universities</a>
                <a href="manage-students.php" class="active">Students</a>
                <a href="../backend/logout.php" style="color:var(--error);">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h1>👥 Manage Students</h1>
                <p style="color:var(--text-muted);">View and manage all registered students</p>
            </div>
        </div>

        <?php if (isset($_GET['success']) && $_GET['success'] == 'deleted'): ?>
            <div class="alert alert-success" style="margin-bottom:1.5rem; padding:1rem; background:rgba(16, 185, 129, 0.1); border:1px solid var(--success); border-radius:8px; color:var(--success);">
                ✅ Student deleted successfully!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error" style="margin-bottom:1.5rem; padding:1rem; background:rgba(239, 68, 68, 0.1); border:1px solid var(--error); border-radius:8px; color:var(--error);">
                ❌ <?php 
                    if ($_GET['error'] == 'delete_failed') echo 'Failed to delete student. Please try again.';
                    elseif ($_GET['error'] == 'invalid_id') echo 'Invalid student ID.';
                    else echo 'An error occurred.';
                ?>
            </div>
        <?php endif; ?>

        <div class="content-section">
            <div class="table-responsive">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>University</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                         <?php
                        // Fetch Students
                        $stmt = $pdo->query("
                            SELECT s.StudentID, s.Name, s.Email, s.RegistrationDate, u.UniName 
                            FROM STUDENT s
                            LEFT JOIN UNIVERSITY u ON s.UniID = u.UniID
                            ORDER BY s.StudentID DESC
                        ");
                        $students = $stmt->fetchAll();

                        if (count($students) > 0) {
                            foreach ($students as $student) {
                                // Default avatar logic
                                $initials = strtoupper(substr($student['Name'] ?? 'U', 0, 2));
                                
                                echo "<tr>";
                                echo "<td>STD-".$student['StudentID']."</td>"; 
                                echo "<td>
                                        <div style='display:flex; align-items:center; gap:0.5rem;'>
                                            <div style='width:32px; height:32px; background:linear-gradient(135deg, var(--primary-cyan), var(--purple-accent)); border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:0.8rem; color:white;'>$initials</div>
                                            <strong>".htmlspecialchars($student['Name'])."</strong>
                                        </div>
                                      </td>";
                                echo "<td>".htmlspecialchars($student['Email'])."</td>";
                                echo "<td>".htmlspecialchars($student['UniName'] ?? 'Not Assigned')."</td>";
                                echo "<td>".htmlspecialchars($student['RegistrationDate'])."</td>";
                                echo "<td>
                                        <button onclick=\"deleteStudent(".$student['StudentID'].", '".htmlspecialchars($student['Name'], ENT_QUOTES)."')\" 
                                                class=\"btn\" style=\"background:var(--error); color:white; padding:0.5rem 1rem; font-size:0.85rem; border:none; border-radius:6px; cursor:pointer;\">
                                            🗑️ Delete
                                        </button>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding:2rem; color:var(--text-muted);'>No students registered.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function deleteStudent(studentId, studentName) {
            if (confirm(`⚠️ DELETE STUDENT: ${studentName}\n\nThis will permanently remove:\n• Student account\n• All enrollments\n• All assessments\n• All class sessions\n• All study materials\n\n❌ This action CANNOT be undone!\n\nAre you absolutely sure?`)) {
                // Send delete request
                window.location.href = `delete-student.php?id=${studentId}`;
            }
        }
    </script>
</body>
</html>