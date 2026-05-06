<?php
$host     = 'localhost';
$db_user  = 'root';
$db_pass  = '';        // Default XAMPP has no password
$dbname   = 'job_matcher';

$conn = new mysqli($host, $db_user, $db_pass, $dbname);

if ($conn->connect_error) {
    die("<div style='font-family:sans-serif;padding:20px;color:#c00;'>
         <strong>Database Connection Failed:</strong> " . $conn->connect_error . "
         <p>Make sure XAMPP MySQL is running and you have imported <code>database.sql</code>.</p>
         </div>");
}

$conn->set_charset("utf8mb4");

// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
