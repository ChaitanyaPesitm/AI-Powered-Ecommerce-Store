<?php
// admin/logout.php - Admin Logout
session_start();

// Clear only admin session data (keep public user logged in)
unset($_SESSION['admin_user']);

// Redirect to admin login page
header('Location: login.php');
exit;
