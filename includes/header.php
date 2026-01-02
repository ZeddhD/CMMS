<?php
// Function to determine active link
function isActive($page) {
    return basename($_SERVER['PHP_SELF']) == $page ? 'active' : '';
}

// Get user name from standardized session variables
$header_name = $_SESSION['student_name'] ?? $_SESSION['admin_name'] ?? 'Guest';
$header_initials = strtoupper(substr($header_name, 0, 2));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - CMMS' : 'CMMS'; ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <style>
        /* Small override to ensure sticky header works if body height is small */
        body { min-height: 100vh; display: flex; flex-direction: column; }
        main { flex: 1; }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header>
        <div class="header-container">
            <a href="index.html" class="logo">CMMS</a>
            <div class="header-right">
                <nav class="header-nav">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="student-dashboard.php" class="<?php echo isActive('student-dashboard.php'); ?>">Dashboard</a>
                        <a href="study-material.php" class="<?php echo isActive('study-material.php'); ?>">Resources</a>
                        <a href="calendar.php" class="<?php echo isActive('calendar.php'); ?>">Calendar</a>
                        <a href="routine-download.php" class="<?php echo isActive('routine-download.php'); ?>">Routine</a>
                        <a href="backend/logout.php" style="color:var(--error);">Logout</a>
                    <?php else: ?>
                        <a href="index.html" class="<?php echo isActive('index.html'); ?>">Home</a>
                        <a href="login.php" class="<?php echo isActive('login.php'); ?>">Login</a>
                        <a href="register.php" class="<?php echo isActive('register.php'); ?>">Register</a>
                    <?php endif; ?>
                </nav>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                <div class="user-profile" style="display:flex; align-items:center; gap:1rem; padding-left:1.5rem; border-left:1px solid var(--border-color);">
                    <div class="user-avatar" style="width:40px; height:40px; background:linear-gradient(135deg, var(--primary-cyan), var(--purple-accent)); border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:bold;">
                        <?php echo $header_initials; ?>
                    </div>
                    <div class="user-info">
                        <span style="font-weight:600; display:block;"><?php echo htmlspecialchars($header_name); ?></span>
                        <a href="backend/logout.php" style="font-size:0.85rem; color:var(--text-muted); text-decoration:none;">Logout</a>
                    </div>
                </div>
                <?php else: ?>
                <div class="auth-buttons" style="display:flex; gap:1rem;">
                    <a href="login.php" class="nav-btn">Login</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <!-- MAIN CONTENT START -->
    <main>
