<?php
session_start();

// Completely destroy the session
unset($_SESSION['suggestions_chat']);

// Redirect back to suggestions page
header('Location: suggestions.php');
exit;
?>
