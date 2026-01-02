<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
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
                <p style="color:var(--text-muted);">View, edit, or remove student accounts.</p>
            </div>
            <!-- Potential Add Student Button -->
        </div>

        <div class="content-section">
            <div class="table-responsive">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>University</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tbody>
                         <?php
                        // Fetch Students
                        $stmt = $pdo->query("
                            SELECT s.StudentID, s.Name, s.Email, u.UniName 
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
                                echo "<td>STD-".$student['StudentID']."</td>"; // Simple ID formatting
                                echo "<td>
                                        <div style='display:flex; align-items:center; gap:0.5rem;'>
                                            <div style='width:32px; height:32px; background:#ddd; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:0.8rem; color:#555;'>$initials</div>
                                            <strong>".htmlspecialchars($student['Name'])."</strong>
                                        </div>
                                      </td>";
                                echo "<td>".htmlspecialchars($student['Email'])."</td>";
                                echo "<td>".htmlspecialchars($student['UniName'] ?? 'Not Assigned')."</td>";
                                echo "<td><span class='badge badge-success'>Active</span></td>"; // Schema doesn't have status, assume active
                                echo "<td>
                                        <button class='btn btn-sm btn-secondary'>Edit</button>
                                        <button class='btn btn-sm btn-danger'>Block</button>
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
</body>
</html>