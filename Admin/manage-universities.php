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
    <title>Manage Universities - CMMS Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <a href="../index.html" class="logo">CMMS <span style="font-size:0.8rem; opacity:0.7; margin-left:5px;">ADMIN</span></a>
            <nav class="header-nav">
                <a href="../admin-dashboard.php">Dashboard</a>
                <a href="manage-universities.php" class="active">Universities</a>
                <a href="manage-students.php">Students</a>
                <a href="../backend/logout.php" style="color:var(--error);">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h1>🏛️ Manage Universities</h1>
                <p style="color:var(--text-muted);">View and manage registered institutions.</p>
            </div>
            <a href="add-university.php" class="btn btn-primary">Add University</a>
        </div>

        <div class="content-section">
            <div class="table-responsive">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>University Name</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tbody>
                        <?php
                        // Fetch Universities
                        $stmt = $pdo->query("SELECT UniID, UniName, Location FROM UNIVERSITY ORDER BY UniName ASC");
                        $universities = $stmt->fetchAll();

                        if (count($universities) > 0) {
                            foreach ($universities as $uni) {
                                echo "<tr>";
                                echo "<td>".htmlspecialchars($uni['UniID'])."</td>";
                                echo "<td><strong>".htmlspecialchars($uni['UniName'])."</strong></td>";
                                echo "<td>".htmlspecialchars($uni['Location'])."</td>";
                                echo "<td>
                                        <button class='btn btn-sm btn-secondary'>Edit</button>
                                        <button class='btn btn-sm btn-danger'>Delete</button>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align:center; padding:2rem; color:var(--text-muted);'>No universities found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>