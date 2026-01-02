<?php
session_start();
require 'includes/db.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch all upcoming quiz/assignment assessments
$stmt = $pdo->prepare("
    SELECT a.*, c.CourseCode, c.CourseTitle, e.Semester
    FROM ASSESSMENT a
    JOIN ENROLLMENT e ON a.EnrollmentID = e.EnrollmentID
    JOIN COURSE c ON e.CourseID = c.CourseID
    WHERE e.StudentID = ?
      AND a.AssessmentType IN ('quiz', 'assignment')
      AND a.DueDate >= CURDATE()
      AND a.Status NOT IN ('completed', 'done')
    ORDER BY a.DueDate ASC, a.DueTime ASC
");
$stmt->execute([$student_id]);
$upcoming = $stmt->fetchAll();

$pageTitle = "Upcoming Quizzes & Assignments";
include 'includes/header.php';
?>

<style>
    .upcoming-header {
        background: linear-gradient(135deg, var(--primary-cyan), var(--purple-accent));
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        color: white;
        text-align: center;
    }
    .assessment-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: transform 0.2s;
    }
    .assessment-card:hover {
        transform: translateY(-2px);
        border-color: var(--primary-cyan);
    }
    .assessment-info h3 {
        margin: 0 0 0.5rem 0;
        color: var(--primary-cyan);
    }
    .assessment-meta {
        color: var(--text-muted);
        font-size: 0.9rem;
    }
    .assessment-type {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-right: 0.5rem;
    }
    .type-quiz {
        background: rgba(59, 130, 246, 0.2);
        color: #3b82f6;
    }
    .type-assignment {
        background: rgba(168, 85, 247, 0.2);
        color: #a855f7;
    }
</style>

<main>
    <div class="upcoming-header">
        <h1>📝 Upcoming Quizzes & Assignments</h1>
        <p style="opacity: 0.9;">Stay on top of your upcoming assessments</p>
    </div>

    <?php if (empty($upcoming)): ?>
        <div style="text-align: center; padding: 4rem 2rem;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">✅</div>
            <h2>All Caught Up!</h2>
            <p style="color: var(--text-muted);">You have no upcoming quizzes or assignments.</p>
            <a href="student-dashboard.php" class="btn btn-primary" style="margin-top: 1rem;">Back to Dashboard</a>
        </div>
    <?php else: ?>
        <div style="max-width: 900px; margin: 0 auto;">
            <p style="color: var(--text-muted); margin-bottom: 2rem;">
                You have <strong><?php echo count($upcoming); ?></strong> upcoming assessment<?php echo count($upcoming) > 1 ? 's' : ''; ?>
            </p>

            <?php foreach ($upcoming as $item): ?>
                <div class="assessment-card">
                    <div class="assessment-info">
                        <h3><?php echo htmlspecialchars($item['Title']); ?></h3>
                        <div class="assessment-meta">
                            <span class="assessment-type type-<?php echo htmlspecialchars($item['AssessmentType']); ?>">
                                <?php echo ucfirst(htmlspecialchars($item['AssessmentType'])); ?>
                            </span>
                            <strong><?php echo htmlspecialchars($item['CourseCode']); ?></strong> - 
                            <?php echo htmlspecialchars($item['CourseTitle']); ?>
                        </div>
                        <div class="assessment-meta" style="margin-top: 0.5rem;">
                            📅 Due: <strong><?php echo date('M j, Y', strtotime($item['DueDate'])); ?></strong> 
                            at <?php echo date('g:i A', strtotime($item['DueTime'])); ?>
                            | Max Marks: <strong><?php echo htmlspecialchars($item['MaxMarks']); ?></strong>
                            <?php if ($item['IsMandatory']): ?>
                                | <span style="color: var(--error);">⚠️ Mandatory</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <form method="POST" action="backend/mark-complete.php" style="margin: 0;">
                        <input type="hidden" name="assessment_id" value="<?php echo $item['AssessmentID']; ?>">
                        <input type="hidden" name="return_url" value="../upcoming-assessments.php">
                        <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                            ✓ Mark Complete
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="student-dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
