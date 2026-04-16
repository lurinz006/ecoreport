<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

if(isLoggedIn()) {
    if(isOfficial()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('user/dashboard.php');
    }
}

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    if(empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        $user = new User($db);

        $result = $user->login($username, $password);
        
        if($result) {
            $_SESSION['user_id'] = $result['id'];
            $_SESSION['username'] = $result['username'];
            $_SESSION['email'] = $result['email'];
            $_SESSION['full_name'] = $result['full_name'];
            $_SESSION['role'] = $result['role'];
            $_SESSION['contact_number'] = $result['contact_number'];
            $_SESSION['address'] = $result['address'];
            $_SESSION['profile_image'] = $result['profile_image'];

            flashMessage('success', 'Welcome back, ' . $result['full_name'] . '!');

            if($result['role'] === 'official') {
                redirect('admin/dashboard.php');
            } else {
                redirect('user/dashboard.php');
            }
        } else {
            $error = 'Invalid username or password';
        }
    }
}

$page_title = "Login - " . APP_NAME;
include 'includes/header.php';
?>

<div class="container-fluid p-0">
    <div class="row g-0 min-vh-100">
        <!-- Left Side: Image/Branding -->
        <div class="col-lg-6 d-none d-lg-block">
            <div class="h-100 d-flex align-items-center justify-content-center p-5 position-relative" style="background-image: url('images/community_cleaning.png'); background-size: cover; background-position: center; border-radius: 0 40px 40px 0;">
                <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-50" style="border-radius: 0 40px 40px 0;"></div>
                <div class="text-center text-white fade-in position-relative" style="z-index: 10;">
                    <i class="bi bi-shield-lock-fill mb-4 text-white shadow-sm" style="font-size: 8rem; filter: drop-shadow(0 0 10px rgba(0,0,0,0.5));"></i>
                    <h1 class="display-4 fw-bold mb-4" style="font-family: 'Outfit', sans-serif; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">Welcome Back!</h1>
                    <p class="lead text-white fs-4 mb-0 fw-medium" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.7); max-width: 500px; margin: 0 auto;">Secure access to the Barangay EcoReport system. Monitor and manage environmental reports with ease.</p>
                </div>
            </div>
        </div>
        
        <!-- Right Side: Login Form -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center auth-form-container p-4">
            <div class="w-100 fade-in text-white" style="max-width: 450px;">
                <div class="text-center mb-5 d-lg-none">
                    <i class="bi bi-house-heart-fill text-emerald mb-2" style="font-size: 3.5rem; color: var(--primary-light);"></i>
                    <h2 class="fw-bold" style="font-family: 'Outfit', sans-serif;"><?php echo APP_NAME; ?></h2>
                </div>
                
                <div class="mb-4">
                    <h2 class="fw-bold text-white fs-1" style="font-family: 'Outfit', sans-serif;">Sign In</h2>
                    <p class="text-white-50">Secure portal to your Barangay Eco-Account.</p>
                </div>
                
                <?php if($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 mb-4">
                        <i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="username" class="form-label fw-medium">Username or Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 rounded-start-4">
                                <i class="bi bi-person text-muted"></i>
                            </span>
                            <input type="text" class="form-control form-control-premium bg-light border-0 rounded-end-4" id="username" name="username" 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                   placeholder="Enter username or email" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <label for="password" class="form-label fw-medium">Password</label>
                            <a href="forgot_password.php" class="text-decoration-none small text-emerald fw-semibold" style="color: var(--primary-color)">Forgot?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-white bg-opacity-10 border-0 rounded-start-4">
                                <i class="bi bi-lock text-white-50"></i>
                            </span>
                            <input type="password" class="form-control form-control-premium bg-white bg-opacity-5 border-0 rounded-end-0" id="password" name="password" placeholder="Enter security key" required>
                            <button class="btn btn-outline-light border-0 rounded-end-4 px-3 bg-white bg-opacity-10" type="button" id="togglePassword">
                                <i class="bi bi-eye text-white-50"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label text-muted small" for="remember">Keep me signed in</label>
                    </div>
                    
                    <button type="submit" class="btn btn-premium w-100 py-3 mb-4">
                        Login to Account <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                    
                    <div class="text-center">
                        <p class="text-muted mb-0">Don't have an account? 
                            <a href="register.php" class="text-emerald fw-bold text-decoration-none" style="color: var(--primary-color)">Register here</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
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
</script>

<?php include 'includes/footer.php'; ?>

