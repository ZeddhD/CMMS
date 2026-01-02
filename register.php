<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'student') header("Location: student-dashboard.php");
    else header("Location: admin-dashboard.php");
    exit();
}
$pageTitle = "Create Account";
include 'includes/header.php';
?>
    <!-- Center the auth container naturally with flex or grid if needed, 
         but auth-container has specific grid styles. 
         We need to ensure it's vertically centered if possible, or just standard padding.
    -->
    <style>
         main { display: flex; align-items: center; justify-content: center; }
    </style>

    <div class="auth-container">
        <!-- LEFT SIDE - PROMO -->
        <div class="auth-promo">
            <div class="promo-content">
                <h1>Join <span class="gradient">Thousands of Students</span></h1>
                <p>Get started with your academic journey. Access courses, materials, assessments, and track your progress all in one place.</p>
                
                <ul class="promo-features">
                    <li><span><strong>📚 Browse Courses</strong> - Find and enroll in courses tailored to your needs</span></li>
                    <li><span><strong>📊 Track Progress</strong> - Monitor your academic performance with visual analytics</span></li>
                    <li><span><strong>✅ Complete Assessments</strong> - Take quizzes and submit assignments seamlessly</span></li>
                    <li><span><strong>📂 Access Materials</strong> - Download study resources anytime, anywhere</span></li>
                </ul>
            </div>
        </div>

        <!-- RIGHT SIDE - REGISTRATION FORM -->
        <div class="auth-form-wrapper">
            <!-- Header OUTSIDE the scrollable box -->
            <div class="form-header">
                <h2>Create Account</h2>
                <p>Join in just a few simple steps</p>
            </div>

            <!-- FORM CARD -->
            <div class="auth-card" role="form" aria-labelledby="register-title">
                <div id="alert-container"></div>

                <form method="POST" action="backend/register.php" novalidate autocomplete="on" id="registerForm">
                    <!-- Name Fields -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstname">First Name</label>
                            <div class="field-wrapper">
                                <input id="firstname" type="text" name="firstname" placeholder="John" required />
                            </div>
                            <p class="field-error" data-error-for="firstname"></p>
                        </div>

                        <div class="form-group">
                            <label for="lastname">Last Name</label>
                            <div class="field-wrapper">
                                <input id="lastname" type="text" name="lastname" placeholder="Doe" required />
                            </div>
                            <p class="field-error" data-error-for="lastname"></p>
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="field-wrapper">
                            <input id="email" type="email" name="email" inputmode="email" placeholder="you@example.com" autocomplete="email" required />
                        </div>
                        <p class="field-error" data-error-for="email"></p>
                    </div>

                    <!-- Role Selection -->
                    <div class="form-group">
                        <label for="role">I am a...</label>
                        <div class="field-wrapper">
                            <select id="role" name="role" required>
                                <option value="" disabled selected>Select role</option>
                                <option value="student">Student</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <p class="field-error" data-error-for="role"></p>
                    </div>

                    <!-- University Field -->
                    <div class="form-group" id="university-group" style="display:none;">
                        <label for="university">University</label>
                        <div class="field-wrapper">
                            <input id="university" type="text" name="university" placeholder="e.g. Harvard University" />
                        </div>
                        <p class="field-error" data-error-for="university"></p>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="field-wrapper">
                            <input id="password" type="password" name="password" autocomplete="new-password" placeholder="Create a strong password" required />
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar"><div class="strength-fill" id="strength-fill"></div></div>
                            <div class="strength-text" id="strength-text">Password strength: None</div>
                        </div>
                        <p class="field-error" data-error-for="password"></p>
                        <ul style="font-size:0.75rem; color:var(--text-muted); list-style:disc; margin-left:1rem; margin-top:0.5rem;" id="password-requirements">
                            <li id="req-length">At least 8 characters</li>
                            <li id="req-upper">One uppercase letter</li>
                            <li id="req-lower">One lowercase letter</li>
                            <li id="req-number">One number</li>
                            <li id="req-special">One special character</li>
                        </ul>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="field-wrapper">
                            <input id="confirm_password" type="password" name="confirm_password" autocomplete="new-password" placeholder="Repeat your password" required />
                        </div>
                        <p class="field-error" data-error-for="confirm_password"></p>
                    </div>

                    <!-- Terms -->
                    <div class="checkbox-group">
                        <input id="terms" type="checkbox" name="terms" required />
                        <label for="terms" class="checkbox-label">
                            I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                        </label>
                    </div>

                    <!-- Button -->
                    <button class="btn btn-primary" type="submit">
                        <span class="btn-label">Create Account</span>
                        <span class="btn-spinner" aria-hidden="true"></span>
                    </button>
                </form>

                <!-- Footer Link -->
                <div class="card-footer">
                    Already have an account? <a href="login.php">Sign in</a>
                </div>
            </div>
        </div>
    </div>

<script>
    // Password Validation Logic
    const passwordInput = document.getElementById('password');
    const requirements = {
        length: /.{8,}/,
        upper: /[A-Z]/,
        lower: /[a-z]/,
        number: /[0-9]/,
        special: /[^A-Za-z0-9]/
    };

    passwordInput.addEventListener('input', function() {
        const val = this.value;
        let score = 0;
        
        // Check each requirement
        for (const [key, regex] of Object.entries(requirements)) {
            const el = document.getElementById(`req-${key}`);
            if (regex.test(val)) {
                el.style.color = 'var(--success)';
                score++;
            } else {
                el.style.color = 'var(--text-muted)';
            }
        }

        // Strength bar
        const fill = document.getElementById('strength-fill');
        const text = document.getElementById('strength-text');
        const percentage = (score / 5) * 100;
        fill.style.width = `${percentage}%`;
        
        if (score <= 2) {
            fill.style.backgroundColor = 'var(--error)';
            text.textContent = 'Weak';
        } else if (score <= 4) {
            fill.style.backgroundColor = 'var(--warning)';
            text.textContent = 'Good';
        } else {
            fill.style.backgroundColor = 'var(--success)';
            text.textContent = 'Strong';
        }
    });

    // Role Validation
    const roleSelect = document.getElementById('role');
    const universityGroup = document.getElementById('university-group');
    
    roleSelect.addEventListener('change', function() {
        if (this.value === 'student') {
            universityGroup.style.display = 'block';
            document.getElementById('university').required = true;
        } else {
            universityGroup.style.display = 'none';
            document.getElementById('university').required = false;
        }
    });

    // Form Submission Validation
    const form = document.getElementById('registerForm');
    form.addEventListener('submit', function(e) {
        let valid = true;
        const password = passwordInput.value;
        const confirm = document.getElementById('confirm_password').value;

        // Check password match
        if (password !== confirm) {
            e.preventDefault();
            alert('Passwords do not match!');
            valid = false;
            return;
        }

        // Check strength
        let score = 0;
        for (const regex of Object.values(requirements)) {
            if (regex.test(password)) score++;
        }
        if (score < 5) {
            e.preventDefault();
            alert('Password does not meet all requirements.');
            valid = false;
            return;
        }
        
    });
</script>

<?php include 'includes/footer.php'; ?>