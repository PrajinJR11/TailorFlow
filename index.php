<?php
session_start();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // SET YOUR PASSWORD HERE
    if ($username == 'admin' && $password == 'admin123') {
        $_SESSION['loggedin'] = true;
        header("Location: home.php"); // Redirects to Dashboard
        exit();
    } else {
        $error = "Invalid Username or Password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - TailorFlow</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">

<style>
*{ margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
body{ height:100vh; background: linear-gradient(135deg,#6366f1,#8b5cf6); display:flex; align-items:center; justify-content:center; }
.card{ width:350px; padding:40px; border-radius:20px; background:rgba(255,255,255,0.1); backdrop-filter:blur(20px); box-shadow:0 20px 40px rgba(0,0,0,0.2); text-align:center; color:white; }
input{ width:100%; padding:12px; margin:10px 0; border:none; border-radius:10px; outline:none; }
button{ width:100%; padding:12px; background:#fff; color:#6366f1; border:none; border-radius:10px; font-weight:600; cursor:pointer; transition:0.3s; }
button:hover{ background:#ddd; }
.error-msg { color: #ff6b6b; font-size: 0.9rem; margin-top: 10px; font-weight: 500; }
</style>
</head>

<body>
<div class="card">
    <h2>✂ TailorFlow</h2>
    <p>Premium Dashboard</p>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

    <?php if(isset($error)) echo "<p class='error-msg'>$error</p>"; ?>
</div>
</body>
</html>