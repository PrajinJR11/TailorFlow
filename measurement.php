<?php
// 1. FORCED ERROR REPORTING
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit();
}

include 'config.php';

// 2. GET CUSTOMER ID
$customer_id = isset($_GET['cid']) ? (int)$_GET['cid'] : 0;

if ($customer_id === 0) {
    die("Error: No Customer ID provided. Go back to <a href='customers.php'>Customers</a>.");
}

// 3. FETCH CUSTOMER DETAILS
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

if (!$customer) {
    die("Error: Customer not found in database.");
}

// 4. SAVE OR UPDATE LOGIC
if (isset($_POST['save'])) {
    try {
        // Update Personal Details
        $updateCustomer = $pdo->prepare("UPDATE customers SET name=?, phone=?, email=?, address=? WHERE id=?");
        $updateCustomer->execute([
            $_POST['name'], 
            $_POST['phone'], 
            $_POST['email'], 
            $_POST['address'], 
            $customer_id
        ]);

        // Measurement Logic
        $check = $pdo->prepare("SELECT id FROM measurements WHERE customer_id = ?");
        $check->execute([$customer_id]);
        
        if ($check->rowCount() > 0) {
            $sql = "UPDATE measurements SET neck=?, chest=?, waist=?, shoulder=?, sleeve_length=?, shirt_length=?, pant_length=?, hips=? WHERE customer_id=?";
        } else {
            $sql = "INSERT INTO measurements (neck, chest, waist, shoulder, sleeve_length, shirt_length, pant_length, hips, customer_id) VALUES (?,?,?,?,?,?,?,?,?)";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['neck'], $_POST['chest'], $_POST['waist'], $_POST['shoulder'], 
            $_POST['sleeve_length'], $_POST['shirt_length'], $_POST['pant_length'], $_POST['hips'], 
            $customer_id
        ]);

        $message = "✅ Profile and Measurements updated successfully!";
        
        // Refresh customer data
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$customer_id]);
        $customer = $stmt->fetch();

    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}

// 5. FETCH EXISTING MEASUREMENTS
$stmt = $pdo->prepare("SELECT * FROM measurements WHERE customer_id = ?");
$stmt->execute([$customer_id]);
$m = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TailorFlow | Edit <?php echo htmlspecialchars($customer['name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body { 
            background-color: #f8fafc; 
            color: #1e293b; 
            font-family: 'Inter', sans-serif;
            margin: 0;
        }

        .sidebar { 
            width: 260px; 
            height: 100vh; 
            position: fixed; 
            background: #0f172a; 
            padding: 2rem 1.2rem; 
            z-index: 1000;
        }

        .brand {
            font-size: 1.4rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 2.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-link { 
            display: flex; 
            align-items: center; 
            padding: 12px 15px; 
            color: #94a3b8; 
            text-decoration: none; 
            border-radius: 8px; 
            margin-bottom: 5px; 
            font-weight: 500;
            transition: 0.2s;
        }

        .nav-link:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .nav-link.active { background: #3b82f6; color: #fff; }

        .main-content { 
            margin-left: 260px; 
            padding: 40px; 
            width: calc(100% - 260px); 
        }

        .content-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .form-label { font-weight: 600; font-size: 0.85rem; color: #475569; margin-bottom: 8px; }
        .form-control {
            border: 1px solid #cbd5e1;
            padding: 10px 12px;
            border-radius: 8px;
        }

        .btn-submit {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            transition: 0.2s;
        }
        .btn-submit:hover { background: #2563eb; color: #fff; }

        .section-divider { 
            border-bottom: 1px solid #e2e8f0; 
            margin: 30px 0 20px 0; 
            padding-bottom: 10px;
            font-weight: 700; 
            color: #0f172a; 
            font-size: 1.1rem; 
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand"><i class="bi bi-scissors"></i> TailorFlow</div>
        <nav>
            <a href="home.php" class="nav-link"><i class="bi bi-house-door me-2"></i> Dashboard</a>
            <a href="customers.php" class="nav-link active"><i class="bi bi-people me-2"></i> Customers</a>
            <a href="orders.php" class="nav-link"><i class="bi bi-cart-check me-2"></i> Orders</a>
            <a href="reports.php" class="nav-link"><i class="bi bi-graph-up me-2"></i> Analytics</a>
            <div style="margin-top: 50px; border-top: 1px solid #1e293b; padding-top: 20px;">
                <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
            </div>
        </nav>
    </aside>

    <main class="main-content">
        <div class="mb-4 d-flex align-items-center gap-3">
            <a href="customers.php" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left"></i></a>
            <div>
                <h2 class="fw-bold mb-0">Measurement Profile</h2>
                <p class="text-muted mb-0">Update personal info and tailored sizing for <?php echo htmlspecialchars($customer['name']); ?></p>
            </div>
        </div>

        <?php if(isset($message)) echo "<div class='alert alert-success border-0 shadow-sm'>$message</div>"; ?>
        <?php if(isset($error)) echo "<div class='alert alert-danger border-0 shadow-sm'>$error</div>"; ?>

        <div class="content-card">
            <form method="POST">
                <div class="section-divider mt-0">Personal Information</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($customer['phone']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($customer['email']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Location / Address</label>
                        <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($customer['address']); ?>">
                    </div>
                </div>

                <div class="section-divider">Body Measurements (Inches)</div>
                <div class="row g-4">
                    <div class="col-md-3 col-6">
                        <label class="form-label">Neck</label>
                        <input type="number" step="0.01" name="neck" class="form-control" value="<?php echo $m['neck'] ?? ''; ?>">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label">Chest</label>
                        <input type="number" step="0.01" name="chest" class="form-control" value="<?php echo $m['chest'] ?? ''; ?>">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label">Waist</label>
                        <input type="number" step="0.01" name="waist" class="form-control" value="<?php echo $m['waist'] ?? ''; ?>">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label">Shoulder</label>
                        <input type="number" step="0.01" name="shoulder" class="form-control" value="<?php echo $m['shoulder'] ?? ''; ?>">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label">Sleeve</label>
                        <input type="number" step="0.01" name="sleeve_length" class="form-control" value="<?php echo $m['sleeve_length'] ?? ''; ?>">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label">Shirt Len</label>
                        <input type="number" step="0.01" name="shirt_length" class="form-control" value="<?php echo $m['shirt_length'] ?? ''; ?>">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label">Pant Len</label>
                        <input type="number" step="0.01" name="pant_length" class="form-control" value="<?php echo $m['pant_length'] ?? ''; ?>">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label">Hips</label>
                        <input type="number" step="0.01" name="hips" class="form-control" value="<?php echo $m['hips'] ?? ''; ?>">
                    </div>

                    <div class="col-12 mt-5">
                        <button name="save" class="btn btn-submit">Update Complete Record</button>
                    </div>
                </div>
            </form>
        </div>
    </main>

</body>
</html>