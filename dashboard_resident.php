<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

if(isOfficial()) {
    redirect('admin/dashboard.php');
}

redirect('user/dashboard.php');
?>
