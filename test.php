<?php
/**
 * TailorFlow System Health Check
 */
$db_status = "Checking...";
try {
    include 'config.php';
    $db_status = "Connected";
    $db_class = "text-success";
} catch (Exception $e) {
    $db_status = "Disconnected";
    $db_class = "text-danger";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Status | TailorFlow</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .status-card { background: white; padding: 3rem; border-radius: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.05); text-align: center; max-width: 400px; width: 90%; }
        .pulse-icon { width: 80px; height: 80px; background: #dcfce7; color: #166534; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2rem; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(22, 101, 52, 0.4); } 70% { box-shadow: 0 0 0 20px rgba(22, 101, 52, 0); } 100% { box-shadow: 0 0 0 0 rgba(22, 101, 52, 0); } }
    </style>
</head>
<body>

<div class="status-card">
    <div class="pulse-icon">✓</div>
    <h2 class="fw-800 mb-1">Server Online</h2>
    <p class="text-muted mb-4">TailorFlow Core Engine is running smoothly.</p>
    
    <div class="d-flex justify-content-between p-3 bg-light rounded-4 mb-3">
        <span class="small fw-bold text-muted">PHP Version</span>
        <span class="small fw-800"><?php echo phpversion(); ?></span>
    </div>
    
    <div class="d-flex justify-content-between p-3 bg-light rounded-4">
        <span class="small fw-bold text-muted">Database</span>
        <span class="small fw-800 <?php echo $db_class; ?>"><?php echo $db_status; ?></span>
    </div>

    <a href="index.php" class="btn btn-dark w-100 mt-4 py-3 rounded-4 fw-bold">Go to Login</a>
</div>

</body>
</html>