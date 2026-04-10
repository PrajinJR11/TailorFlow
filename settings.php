<?php 
session_start();
if (!isset($_SESSION['loggedin'])) header('Location: index.php');
include 'config.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        // Logic to update a 'settings' table would go here
        $message = "<div class='alert alert-success border-0 shadow-lg animate__animated animate__fadeInDown'>✨ Shop Profile Updated Successfully!</div>";
    }
    
    if (isset($_POST['change_password'])) {
        if ($_POST['new_pass'] === $_POST['confirm_pass']) {
            $message = "<div class='alert alert-success border-0 shadow-lg animate__animated animate__fadeInDown'>🔐 Security Credentials Updated!</div>";
        } else {
            $message = "<div class='alert alert-danger border-0 shadow-lg animate__animated animate__shakeX'>❌ Passwords do not match.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TailorFlow | Advanced Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root { --brand: #6366f1; --bg: #f8fafc; --surface: #ffffff; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg); color: #1e293b; }
        
        /* Sidebar Navigation */
        .sidebar { width: 280px; height: 100vh; position: fixed; background: var(--surface); border-right: 1px solid #e2e8f0; padding: 2.5rem 1.5rem; z-index: 100; }
        .nav-link { display: flex; align-items: center; padding: 0.8rem 1.2rem; color: #64748b; border-radius: 14px; text-decoration: none; margin-bottom: 0.5rem; transition: 0.3s; }
        .nav-link.active { background: #eef2ff; color: var(--brand); font-weight: 700; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1); }
        .nav-link:hover:not(.active) { background: #f1f5f9; color: var(--brand); transform: translateX(5px); }

        .main-content { margin-left: 280px; padding: 3.5rem; }

        /* Settings Cards */
        .settings-card { background: var(--surface); border-radius: 24px; border: none; box-shadow: 0 10px 30px -5px rgba(0,0,0,0.04); padding: 2.5rem; height: 100%; }
        
        .glass-input { background: #f1f5f9 !important; border: 1px solid transparent !important; border-radius: 12px !important; padding: 0.75rem 1rem !important; transition: 0.3s; font-weight: 500; }
        .glass-input:focus { background: #fff !important; border-color: var(--brand) !important; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important; }

        .form-label { font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05rem; color: #94a3b8; margin-bottom: 0.6rem; }
        
        .btn-brand { background: var(--brand); color: white; border: none; border-radius: 12px; padding: 0.8rem 2rem; font-weight: 700; transition: 0.3s; }
        .btn-brand:hover { background: #4f46e5; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3); color: white; }

        .setting-icon { width: 42px; height: 42px; background: #f1f5f9; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--brand); font-size: 1.2rem; }
        
        /* Modern Toggle Switch */
        .form-check-input:checked { background-color: var(--brand); border-color: var(--brand); }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="d-flex align-items-center mb-5 px-2">
        <div class="bg-primary text-white rounded-3 p-2 me-3 shadow-sm"><i class="bi bi-scissors fs-4"></i></div>
        <h4 class="fw-800 mb-0">TailorFlow</h4>
    </div>
    <nav>
        <a href="home.php" class="nav-link"><i class="bi bi-grid-1x2-fill me-3"></i> Dashboard</a>
        <a href="customers.php" class="nav-link"><i class="bi bi-people-fill me-3"></i> Customers</a>
        <a href="orders.php" class="nav-link"><i class="bi bi-bag-check-fill me-3"></i> Orders</a>
        <a href="settings.php" class="nav-link active"><i class="bi bi-gear-fill me-3"></i> Settings</a>
        <div class="my-4 border-top"></div>
        <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left me-3"></i> Logout</a>
    </nav>
</aside>

<main class="main-content">
    <header class="mb-5 animate__animated animate__fadeIn">
        <h1 class="fw-800 mb-1">System Preferences</h1>
        <p class="text-muted">Personalize your tailoring workstation and secure your shop data.</p>
    </header>

    <div class="max-width-1000">
        <?php echo $message; ?>

        <div class="row g-4">
            <div class="col-lg-7 animate__animated animate__fadeInLeft">
                <div class="settings-card">
                    <div class="d-flex align-items-center mb-4">
                        <div class="setting-icon me-3"><i class="bi bi-shop"></i></div>
                        <div>
                            <h5 class="fw-800 mb-0">Shop Branding</h5>
                            <p class="small text-muted mb-0">Appears on your invoices and receipts.</p>
                        </div>
                    </div>

                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label">Tailor Shop Name</label>
                            <input type="text" name="shop_name" class="form-control glass-input" value="Siva Tailoring Elite" placeholder="Enter shop name">
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Business Phone</label>
                                <input type="text" name="phone" class="form-control glass-input" value="+91 98765 43210">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Tax/GST Number</label>
                                <input type="text" name="tax_id" class="form-control glass-input" placeholder="GSTIN-0000X">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Physical Address</label>
                            <textarea name="address" class="form-control glass-input" rows="3">Grand Trunk Road, Crosscut Street, Coimbatore, TN</textarea>
                        </div>

                        <div class="p-3 mb-4 rounded-4 bg-light">
                            <div class="form-check form-switch d-flex align-items-center justify-content-between p-0">
                                <label class="fw-700 small text-muted text-uppercase mb-0 ms-0" style="font-size: 0.65rem;">Auto-Generate Invoice PDF</label>
                                <input class="form-check-input" type="checkbox" checked>
                            </div>
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-brand w-100">
                            Save Business Details
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-5 animate__animated animate__fadeInRight">
                <div class="settings-card border-top border-4 border-primary">
                    <div class="d-flex align-items-center mb-4">
                        <div class="setting-icon me-3 text-warning"><i class="bi bi-shield-lock"></i></div>
                        <div>
                            <h5 class="fw-800 mb-0">Access Control</h5>
                            <p class="small text-muted mb-0">Update your login credentials.</p>
                        </div>
                    </div>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control glass-input" placeholder="••••••••">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Secure Password</label>
                            <input type="password" name="new_pass" id="pass_input" class="form-control glass-input" required>
                            <div class="progress mt-2" style="height: 4px;">
                                <div id="pass_strength" class="progress-bar bg-danger" style="width: 0%"></div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_pass" class="form-control glass-input" required>
                        </div>

                        <button type="submit" name="change_password" class="btn btn-dark w-100 fw-800 py-3 shadow-sm" style="border-radius: 12px;">
                            Update Security Key
                        </button>
                    </form>

                    <div class="mt-5 pt-4 border-top">
                        <p class="small text-muted"><i class="bi bi-info-circle me-1"></i> Use at least 8 characters with a mix of letters and numbers for better safety.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // Dynamic Password Strength Meter
    document.getElementById('pass_input').addEventListener('input', function() {
        let val = this.value;
        let bar = document.getElementById('pass_strength');
        if(val.length < 5) { bar.style.width = '30%'; bar.className = 'progress-bar bg-danger'; }
        else if(val.length < 10) { bar.style.width = '60%'; bar.className = 'progress-bar bg-warning'; }
        else { bar.style.width = '100%'; bar.className = 'progress-bar bg-success'; }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>