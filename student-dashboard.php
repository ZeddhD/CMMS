<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}
// Using the new shared DB include
require 'includes/db.php';

$student_name = $_SESSION['name'] ?? 'Student';
// Initials are now handled in header.php if needed, but we can keep logic here if specific to this page body
$pageTitle = "Student Dashboard";
?>
<?php include 'includes/header.php'; ?>

    <!-- Welcome Section -->
    <div class="welcome-section">
        <h1>Welcome back, <span><?php echo htmlspecialchars($student_name); ?></span> 👋</h1>
        <p>Track your assignments, manage your class schedule, and access study materials all in one place.</p>
    </div>

    <!-- Quick Stats Grid -->
    <?php
        // 1. Enrolled Courses Count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM ENROLLMENT WHERE StudentID = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $enrolledCount = $stmt->fetchColumn();

        // 2. Upcoming Classes Today
        // Need to map current day name to DB ENUM ('Sat', 'Sun', etc.)
        $todayAbbrev = date('D'); 
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM CLASS_SESSION cs
            JOIN ENROLLMENT e ON cs.EnrollmentID = e.EnrollmentID
            WHERE e.StudentID = ? AND cs.DayOfWeek = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $todayAbbrev]);
        $todayClassesCount = $stmt->fetchColumn();

        // 3. Pending Quizzes/Assignments (Due in future)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM ASSESSMENT a
            JOIN ENROLLMENT e ON a.EnrollmentID = e.EnrollmentID
            WHERE e.StudentID = ? AND a.DueDate >= CURDATE() AND a.Status != 'Completed'
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $pendingCount = $stmt->fetchColumn();
    ?>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Enrolled Courses</div>
            <div class="stat-value"><?php echo $enrolledCount; ?></div>
            <div style="color:var(--text-muted); font-size:0.85rem;">Active this semester</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Upcoming Classes</div>
            <div class="stat-value"><?php echo $todayClassesCount; ?></div>
            <div style="color:var(--text-muted); font-size:0.85rem;">Today</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending Assessments</div>
            <div class="stat-value"><?php echo $pendingCount; ?></div>
            <div style="color:var(--warning); font-size:0.85rem;">Due soon</div>
        </div>
    </div>

    <!-- Dashboard Actions -->
    <div class="content-section">
        <div class="section-title"><span>🚀</span> Quick Actions</div>
        <div style="display:flex; gap:1rem; flex-wrap:wrap;">
            <a href="enroll.php" class="btn btn-primary">Enroll in Course</a>
            <a href="class-session.php" class="btn btn-secondary">Add Class Session</a>
            <a href="assessment.php" class="btn btn-secondary">Add Assessment</a>
            <a href="study-material.php" class="btn btn-secondary">Upload Material</a>
        </div>
    </div>

    <!-- Recent Activity / Placeholder -->
    <div class="content-section">
        <div class="section-title"><span>📝</span> Recent Activity</div>
        <p style="color:var(--text-muted);">Your recent course updates and material uploads will appear here.</p>
    </div>

<?php include 'includes/footer.php'; ?>

