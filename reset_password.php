<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

if(isLoggedIn()) {
    redirect('index.php');
}

if(!isset($_SESSION['reset_authenticated']) || !isset($_SESSION['reset_email'])) {
    redirect('forgot_password.php');
}

$error = '';
$email = $_SESSION['reset_email'];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if(empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif(strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        if(resetUserPassword($email, $password)) {
            // Success! Clean up sessions
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_authenticated']);
            unset($_SESSION['demo_reset_code']);
            
            flashMessage('success', 'Your password has been reset successfully. Please login with your new password.');
            redirect('login.php');
        } else {
            $error = 'Failed to reset password. Please try again later.';
        }
    }
}

$page_title = "Reset Password - " . APP_NAME;
include 'includes/header.php';
?>

<div class="container-fluid p-0">
    <div class="row g-0 min-vh-100">
        <div class="col-lg-6 d-none d-lg-block">
            <div class="hero-gradient h-100 d-flex align-items-center justify-content-center p-5">
                <div class="text-center text-white fade-in">
                    <i class="bi bi-shield-lock-fill mb-4" style="font-size: 8rem;"></i>
                    <h1 class="display-4 fw-bold mb-4" style="font-family: 'Outfit', sans-serif;">Secure Your Account</h1>
                    <p class="lead text-white-50 fs-4 mb-0">You're almost there! Create a strong, new password that you haven't used before.</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 d-flex align-items-center justify-content-center auth-form-container p-4">
            <div class="w-100 fade-in text-white" style="max-width: 450px;">
                <div class="mb-4">
                    <h2 class="fw-bold text-white fs-1" style="font-family: 'Outfit', sans-serif;">New Password</h2>
                    <p class="text-white-50">Please enter your new security credentials below.</p>
                </div>
                
                <?php if($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 mb-4">
                        <i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="password" class="form-label fw-medium">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white bg-opacity-10 border-0 rounded-start-4">
                                <i class="bi bi-lock text-white-50"></i>
                            </span>
                            <input type="password" class="form-control form-control-premium bg-white bg-opacity-5 border-0 rounded-end-0" id="password" name="password" 
                                   placeholder="Min. 8 characters" required>
                            <button class="btn btn-outline-light border-0 rounded-end-4 px-3 bg-white bg-opacity-10 toggle-password" type="button" data-target="password">
                                <i class="bi bi-eye text-white-50"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-medium">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white bg-opacity-10 border-0 rounded-start-4">
                                <i class="bi bi-lock-check text-white-50"></i>
                            </span>
                            <input type="password" class="form-control form-control-premium bg-white bg-opacity-5 border-0 rounded-end-0" id="confirm_password" name="confirm_password" 
                                   placeholder="Repeat new password" required>
                            <button class="btn btn-outline-light border-0 rounded-end-4 px-3 bg-white bg-opacity-10 toggle-password" type="button" data-target="confirm_password">
                                <i class="bi bi-eye text-white-50"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-premium w-100 py-3 mb-4">
                        Save New Password <i class="bi bi-shield-check ms-2"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const passwordInput = document.getElementById(targetId);
        const icon = this.querySelector('i');
        
        if(passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
