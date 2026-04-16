<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if(!isLoggedIn()) {
    redirect('../login.php');
}

if(!isOfficial()) {
    redirect('../user/dashboard.php');
}

// Redirect to admin dashboard
redirect('dashboard.php');
?>
