<?php
session_start();
require 'includes/db.php';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'student') {
        header("Location: student-dashboard.php");
    } else {
        header("Location: admin-dashboard.php");
    }
    exit();
}

$pageTitle = "Sign In";
include 'includes/header.php';
?>
    <style>
        main { display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 200px); }
    </style>

    <div class="login-wrapper">
        <div class="login-card" role="form" aria-labelledby="login-title">
            <div class="card-header">
                <h1 id="login-title">Welcome Back</h1>
                <p>Sign in to your CMMS account to continue</p>
            </div>

            <div id="alert-container"></div>

            <form method="POST" action="backend/login.php" novalidate autocomplete="on">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="field-wrapper">
                        <input id="email" type="email" name="email" inputmode="email" placeholder="you@example.com"
                            autocomplete="email" required />
                    </div>
                    <p class="field-error" data-error-for="email"></p>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="field-wrapper">
                        <input id="password" type="password" name="password" autocomplete="current-password"
                            placeholder="Enter your password" required />
                    </div>
                    <p class="field-error" data-error-for="password"></p>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; margin-top:1rem;">
                    Sign In
                </button>
            </form>

            <p style="text-align:center; margin-top:1.5rem; color:var(--text-muted);">
                Don't have an account? <a href="register.php" style="color:var(--primary-cyan); text-decoration:none;">Create one here</a>
            </p>
        </div>
    </div>

<footer>
    <p style="text-align:center; padding:2rem; color:var(--text-muted);">
        &copy; 2025 <a href="index.html" style="color:var(--primary-cyan); text-decoration:none;">CMMS - Course Material Management System</a>. All rights reserved.
    </p>
</footer>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error')) {
        const alertDiv = document.getElementById('alert-container');
        const alert = document.createElement('div');
        alert.className = 'alert alert-error';
        alert.style.marginBottom = '1rem';
        alert.textContent = '❌ Invalid email or password. Please try again.';
        alertDiv.appendChild(alert);
    }
</script>
</body>
</html>