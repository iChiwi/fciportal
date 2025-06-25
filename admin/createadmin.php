<?php
// Database connection | Ensure you have a config.php file with the correct database connection settings
require '../config.php';

// Enter your admin username and password here
$username = '';
$password = '';
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$sql = "INSERT INTO admins (username, password) VALUES (:username, :password)";
$stmt = $pdo->prepare($sql);
$stmt->execute(['username' => $username, 'password' => $hashedPassword]);

echo "Admin user created successfully!";
?>
