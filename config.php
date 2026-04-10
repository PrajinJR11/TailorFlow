<?php
// DATABASE CONFIGURATION

$host = "localhost";
$dbname = "tailoring_db";   // ✅ IMPORTANT: your correct database name
$username = "root";
$password = "";

// CREATE CONNECTION
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // ERROR HANDLING
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>