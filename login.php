<?php
/**
 * TailorFlow Premium - Secure Authentication
 */
session_start();
require 'config.php'; // Ensure your DB connection is available

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitize Inputs (UX: Trim whitespace to prevent "accidental space" login errors)
    $user = isset($_POST['username']) ? trim($_POST['username']) : '';
    $pass = isset($_POST['password']) ? $_POST['password'] : '';

    // 2. Authentication Logic
    // UX Tip: In a real app, you'd fetch the hashed password from the 'users' table
    if ($user === 'admin' && $pass === 'admin123') {
        
        // 3. Security UX: Prevent Session Fixation
        session_regenerate_id(true);

        // 4. Set Session Variables
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id']  = 1;
        $_SESSION['username'] = 'Admin';
        $_SESSION['last_login'] = date('Y-m-d H:i:s');

        // 5. Success Redirect
        header('Location: home.php');
        exit();

    } else {
        // 6. Error Handling
        // UX Win: Be specific enough for the admin, but safe from hackers
        $_SESSION['error'] = 'Access Denied: Incorrect credentials.';
        header('Location: index.php');
        exit();
    }
} else {
    // Redirect if they try to access login.php directly without posting
    header('Location: index.php');
    exit();
}
?>