<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

if(isResident()) {
    redirect('user/dashboard.php');
}

redirect('admin/dashboard.php');
?>
