<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$pageTitle = "Study Materials";
include 'includes/header.php';
require_once 'includes/db.php';
?>

<main>
    <div class="page-header">
        <div class="header-content">
            <h1>📚 Study Materials</h1>
            <p>Access course documents or upload your own resources.</p>
        </div>
        <button class="btn btn-primary" onclick="toggleUploadForm()">
            <span>📤 Upload New</span>
        </button>
    </div>

    <!-- Upload Form (Hidden by default) -->
    <div id="upload-section" class="content-section" style="display: none;">
        <div class="section-title">Upload Material</div>
        <form action="backend/add-material.php" method="POST" enctype="multipart/form-data" class="upload-form">
            <div class="form-group">
                <label>Resource Type</label>
                <div class="type-selector">
                    <label class="radio-label">
                        <input type="radio" name="type" value="File" checked onclick="toggleType('file')">
                        <span>📄 File Document</span>
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="type" value="Link" onclick="toggleType('link')">
                        <span>🔗 External Link</span>
                    </label>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Title</label>
                    <div class="field-wrapper">
                        <input type="text" name="title" placeholder="e.g. Lecture 5 Notes" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Course Code (Optional)</label>
                    <div class="field-wrapper">
                        <input type="text" name="course_code" placeholder="e.g. CSE101">
                    </div>
                </div>
            </div>

            <!-- Dynamic Input Area -->
            <div id="file-input-group" class="form-group">
                <label>Select File</label>
                <div class="field-wrapper file-input-wrapper">
                    <input type="file" name="file_upload" id="file_upload">
                </div>
                <small style="color:var(--text-muted)">Supported: PDF, IMG, DOC, PPT</small>
            </div>

            <div id="link-input-group" class="form-group" style="display:none;">
                <label>Resource URL</label>
                <div class="field-wrapper">
                    <input type="url" name="link_url" placeholder="https://example.com/resource">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:auto; margin-top:1rem;">
                Submit Material
            </button>
        </form>
    </div>

    <!-- Materials List -->
    <div class="content-section">
        <div class="section-title">Available Resources</div>
        
        <!-- Filter/Search could go here -->

        <div class="table-responsive">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Course</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $userId = $_SESSION['user_id'];
                    // Fetch all materials uploaded by user OR for courses they are in (optional logic)
                    // For now let's show materials for courses the student is enrolled in + their own uploads?
                    // Or just their enrolled courses materials.
                    
                    // Logic: Get materials for courses I am enrolled in.
                    $sql = "
                        SELECT sm.*, c.CourseCode 
                        FROM study_material sm
                        LEFT JOIN course c ON sm.CourseID = c.CourseID
                        JOIN enrollment e ON c.CourseID = e.CourseID
                        WHERE e.StudentID = ?
                        UNION
                        SELECT sm.*, c.CourseCode
                        FROM study_material sm
                        LEFT JOIN course c ON sm.CourseID = c.CourseID
                        WHERE sm.UserID = ?
                        ORDER BY UploadedAt DESC
                    ";
                    
                    // Simplified: Just show everything for now or just enrollment based.
                    // The previous query was Enrollments only.
                    // Let's stick to Enrollments + Own uploads.
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$userId, $userId]);
                    $materials = $stmt->fetchAll();

                    if (count($materials) > 0) {
                        foreach ($materials as $mat) {
                            $icon = $mat['Type'] === 'Link' ? '🔗' : '📄';
                            $date = date('M d, Y', strtotime($mat['UploadedAt']));
                            $course = $mat['CourseCode'] ? "<span class='badge badge-info'>{$mat['CourseCode']}</span>" : "<span class='badge badge-warning'>General</span>";
                            $link = $mat['Type'] === 'Link' ? $mat['URL_or_Path'] : $mat['URL_or_Path']; 
                            
                            echo "<tr>";
                            echo "<td><span style='font-size:1.2rem;'>$icon</span></td>";
                            echo "<td>" . htmlspecialchars($mat['Title']) . "</td>";
                            echo "<td>$course</td>";
                            echo "<td>$date</td>";
                            echo "<td><a href='" . htmlspecialchars($link) . "' class='btn btn-sm btn-secondary' target='_blank'>Opener</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center p-4'>No materials found. Upload some!</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
function toggleUploadForm() {
    const form = document.getElementById('upload-section');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth' });
    } else {
        form.style.display = 'none';
    }
}

function toggleType(type) {
    const fileGroup = document.getElementById('file-input-group');
    const linkGroup = document.getElementById('link-input-group');
    const fileInput = document.getElementById('file_upload');
    const linkInput = document.querySelector('input[name="link_url"]');

    if (type === 'file') {
        fileGroup.style.display = 'block';
        linkGroup.style.display = 'none';
        fileInput.required = true;
        linkInput.required = false;
    } else {
        fileGroup.style.display = 'none';
        linkGroup.style.display = 'block';
        fileInput.required = false;
        linkInput.required = true;
    }
}
</script>

<style>
.type-selector {
    display: flex;
    gap: 1.5rem;
    margin-top: 0.5rem;
}

.radio-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-weight: 500;
}

.radio-label input {
    accent-color: var(--primary-cyan);
    width: 1.2rem;
    height: 1.2rem;
}
</style>

<?php include 'includes/footer.php'; ?>

