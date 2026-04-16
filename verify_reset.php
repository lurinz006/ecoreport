<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

if(isLoggedIn()) {
    redirect('index.php');
}

if(!isset($_SESSION['reset_email'])) {
    redirect('forgot_password.php');
}

$error = '';
$email = $_SESSION['reset_email'];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = sanitizeInput($_POST['code']);
    
    if(empty($code)) {
        $error = 'Please enter the verification code.';
    } elseif(verifyResetCode($email, $code)) {
        $_SESSION['reset_authenticated'] = true;
        flashMessage('success', 'Email verified. Please set your new password.');
        redirect('reset_password.php');
    } else {
        $error = 'Invalid or expired verification code. Please try again.';
    }
}

$page_title = "Verify Reset - " . APP_NAME;
include 'includes/header.php';
?>

<div class="container-fluid p-0">
    <div class="row g-0 min-vh-100">
        <div class="col-lg-6 d-none d-lg-block">
            <div class="hero-gradient h-100 d-flex align-items-center justify-content-center p-5">
                <div class="text-center text-white fade-in">
                    <i class="bi bi-shield-check mb-4" style="font-size: 8rem;"></i>
                    <h1 class="display-4 fw-bold mb-4" style="font-family: 'Outfit', sans-serif;">Verify Identity</h1>
                    <p class="lead text-white-50 fs-4 mb-0">We sent a 6-digit verification code to <strong><?php echo htmlspecialchars($email); ?></strong>. Enter it below to proceed.</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 d-flex align-items-center justify-content-center auth-form-container p-4">
            <div class="w-100 fade-in text-white" style="max-width: 450px;">
                <div class="mb-4">
                    <a href="forgot_password.php" class="text-white-50 text-decoration-none small mb-3 d-inline-block">
                        <i class="bi bi-arrow-left me-2"></i>Change Email
                    </a>
                    <h2 class="fw-bold text-white fs-1" style="font-family: 'Outfit', sans-serif;">Enter Code</h2>
                    <p class="text-white-50">Please type the 6-digit security code sent to you.</p>
                </div>
                
                <?php if($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 mb-4">
                        <i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="code" class="form-label fw-medium">Verification Code</label>
                        <input type="text" class="form-control form-control-premium bg-white bg-opacity-5 border-0 rounded-4 text-center fs-2 fw-bold tracking-widest py-3" 
                               id="code" name="code" placeholder="000 000" maxlength="6" pattern="\d{6}" required autocomplete="one-time-code">
                    </div>
                    
                    <button type="submit" class="btn btn-premium w-100 py-3 mb-4">
                        Verify Code <i class="bi bi-check2-circle ms-2"></i>
                    </button>
                    
                    <div class="text-center">
                        <p class="text-muted mb-0 small">Didn't receive the code? 
                            <a href="forgot_password.php" class="text-emerald fw-bold text-decoration-none" style="color: var(--primary-color)">Resend Code</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.tracking-widest {
    letter-spacing: 0.5rem;
}
</style>

<?php include 'includes/footer.php'; ?>
