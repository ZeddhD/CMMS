<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}
require 'backend/db.php';
$admin_name = $_SESSION['name'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CMMS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <!-- HEADER -->
    <header>
        <div class="header-container">
            <a href="index.html" class="logo">CMMS <span style="font-size:0.8rem; opacity:0.7; margin-left:5px;">ADMIN</span></a>
            <div class="header-right">
                <nav class="header-nav">
                    <a href="admin-dashboard.php" class="active">Dashboard</a>
                    <a href="Admin/manage-universities.php">Universities</a>
                    <a href="Admin/manage-students.php">Students</a>
                </nav>
                <div class="user-profile" style="display:flex; align-items:center; gap:1rem; padding-left:1.5rem; border-left:1px solid var(--border-color);">
                    <div class="user-avatar" style="width:40px; height:40px; background:linear-gradient(135deg, var(--error), var(--warning)); border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:bold;">
                        AD
                    </div>
                    <div class="user-info">
                        <span style="font-weight:600; display:block;"><?php echo htmlspecialchars($admin_name); ?></span>
                        <a href="backend/logout.php" style="font-size:0.85rem; color:var(--text-muted); text-decoration:none;">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main>
        <!-- Welcome Section -->
        <div class="welcome-section" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(245, 158, 11, 0.15)); border-color: rgba(239, 68, 68, 0.4);">
            <h1>Admin Panel</h1>
            <p>Manage system data, universities, and student accounts.</p>
        </div>

        <!-- Quick Stats Grid -->
        <?php
            // 1. Total Universities
            $stmt = $pdo->query("SELECT COUNT(*) FROM UNIVERSITY");
            $uniCount = $stmt->fetchColumn();

            // 2. Total Students
            $stmt = $pdo->query("SELECT COUNT(*) FROM STUDENT");
            $studentCount = $stmt->fetchColumn();
        ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Universities</div>
                <div class="stat-value"><?php echo $uniCount; ?></div>
                <div style="color:var(--text-muted); font-size:0.85rem;">In Database</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Registered Students</div>
                <div class="stat-value"><?php echo $studentCount; ?></div>
                <div style="color:var(--success); font-size:0.85rem;">Active users</div>
            </div>
        </div>

        <!-- Admin Actions -->
        <div class="content-section">
            <div class="section-title"><span>⚙️</span> Management Tools</div>
            <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                <!-- Fixed link to point to HTML form instead of PHP handler -->
                <a href="Admin/add-university.php" class="btn btn-primary">Add New University</a>
                <a href="Admin/manage-universities.php" class="btn btn-secondary">Manage Universities</a>
                <a href="Admin/manage-students.php" class="btn btn-secondary">Manage Students</a>
            </div>
        </div>
    </main>
</body>
</html>
