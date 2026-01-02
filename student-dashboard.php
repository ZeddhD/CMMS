<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'] ?? 'Student';

// Fetch enrolled courses
$stmt = $pdo->prepare("
    SELECT e.EnrollmentID, c.CourseCode, c.CourseTitle, e.Semester
    FROM ENROLLMENT e
    JOIN COURSE c ON e.CourseID = c.CourseID
    WHERE e.StudentID = ?
    ORDER BY e.EnrollmentID DESC
");
$stmt->execute([$student_id]);
$enrolled_courses = $stmt->fetchAll();

// Get recent activity (combine last 2-3 activities from different sources)
$recent_activities = [];

// Recent enrollments
$stmt = $pdo->prepare("
    SELECT 'enrollment' as type, e.EnrollmentID as id, c.CourseCode, c.CourseTitle, e.EnrollmentID as ref_id
    FROM ENROLLMENT e
    JOIN COURSE c ON e.CourseID = c.CourseID
    WHERE e.StudentID = ?
    ORDER BY e.EnrollmentID DESC LIMIT 2
");
$stmt->execute([$student_id]);
foreach ($stmt->fetchAll() as $row) {
    $recent_activities[] = array_merge($row, ['sort_id' => $row['id']]);
}

// Recent class sessions
$stmt = $pdo->prepare("
    SELECT 'session' as type, cs.ClassSessionID as id, c.CourseCode, c.CourseTitle, cs.DayOfWeek, e.EnrollmentID as ref_id
    FROM CLASS_SESSION cs
    JOIN ENROLLMENT e ON cs.EnrollmentID = e.EnrollmentID
    JOIN COURSE c ON e.CourseID = c.CourseID
    WHERE e.StudentID = ?
    ORDER BY cs.ClassSessionID DESC LIMIT 2
");
$stmt->execute([$student_id]);
foreach ($stmt->fetchAll() as $row) {
    $recent_activities[] = array_merge($row, ['sort_id' => $row['id']]);
}

// Recent assessments
$stmt = $pdo->prepare("
    SELECT 'assessment' as type, a.AssessmentID as id, c.CourseCode, c.CourseTitle, a.Title, a.AssessmentType, e.EnrollmentID as ref_id
    FROM ASSESSMENT a
    JOIN ENROLLMENT e ON a.EnrollmentID = e.EnrollmentID
    JOIN COURSE c ON e.CourseID = c.CourseID
    WHERE e.StudentID = ?
    ORDER BY a.AssessmentID DESC LIMIT 2
");
$stmt->execute([$student_id]);
foreach ($stmt->fetchAll() as $row) {
    $recent_activities[] = array_merge($row, ['sort_id' => $row['id']]);
}

// Recent materials
$stmt = $pdo->prepare("
    SELECT 'material' as type, sm.MaterialID as id, c.CourseCode, c.CourseTitle, sm.Title, sm.MaterialType
    FROM STUDY_MATERIAL sm
    JOIN COURSE c ON sm.CourseID = c.CourseID
    WHERE sm.StudentID = ?
    ORDER BY sm.MaterialID DESC LIMIT 2
");
$stmt->execute([$student_id]);
foreach ($stmt->fetchAll() as $row) {
    $recent_activities[] = array_merge($row, ['sort_id' => $row['id']]);
}

// Sort by ID desc and limit to 3
usort($recent_activities, function($a, $b) { return $b['sort_id'] - $a['sort_id']; });
$recent_activities = array_slice($recent_activities, 0, 3);

// Upcoming classes (today and tomorrow)
$today = date('D'); // 'Mon', 'Tue', etc.
$tomorrow = date('D', strtotime('+1 day'));

$stmt = $pdo->prepare("
    SELECT cs.*, c.CourseCode, c.CourseTitle
    FROM CLASS_SESSION cs
    JOIN ENROLLMENT e ON cs.EnrollmentID = e.EnrollmentID
    JOIN COURSE c ON e.CourseID = c.CourseID
    WHERE e.StudentID = ? AND cs.DayOfWeek IN (?, ?)
    ORDER BY FIELD(cs.DayOfWeek, ?, ?), cs.StartTime
");
$stmt->execute([$student_id, $today, $tomorrow, $today, $tomorrow]);
$upcoming_classes = $stmt->fetchAll();

// Count upcoming quizzes/assignments
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count
    FROM ASSESSMENT a
    JOIN ENROLLMENT e ON a.EnrollmentID = e.EnrollmentID
    WHERE e.StudentID = ?
      AND a.AssessmentType IN ('quiz', 'assignment')
      AND a.DueDate >= CURDATE()
      AND a.Status NOT IN ('completed', 'done')
");
$stmt->execute([$student_id]);
$upcoming_count = $stmt->fetchColumn();

$pageTitle = "Student Dashboard";
include 'includes/header.php';
?>

<style>
    .dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }
    .welcome-banner {
        background: linear-gradient(135deg, var(--primary-cyan), var(--purple-accent));
        padding: 2rem;
        border-radius: 12px;
        color: white;
        margin-bottom: 2rem;
    }
    .section {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    .section-title {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--primary-cyan);
    }
    .course-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
    }
    .course-card {
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.1), rgba(168, 85, 247, 0.1));
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1.5rem;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .course-card:hover {
        transform: translateY(-3px);
        border-color: var(--primary-cyan);
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
    }
    .activity-item {
        padding: 0.75rem;
        background: rgba(var(--primary-cyan-rgb), 0.05);
        border-radius: 6px;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    .upcoming-link {
        display: inline-block;
        background: var(--primary-cyan);
        color: white;
        padding: 1rem 2rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
    }
    .upcoming-link:hover {
        background: var(--purple-accent);
        transform: translateY(-2px);
    }
</style>

<main class="dashboard-container">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <h1>👋 Welcome back, <?php echo htmlspecialchars($student_name); ?>!</h1>
        <p style="opacity: 0.9;">Manage your courses, track assignments, and stay organized</p>
    </div>

    <!-- ACTION Section -->
    <div class="section">
        <div class="section-title">⚡ Action</div>
        <a href="enroll.php" class="btn btn-primary">
            ➕ Enroll in Course
        </a>
    </div>

    <!-- ENROLLED COURSES Section -->
    <div class="section">
        <div class="section-title">📚 Enrolled Courses</div>
        <?php if (empty($enrolled_courses)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 2rem;">
                No courses enrolled yet. Click "Enroll in Course" to get started!
            </p>
        <?php else: ?>
            <div class="course-grid">
                <?php foreach ($enrolled_courses as $course): ?>
                    <a href="course-details.php?enrollment_id=<?php echo $course['EnrollmentID']; ?>" class="course-card">
                        <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">📖</div>
                        <div style="font-weight: 700; font-size: 1.1rem; margin-bottom: 0.25rem;">
                            <?php echo htmlspecialchars($course['CourseCode']); ?>
                        </div>
                        <div style="color: var(--text-muted); font-size: 0.9rem;">
                            <?php echo htmlspecialchars($course['CourseTitle']); ?>
                        </div>
                        <div style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.5rem;">
                            <?php echo htmlspecialchars($course['Semester']); ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- RECENT ACTIVITY Section -->
    <div class="section">
        <div class="section-title">🕒 Recent Activity</div>
        <?php if (empty($recent_activities)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 1rem;">No recent activity</p>
        <?php else: ?>
            <?php foreach ($recent_activities as $activity): ?>
                <div class="activity-item">
                    <?php if ($activity['type'] === 'enrollment'): ?>
                        📝 Enrolled in <strong><?php echo htmlspecialchars($activity['CourseCode']); ?></strong>
                    <?php elseif ($activity['type'] === 'session'): ?>
                        📅 Added class session for <strong><?php echo htmlspecialchars($activity['CourseCode']); ?></strong> on <?php echo htmlspecialchars($activity['DayOfWeek']); ?>
                    <?php elseif ($activity['type'] === 'assessment'): ?>
                        ✏️ Added <?php echo htmlspecialchars($activity['AssessmentType']); ?>: <strong><?php echo htmlspecialchars($activity['Title']); ?></strong> in <?php echo htmlspecialchars($activity['CourseCode']); ?>
                    <?php elseif ($activity['type'] === 'material'): ?>
                        📂 Uploaded material: <strong><?php echo htmlspecialchars($activity['Title']); ?></strong> for <?php echo htmlspecialchars($activity['CourseCode']); ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- UPCOMING CLASSES Section -->
    <div class="section">
        <div class="section-title">📅 Upcoming Classes</div>
        <?php if (empty($upcoming_classes)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 1rem;">No classes today or tomorrow</p>
        <?php else: ?>
            <?php 
            $grouped = [];
            foreach ($upcoming_classes as $class) {
                $label = ($class['DayOfWeek'] === $today) ? 'Today' : 'Tomorrow';
                $grouped[$label][] = $class;
            }
            foreach ($grouped as $label => $classes): ?>
                <div style="margin-bottom: 1rem;">
                    <strong style="color: var(--primary-cyan);"><?php echo $label; ?>:</strong>
                    <?php foreach ($classes as $class): ?>
                        <div class="activity-item">
                            <strong><?php echo htmlspecialchars($class['CourseCode']); ?></strong> 
                            @ <?php echo date('g:i A', strtotime($class['StartTime'])); ?> 
                            - Room: <?php echo htmlspecialchars($class['Room']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- UPCOMING QUIZZES/ASSIGNMENTS Section -->
    <div class="section">
        <div class="section-title">📝 Upcoming Quizzes & Assignments</div>
        <div style="text-align: center; padding: 1rem;">
            <div style="font-size: 3rem; font-weight: 700; color: var(--primary-cyan); margin-bottom: 1rem;">
                <?php echo $upcoming_count; ?>
            </div>
            <p style="color: var(--text-muted); margin-bottom: 1rem;">
                Upcoming <?php echo $upcoming_count === 1 ? 'assessment' : 'assessments'; ?>
            </p>
            <?php if ($upcoming_count > 0): ?>
                <a href="upcoming-assessments.php" class="upcoming-link">
                    View All →
                </a>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
