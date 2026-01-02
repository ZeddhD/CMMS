<?php
session_start();
require 'includes/db.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$enrollment_id = $_GET['enrollment_id'] ?? null;
$student_id = $_SESSION['student_id'];

if (!$enrollment_id) {
    header("Location: student-dashboard.php");
    exit();
}

// Verify this enrollment belongs to this student  
$stmt = $pdo->prepare("
    SELECT e.*, c.CourseCode, c.CourseTitle, c.CourseID
    FROM ENROLLMENT e
    JOIN COURSE c ON e.CourseID = c.CourseID
    WHERE e.EnrollmentID = ? AND e.StudentID = ?
");
$stmt->execute([$enrollment_id, $student_id]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    header("Location: student-dashboard.php?error=invalid_enrollment");
    exit();
}

// Fetch class sessions for this enrollment
$stmt = $pdo->prepare("SELECT * FROM CLASS_SESSION WHERE EnrollmentID = ? ORDER BY 
    CASE DayOfWeek 
        WHEN 'Sun' THEN 1 WHEN 'Mon' THEN 2 WHEN 'Tue' THEN 3 WHEN 'Wed' THEN 4 
        WHEN 'Thu' THEN 5 WHEN 'Fri' THEN 6 WHEN 'Sat' THEN 7 
    END, StartTime");
$stmt->execute([$enrollment_id]);
$sessions = $stmt->fetchAll();

// Fetch assessments for this enrollment
$stmt = $pdo->prepare("SELECT * FROM ASSESSMENT WHERE EnrollmentID = ? ORDER BY DueDate ASC, DueTime ASC");
$stmt->execute([$enrollment_id]);
$assessments = $stmt->fetchAll();

// Fetch study materials for this course
$stmt = $pdo->prepare("
    SELECT * FROM STUDY_MATERIAL 
    WHERE CourseID = ? AND StudentID = ?
    ORDER BY UploadDate DESC
");
$stmt->execute([$enrollment['CourseID'], $student_id]);
$materials = $stmt->fetchAll();

$pageTitle = $enrollment['CourseCode'] . " - Course Details";
include 'includes/header.php';
?>

<style>
    .course-header {
        background: linear-gradient(135deg, var(--primary-cyan), var(--purple-accent));
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        color: white;
    }
    .section-card {
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
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .item-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .item {
        background: rgba(var(--primary-cyan-rgb), 0.05);
        padding: 1rem;
        border-radius: 8px;
        border-left: 3px solid var(--primary-cyan);
    }
    .form-toggle {
        background: var(--primary-cyan);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        margin-bottom: 1rem;
    }
</style>

<main>
    <!-- Course Header -->
    <div class="course-header">
        <h1>📚 <?php echo htmlspecialchars($enrollment['CourseCode']); ?></h1>
        <h2><?php echo htmlspecialchars($enrollment['CourseTitle']); ?></h2>
        <p style="opacity: 0.9;">Semester: <?php echo htmlspecialchars($enrollment['Semester']); ?></p>
    </div>

    <!-- Class Sessions Section -->
    <div class="section-card">
        <div class="section-title">
            <span>📅</span> Class Sessions
        </div>
        
        <button class="form-toggle" onclick="document.getElementById('add-session-form').style.display = document.getElementById('add-session-form').style.display === 'none' ? 'block' : 'none'">
            + Add Session
        </button>

        <form id="add-session-form" action="backend/add-session.php" method="POST" style="display:none; margin-bottom:2rem; padding:1.5rem; background:rgba(255,255,255,0.03); border-radius:8px;">
            <input type="hidden" name="course_code" value="<?php echo htmlspecialchars($enrollment['CourseCode']); ?>">
            <input type="hidden" name="course_title" value="<?php echo htmlspecialchars($enrollment['CourseTitle']); ?>">
            <input type="hidden" name="enrollment_id" value="<?php echo $enrollment_id; ?>">
            
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div>
                    <label>Day of Week</label>
                    <select name="day" required style="width:100%; padding:0.5rem;">
                        <option value="">Select...</option>
                        <option value="Sun">Sunday</option>
                        <option value="Mon">Monday</option>
                        <option value="Tue">Tuesday</option>
                        <option value="Wed">Wednesday</option>
                        <option value="Thu">Thursday</option>
                        <option value="Fri">Friday</option>
                        <option value="Sat">Saturday</option>
                    </select>
                </div>
                <div>
                    <label>Start Time</label>
                    <input type="time" name="start_time" required style="width:100%; padding:0.5rem;">
                </div>
                <div>
                    <label>End Time</label>
                    <input type="time" name="end_time" required style="width:100%; padding:0.5rem;">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div>
                    <label>Room / Link</label>
                    <input type="text" name="room" required placeholder="Room 302 or Zoom Link" style="width:100%; padding:0.5rem;">
                </div>
                <div>
                    <label>Mode</label>
                    <select name="mode" required style="width:100%; padding:0.5rem;">
                        <option value="">Select...</option>
                        <option value="offline">Offline</option>
                        <option value="online">Online</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Save Session</button>
        </form>

        <div class="item-list">
            <?php if (empty($sessions)): ?>
                <p style="color:var(--text-muted); text-align:center; padding:2rem;">No class sessions added yet.</p>
            <?php else: ?>
                <?php foreach ($sessions as $session): ?>
                    <div class="item">
                        <strong><?php echo htmlspecialchars($session['DayOfWeek']); ?></strong> 
                        | <?php echo htmlspecialchars($session['StartTime']); ?> - <?php echo htmlspecialchars($session['EndTime']); ?>
                        | Room: <?php echo htmlspecialchars($session['Room']); ?>
                        | <span class="badge"><?php echo htmlspecialchars($session['Mode']); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Assessments Section -->
    <div class="section-card">
        <div class="section-title">
            <span>📝</span> Assessments
        </div>

        <button class="form-toggle" onclick="document.getElementById('add-assessment-form').style.display = document.getElementById('add-assessment-form').style.display === 'none' ? 'block' : 'none'">
            + Add Assessment
        </button>

        <form id="add-assessment-form" action="backend/add-assessment.php" method="POST" style="display:none; margin-bottom:2rem; padding:1.5rem; background:rgba(255,255,255,0.03); border-radius:8px;">
            <input type="hidden" name="course_code" value="<?php echo htmlspecialchars($enrollment['CourseCode']); ?>">
            <input type="hidden" name="course_title" value="<?php echo htmlspecialchars($enrollment['CourseTitle']); ?>">
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div>
                    <label>Type</label>
                    <select name="type" id="assessment-type" required style="width:100%; padding:0.5rem;" onchange="toggleDueTime()">
                        <option value="">Select...</option>
                        <option value="quiz">Quiz</option>
                        <option value="assignment">Assignment</option>
                        <option value="exam">Exam</option>
                    </select>
                </div>
                <div>
                    <label>Title</label>
                    <input type="text" name="title" required placeholder="Assessment name" style="width:100%; padding:0.5rem;">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div>
                    <label>Marks</label>
                    <input type="number" name="max_marks" required min="0" style="width:100%; padding:0.5rem;">
                </div>
                <div>
                    <label>Mandatory</label>
                    <select name="mandatory" required style="width:100%; padding:0.5rem;">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div>
                    <label>Due Date</label>
                    <input type="date" name="due_date" required style="width:100%; padding:0.5rem;">
                </div>
                <div id="due-time-field">
                    <label>Due Time</label>
                    <select name="due_time" id="due-time-input" style="width:100%; padding:0.5rem;">
                        <option value="">Select time...</option>
                        <option value="08:00:00">8:00 AM</option>
                        <option value="08:30:00">8:30 AM</option>
                        <option value="09:00:00">9:00 AM</option>
                        <option value="09:30:00">9:30 AM</option>
                        <option value="10:00:00">10:00 AM</option>
                        <option value="10:30:00">10:30 AM</option>
                        <option value="11:00:00">11:00 AM</option>
                        <option value="11:30:00">11:30 AM</option>
                        <option value="12:00:00">12:00 PM</option>
                        <option value="12:30:00">12:30 PM</option>
                        <option value="13:00:00">1:00 PM</option>
                        <option value="13:30:00">1:30 PM</option>
                        <option value="14:00:00">2:00 PM</option>
                        <option value="14:30:00">2:30 PM</option>
                        <option value="15:00:00">3:00 PM</option>
                        <option value="15:30:00">3:30 PM</option>
                        <option value="16:00:00">4:00 PM</option>
                        <option value="16:30:00">4:30 PM</option>
                        <option value="17:00:00">5:00 PM</option>
                        <option value="17:30:00">5:30 PM</option>
                        <option value="18:00:00">6:00 PM</option>
                        <option value="18:30:00">6:30 PM</option>
                        <option value="19:00:00">7:00 PM</option>
                        <option value="19:30:00">7:30 PM</option>
                        <option value="20:00:00">8:00 PM</option>
                        <option value="20:30:00">8:30 PM</option>
                        <option value="21:00:00">9:00 PM</option>
                        <option value="21:30:00">9:30 PM</option>
                        <option value="22:00:00">10:00 PM</option>
                        <option value="22:30:00">10:30 PM</option>
                        <option value="23:00:00">11:00 PM</option>
                        <option value="23:30:00">11:30 PM</option>
                    </select>
                </div>
            </div>
            <input type="hidden" name="status" value="pending">
            <button type="submit" class="btn btn-primary">Add Assessment</button>
        </form>

        <script>
        function toggleDueTime() {
            const type = document.getElementById('assessment-type').value;
            const dueTimeField = document.getElementById('due-time-field');
            const dueTimeInput = document.getElementById('due-time-input');
            
            if (type === 'quiz') {
                dueTimeField.style.display = 'none';
                dueTimeInput.required = false;
                dueTimeInput.value = '';
            } else {
                dueTimeField.style.display = 'block';
                dueTimeInput.required = true;
            }
        }
        
        // Initialize on page load
        toggleDueTime();
        </script>

        <div class="item-list">
            <?php if (empty($assessments)): ?>
                <p style="color:var(--text-muted); text-align:center; padding:2rem;">No assessments added yet.</p>
            <?php else: ?>
                <?php foreach ($assessments as $assessment): ?>
                    <div class="item">
                        <div style="display:flex; justify-content:space-between; align-items:start;">
                            <div>
                                <strong><?php echo htmlspecialchars($assessment['Title']); ?></strong>
                                <span class="badge"><?php echo htmlspecialchars($assessment['AssessmentType']); ?></span>
                                <br>
                                <small>Due: <?php echo htmlspecialchars($assessment['DueDate'] . ' ' . $assessment['DueTime']); ?></small>
                                | Max: <?php echo htmlspecialchars($assessment['MaxMarks']); ?> marks
                                <?php if ($assessment['MarksObtained'] !== null): ?>
                                    | Scored: <?php echo htmlspecialchars($assessment['MarksObtained']); ?>
                                <?php endif; ?>
                            </div>
                            <?php if ($assessment['Status'] !== 'completed' && $assessment['Status'] !== 'done'): ?>
                                <form method="POST" action="backend/mark-complete.php" style="display:inline;">
                                    <input type="hidden" name="assessment_id" value="<?php echo $assessment['AssessmentID']; ?>">
                                    <input type="hidden" name="return_url" value="../course-details.php?enrollment_id=<?php echo $enrollment_id; ?>">
                                    <button type="submit" class="btn" style="background:var(--success); color:white; padding:0.5rem 1rem; font-size:0.85rem;">✓ Mark Complete</button>
                                </form>
                            <?php else: ?>
                                <span class="badge badge-success">Completed</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Materials Section -->
    <div class="section-card">
        <div class="section-title">
            <span>📂</span> Study Materials
        </div>

        <button class="form-toggle" onclick="document.getElementById('add-material-form').style.display = document.getElementById('add-material-form').style.display === 'none' ? 'block' : 'none'">
            + Upload Material
        </button>

        <form id="add-material-form" action="backend/add-material.php" method="POST" enctype="multipart/form-data" style="display:none; margin-bottom:2rem; padding:1.5rem; background:rgba(255,255,255,0.03); border-radius:8px;">
            <input type="hidden" name="course_code" value="<?php echo htmlspecialchars($enrollment['CourseCode']); ?>">
            
            <div style="margin-bottom:1rem;">
                <label>Title</label>
                <input type="text" name="title" required placeholder="Material title" style="width:100%; padding:0.5rem;">
            </div>
            <div style="margin-bottom:1rem;">
                <label>Type</label>
                <select name="type" id="material-type" required style="width:100%; padding:0.5rem;" onchange="toggleMaterialInput()">
                    <option value="">Select...</option>
                    <option value="File">File Upload</option>
                    <option value="Link">Link/URL</option>
                </select>
            </div>
            <div id="file-upload-section" style="margin-bottom:1rem; display:none;">
                <label>Upload File (Any file type allowed)</label>
                <input type="file" name="file_upload" style="width:100%; padding:0.5rem;">
            </div>
            <div id="link-url-section" style="margin-bottom:1rem; display:none;">
                <label>URL</label>
                <input type="url" name="link_url" placeholder="https://example.com/resource" style="width:100%; padding:0.5rem;">
            </div>
            <input type="hidden" name="return_url" value="../course-details.php?enrollment_id=<?php echo $enrollment_id; ?>">
            <button type="submit" class="btn btn-primary">Upload Material</button>
        </form>
        
        <script>
        function toggleMaterialInput() {
            const type = document.getElementById('material-type').value;
            const fileSection = document.getElementById('file-upload-section');
            const linkSection = document.getElementById('link-url-section');
            const fileInput = document.querySelector('input[name="file_upload"]');
            const linkInput = document.querySelector('input[name="link_url"]');
            
            if (type === 'File') {
                fileSection.style.display = 'block';
                linkSection.style.display = 'none';
                if (fileInput) fileInput.required = true;
                if (linkInput) linkInput.required = false;
            } else if (type === 'Link') {
                fileSection.style.display = 'none';
                linkSection.style.display = 'block';
                if (fileInput) fileInput.required = false;
                if (linkInput) linkInput.required = true;
            } else {
                fileSection.style.display = 'none';
                linkSection.style.display = 'none';
                if (fileInput) fileInput.required = false;
                if (linkInput) linkInput.required = false;
            }
        }
        </script>

        <div class="item-list">
            <?php if (empty($materials)): ?>
                <p style="color:var(--text-muted); text-align:center; padding:2rem;">No materials uploaded yet.</p>
            <?php else: ?>
                <?php foreach ($materials as $material): ?>
                    <div class="item">
                        <strong><?php echo htmlspecialchars($material['Title']); ?></strong>
                        <span class="badge"><?php echo htmlspecialchars($material['MaterialType']); ?></span>
                        <br>
                        <small>Uploaded: <?php echo htmlspecialchars($material['UploadDate']); ?></small>
                        <?php if ($material['URL_or_Path']): ?>
                            | <a href="<?php echo htmlspecialchars($material['URL_or_Path']); ?>" target="_blank" style="color:var(--primary-cyan);">View/Download</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div style="text-align:center; margin-top:2rem;">
        <a href="student-dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
