<?php
require_once 'functions.php';

// Logout admin user
logoutAdmin();

// Redirect to login page
header('Location: admin-login.php');
exit;
?>
