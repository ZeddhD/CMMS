<?php
session_start();
// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'student') header("Location: student-dashboard.php");
    else header("Location: admin-dashboard.php");
    exit();
}
$pageTitle = "Sign In";
include 'includes/header.php'; 
?>
    <!-- Center the login wrapper using flex utils or specific styles if needed. 
         header.php's main has standard padding. 
         We might want to center vertically. The shared css puts 'main' as block. 
         But we can style the wrapper. 
    -->
    <style>
        main { display: flex; align-items: center; justify-content: center; }
    </style>

    <div class="login-wrapper">
        <!-- LOGIN CARD -->
        <div class="login-card" role="form" aria-labelledby="login-title">
            <!-- Card Header -->
            <div class="card-header">
                <h1 id="login-title">Welcome Back</h1>
                <p>Sign in to your CMMS account to continue</p>
            </div>

            <!-- Alert Container -->
            <div id="alert-container"></div>

            <!-- Login Form -->
            <form method="POST" action="backend/login.php" novalidate autocomplete="on">
                <!-- Email Field -->
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="field-wrapper">
                        <input id="email" type="email" name="email" inputmode="email" placeholder="you@example.com"
                            autocomplete="email" required />
                    </div>
                    <p class="field-error" data-error-for="email"></p>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="field-wrapper">
                        <input id="password" type="password" name="password" autocomplete="current-password"
                            placeholder="Enter your password" required />
                    </div>
                    <p class="field-error" data-error-for="password"></p>
                </div>

                <!-- Remember & Forgot -->
                <div class="form-options">
                    <div class="remember-group">
                        <input id="remember" type="checkbox" name="remember" />
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>

                <!-- Sign In Button -->
                <button class="btn btn-primary" type="submit">
                    <span class="btn-label">Sign In</span>
                    <span class="btn-spinner" aria-hidden="true"></span>
                </button>
            </form>

            <!-- Divider -->
            <div class="divider">
                <span>OR</span>
            </div>

            <!-- Social Login -->
            <div class="social-login">
                <button class="social-btn" type="button" onclick="handleSocialLogin('google')">
                    🔵 Google
                </button>
                <button class="social-btn" type="button" onclick="handleSocialLogin('microsoft')">
                    💻 Microsoft
                </button>
            </div>

            <!-- Footer Link -->
            <div class="card-footer">
                Don't have an account? <a href="register.php">Create one here</a>
            </div>
        </div>
    </div>

<script>
    // Check for error in URL
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    if (error === 'invalid') {
        const alertContainer = document.getElementById('alert-container');
        const alert = document.createElement('div');
        alert.className = 'alert alert-error show';
        alert.textContent = 'Invalid email or password.';
        alertContainer.innerHTML = '';
        alertContainer.appendChild(alert);
    }
</script>

<?php include 'includes/footer.php'; ?>

</body>

</html>