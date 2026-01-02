<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Manual enrollment only - no pre-fetching courses
$pageTitle = "Enroll in Course";
include 'includes/header.php';
?>

$pageTitle = "Enroll in Course";
include 'includes/header.php';
?>

<style>
    .enroll-header {
        background: linear-gradient(135deg, var(--primary-cyan), var(--purple-accent));
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        color: white;
        text-align: center;
    }
    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    .course-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        transition: all 0.2s;
    }
    .course-card:hover {
        transform: translateY(-3px);
        border-color: var(--primary-cyan);
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
    }
    .course-code {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--primary-cyan);
        margin-bottom: 0.5rem;
    }
    .course-title {
        font-size: 1rem;
        margin-bottom: 1rem;
        color: var(--text-primary);
    }
    .course-credits {
        font-size: 0.9rem;
        color: var(--text-muted);
        margin-bottom: 1rem;
    }
</style>

<main style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
    <div class="enroll-header">
        <h1>📚 Enroll in a Course</h1>
        <p style="opacity: 0.9;">Enter the course details below to enroll. If the course doesn't exist, it will be automatically created.</p>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success" style="margin-bottom: 2rem;">
            ✅ Successfully enrolled! The course now appears in your dashboard.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error" style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--error); color: var(--error); padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
            <?php 
            if ($_GET['error'] == 'missing_fields') echo "⚠️ Please fill in all required fields.";
            elseif ($_GET['error'] == 'already_enrolled') echo "⚠️ You are already enrolled in this course.";
            else echo "⚠️ An error occurred.";
            ?>
        </div>
    <?php endif; ?>

    <div style="margin-top: 2rem;">
        <form action="backend/enroll.php" method="POST" class="section-card" style="max-width: 800px; margin: 0 auto; padding: 2rem; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Course Code</label>
                    <input type="text" name="course_code" placeholder="e.g. PHY101" required 
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; background: rgba(255,255,255,0.05); color: var(--text-primary);">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Course Title</label>
                    <input type="text" name="course_title" placeholder="e.g. Physics I" required 
                           style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; background: rgba(255,255,255,0.05); color: var(--text-primary);">
                </div>
            </div>

            <div style="margin-bottom: 1.5rem; padding: 1.5rem; background: rgba(var(--primary-cyan-rgb), 0.05); border-radius: 8px; border: 1px solid rgba(var(--primary-cyan-rgb), 0.1);">
                <div style="font-weight: 600; margin-bottom: 1rem; color: var(--primary-cyan); display: flex; align-items: center; gap: 0.5rem;">
                    <span>📊</span> Assessment Plan Configuration
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">Total Quizzes</label>
                        <input type="number" name="total_quiz" value="4" min="0" required 
                               style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-primary);">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">Total Assignments</label>
                        <input type="number" name="total_assignment" value="3" min="0" required 
                               style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-primary);">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">Best Of (Quiz)</label>
                        <input type="number" name="best_of_quiz" value="3" min="0" required 
                               style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-primary);">
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 2rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Semester</label>
                <input type="text" name="semester" value="Fall 2025" required 
                       style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; background: rgba(255,255,255,0.05); color: var(--text-primary);">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem; font-weight: 600;">
                ➕ Create & Enroll Course
            </button>
        </form>
    </div>

    <div style="text-align: center; margin-top: 3rem;">
        <a href="student-dashboard.php" class="btn btn-secondary" style="background: transparent; border: 1px solid var(--border-color);">← Back to Dashboard</a>
    </div>
</main>

<?php include 'includes/footer.php'; ?>