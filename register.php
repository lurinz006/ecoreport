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
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitizeInput($_POST['full_name']);
    $contact_number = sanitizeInput($_POST['contact_number']);
    $address = sanitizeInput($_POST['address']);
    $role = sanitizeInput($_POST['role']);

    // Validation
    if(empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $error = 'Please fill in all required fields';
    } elseif(!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } elseif(strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif(strlen($username) < 3) {
        $error = 'Username must be at least 3 characters long';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        $user = new User($db);

        // Check if username already exists
        $check_query = "SELECT id FROM users WHERE username = :username OR email = :email";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(":username", $username);
        $check_stmt->bindParam(":email", $email);
        $check_stmt->execute();

        if($check_stmt->rowCount() > 0) {
            $error = 'Username or email already exists';
        } else {
            $user_id = $user->register($username, $email, $password, $full_name, $role, $contact_number, $address);
            
            if($user_id) {
                flashMessage('success', 'Registration successful! Please login to continue.');
                redirect('login.php');
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

$page_title = "Register - " . APP_NAME;
include 'includes/header.php';
?>

<div class="container-fluid p-0">
    <div class="row g-0 min-vh-100">
        <!-- Left Side: Image/Branding (Dynamic) -->
        <div class="col-lg-5 d-none d-lg-block">
            <div class="hero-gradient h-100 d-flex align-items-center justify-content-center p-5 sticky-top">
                <div class="text-center text-white fade-in">
                    <i class="bi bi-person-plus-fill mb-4" style="font-size: 8rem;"></i>
                    <h1 class="display-4 fw-bold mb-4" style="font-family: 'Outfit', sans-serif;">Join the Community</h1>
                    <p class="lead text-white-50 fs-4 mb-0">Create an account to help us build a cleaner and safer environment for everyone in our Barangay.</p>
                </div>
            </div>
        </div>
        
        <!-- Right Side: Registration Form -->
        <div class="col-lg-7 d-flex align-items-center justify-content-center auth-form-container p-4 p-md-5">
            <div class="w-100 fade-in text-white" style="max-width: 650px;">
                <div class="text-center mb-5 d-lg-none">
                    <i class="bi bi-house-heart-fill text-emerald mb-2" style="font-size: 3.5rem; color: var(--primary-light);"></i>
                    <h2 class="fw-bold" style="font-family: 'Outfit', sans-serif;"><?php echo APP_NAME; ?></h2>
                </div>
                
                <div class="mb-5">
                    <h2 class="fw-bold fs-1 text-white" style="font-family: 'Outfit', sans-serif;">Create Account</h2>
                    <p class="text-white-50">Join the movement for a greener, safer Barangay.</p>
                </div>
                
                <?php if($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 mb-4">
                        <i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="full_name" class="form-label fw-medium">Full Name *</label>
                            <input type="text" class="form-control form-control-premium bg-light border-0" id="full_name" name="full_name" 
                                   value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" 
                                   placeholder="e.g. John Doe" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="username" class="form-label fw-medium">Username *</label>
                            <input type="text" class="form-control form-control-premium bg-light border-0" id="username" name="username" 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                   placeholder="Choose a username" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label fw-medium">Email Address *</label>
                        <input type="email" class="form-control form-control-premium bg-light border-0" id="email" name="email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                               placeholder="your@email.com" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="password" class="form-label fw-medium">Security Key *</label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-premium bg-white bg-opacity-5 border-0 rounded-start-4" id="password" name="password" placeholder="Min. 6 chars" required>
                                <button class="btn btn-outline-light border-0 rounded-end-4 px-3 bg-white bg-opacity-10" type="button" id="togglePassword">
                                    <i class="bi bi-eye text-white-50"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="confirm_password" class="form-label fw-medium">Confirm Key *</label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-premium bg-white bg-opacity-5 border-0 rounded-start-4" id="confirm_password" name="confirm_password" placeholder="Repeat key" required>
                                <button class="btn btn-outline-light border-0 rounded-end-4 px-3 bg-white bg-opacity-10" type="button" id="toggleConfirm">
                                    <i class="bi bi-eye text-white-50"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="role" class="form-label fw-medium">I am registering as a *</label>
                        <select class="form-select form-control-premium bg-light border-0" id="role" name="role" required>
                            <option value="">Select Account Type</option>
                            <option value="resident" <?php echo (isset($_POST['role']) && $_POST['role'] === 'resident') ? 'selected' : ''; ?>>Resident / Citizen</option>
                            <option value="official" <?php echo (isset($_POST['role']) && $_POST['role'] === 'official') ? 'selected' : ''; ?>>Barangay Official</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="contact_number" class="form-label fw-medium">Contact Number</label>
                        <input type="tel" class="form-control form-control-premium bg-light border-0" id="contact_number" name="contact_number" 
                               value="<?php echo isset($_POST['contact_number']) ? htmlspecialchars($_POST['contact_number']) : ''; ?>"
                               placeholder="e.g. 09123456789">
                    </div>

                    <div class="mb-4">
                        <label for="address" class="form-label fw-medium">Address</label>
                        <textarea class="form-control form-control-premium bg-light border-0" id="address" name="address" rows="3" placeholder="Purok, Street, Barangay..."><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                    </div>

                    <div class="mb-5 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" required>
                        <label class="form-check-label text-muted small" for="terms">
                            I agree to the <a href="#" class="text-emerald fw-bold text-decoration-none" style="color: var(--primary-color)" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-premium w-100 py-3 mb-4">
                        Create My Account <i class="bi bi-person-check ms-2"></i>
                    </button>
                    
                    <div class="text-center">
                        <p class="text-muted mb-0">Already have an account? 
                            <a href="login.php" class="text-emerald fw-bold text-decoration-none" style="color: var(--primary-color)">Login here</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 p-4">
                <h5 class="modal-title fw-bold fs-4">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-0">
                <div class="mb-4">
                    <h6 class="fw-bold text-emerald" style="color: var(--primary-color)">1. Account Registration</h6>
                    <p class="text-muted">You must provide accurate and complete information when registering an account. Impersonation of others is strictly prohibited.</p>
                </div>
                
                <div class="mb-4">
                    <h6 class="fw-bold text-emerald" style="color: var(--primary-color)">2. Report Submission</h6>
                    <p class="text-muted">All reports must be truthful and accurate. Providing false information intentionally may result in the permanent suspension of your account.</p>
                </div>
                
                <div class="mb-4">
                    <h6 class="fw-bold text-emerald" style="color: var(--primary-color)">3. Multimedia Content</h6>
                    <p class="text-muted">Any photos uploaded must be directly related to the environmental incident being reported. Inappropriate content will be removed immediately.</p>
                </div>
                
                <div class="mb-0">
                    <h6 class="fw-bold text-emerald" style="color: var(--primary-color)">4. Data Privacy</h6>
                    <p class="text-muted">Your personal information will be kept secure and only used by Barangay officials for the purpose of communicating about your reports.</p>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-premium rounded-3 px-4" data-bs-dismiss="modal" onclick="document.getElementById('terms').checked = true;">Accept Terms</button>
            </div>
        </div>
    </div>
</div>

<script>
function setupPasswordToggle(inputId, toggleId) {
    const input = document.getElementById(inputId);
    const toggle = document.getElementById(toggleId);
    const icon = toggle.querySelector('i');
    
    toggle.addEventListener('click', function() {
        if(input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
}

setupPasswordToggle('password', 'togglePassword');
setupPasswordToggle('confirm_password', 'toggleConfirm');

document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    if(password.length < 6) {
        this.setCustomValidity('Password must be at least 6 characters long');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirm = this.value;
    if(password !== confirm) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php include 'includes/footer.php'; ?>

