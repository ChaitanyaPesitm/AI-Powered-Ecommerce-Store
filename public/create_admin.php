<?php
require_once __DIR__ . '/../config/functions.php';
if (isAdmin()) { echo "Admin exists."; exit; }
if ($_SERVER['REQUEST_METHOD']!=='POST') {
echo '<form method="post"><h3>Create Admin (one-time)</h3>
<label>Name <input name="name"></label><br>
<label>Email <input name="email" type="email"></label><br>
<label>Password <input name="password" type="password"></label><br>
<button>Create</button></form>'; exit;
}
$name=$_POST['name']??''; $email=$_POST['email']??''; $pass=$_POST['password']??'';
if(!$name||!$email||!$pass) die('All fields required.');
$u = findUserByEmail($email);
if ($u) die('Email exists.');
global $pdo;
$pdo->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?, 'admin')")
    ->execute([$name,$email,password_hash($pass, PASSWORD_DEFAULT)]);
echo "Admin created. <a href='".base_url('admin/login.php')."'>Go to Admin Login</a>";
