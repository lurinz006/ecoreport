<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

if(isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    
    if(empty($username) || empty($email)) {
        $error = 'Please enter both username and email address.';
    } elseif(!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        $result = handlePasswordResetRequest($username, $email);
        if($result['success']) {
            $_SESSION['reset_email'] = $email;
            // For convenience in testing/demo, we'll store a flash message with the code
            flashMessage('success', 'Verification code has been sent! (Demo Code: ' . $result['code'] . ')');
            redirect('verify_reset.php');
        } else {
            $error = $result['message'];
        }
    }
}

$page_title = "Forgot Password - " . APP_NAME;
include 'includes/header.php';
?>

<div class="container-fluid p-0">
    <div class="row g-0 min-vh-100">
        <div class="col-lg-6 d-none d-lg-block">
            <div class="hero-gradient h-100 d-flex align-items-center justify-content-center p-5">
                <div class="text-center text-white fade-in">
                    <i class="bi bi-key-fill mb-4" style="font-size: 8rem;"></i>
                    <h1 class="display-4 fw-bold mb-4" style="font-family: 'Outfit', sans-serif;">Reset Password</h1>
                    <p class="lead text-white-50 fs-4 mb-0">Don't worry! It happens. Enter your credentials and we'll send you a recovery code.</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 d-flex align-items-center justify-content-center auth-form-container p-4">
            <div class="w-100 fade-in text-white" style="max-width: 450px;">
                <div class="mb-4">
                    <a href="login.php" class="text-white-50 text-decoration-none small mb-3 d-inline-block">
                        <i class="bi bi-arrow-left me-2"></i>Back to Login
                    </a>
                    <h2 class="fw-bold text-white fs-1" style="font-family: 'Outfit', sans-serif;">Forgot Password?</h2>
                    <p class="text-white-50">Enter your account details to receive a 6-digit code.</p>
                </div>
                
                <?php if($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 mb-4">
                        <i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="username" class="form-label fw-medium">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white bg-opacity-10 border-0 rounded-start-4">
                                <i class="bi bi-person text-white-50"></i>
                            </span>
                            <input type="text" class="form-control form-control-premium bg-white bg-opacity-5 border-0 rounded-end-4" id="username" name="username" 
                                   placeholder="Your username" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label fw-medium">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white bg-opacity-10 border-0 rounded-start-4">
                                <i class="bi bi-envelope text-white-50"></i>
                            </span>
                            <input type="email" class="form-control form-control-premium bg-white bg-opacity-5 border-0 rounded-end-4" id="email" name="email" 
                                   placeholder="name@email.com" required>
                        </div>
                        <div class="form-text text-white-50 small mt-2">
                            <i class="bi bi-info-circle me-1"></i> We'll verify both details before sending the code.
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-premium w-100 py-3 mb-4">
                        Send Reset Code <i class="bi bi-send ms-2"></i>
                    </button>
                    
                    <div class="text-center">
                        <p class="text-muted mb-0 small">Remembered your password? 
                            <a href="login.php" class="text-emerald fw-bold text-decoration-none" style="color: var(--primary-color)">Try logging in</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
