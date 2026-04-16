<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link href="<?php echo APP_URL; ?>/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <!-- Leaflet JS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <!-- Custom CSS -->
    <link href="<?php echo APP_URL; ?>/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    
    <style>
        :root {
            --bg-deep: #021a15;
            --bg-green-gradient: linear-gradient(135deg, #021a15 0%, #064e3b 100%);
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.12);
            --text-main: #f8fafc;
        }
        
        body {
            background-color: var(--bg-deep) !important;
            background-image: var(--bg-green-gradient) !important;
            background-attachment: fixed !important;
            color: var(--text-main) !important;
            margin: 0;
            min-height: 100vh;
        }

        .glass-card {
            background: var(--glass-bg) !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid var(--glass-border) !important;
        }

        .bg-white, .bg-light, .card, .modal-content {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: white !important;
        }
        
        .text-dark {
            color: #f8fafc !important;
        }

        /* Legacy support and immediate fixes during migration */
        .navbar-brand {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            background: linear-gradient(135deg, #10b981, #14b8a6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #10b981, #14b8a6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
    </style>
</head>
<body style="background-image: var(--bg-green-gradient) !important; background-color: #021a15 !important; background-attachment: fixed !important; color: white !important; margin: 0; min-height: 100vh;">
    <?php if(isLoggedIn()): ?>
        <nav class="navbar navbar-expand-lg sticky-nav shadow-sm mb-4">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="<?php echo APP_URL; ?>/<?php echo isOfficial() ? 'admin/dashboard.php' : 'user/dashboard.php'; ?>">
                    <i class="bi bi-house-heart-fill me-2" style="color: var(--primary-color); -webkit-text-fill-color: initial;"></i>
                    <span><?php echo APP_NAME; ?></span>
                </a>
                
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <div class="navbar-nav ms-auto align-items-center">
                        <div class="nav-item">
                            <div class="d-flex align-items-center gap-3">
                                <span class="fw-medium text-white d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                                <div class="avatar-circle overflow-hidden shadow-sm" style="width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border: 2px solid var(--glass-border); display: flex; align-items: center; justify-content: center; color: white;">
                                    <?php if(isset($_SESSION['profile_image']) && $_SESSION['profile_image']): ?>
                                        <img src="<?php echo APP_URL; ?>/uploads/profiles/<?php echo $_SESSION['profile_image']; ?>" class="w-100 h-100 object-fit-cover">
                                    <?php else: ?>
                                        <i class="bi bi-person-fill"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    
    <?php 
    $flash = getFlashMessages();
    foreach($flash as $type => $message): ?>
        <div class="container mt-3">
            <div class="alert alert-<?php echo $type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endforeach; ?>
