<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit();
}
include 'config.php';

// Add Customer Logic
if (isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['name'], $_POST['phone'], $_POST['email'], $_POST['address']]);
    header("Location: customers.php");
    exit();
}

// FIXED DELETE CUSTOMER LOGIC (Deletes child records first to avoid SQL errors)
if (isset($_GET['delete'])) {
    $cid = $_GET['delete'];
    
    // 1. Delete associated measurements
    $stmt1 = $pdo->prepare("DELETE FROM measurements WHERE customer_id = ?");
    $stmt1->execute([$cid]);
    
    // 2. Delete associated orders
    $stmt2 = $pdo->prepare("DELETE FROM orders WHERE customer_id = ?");
    $stmt2->execute([$cid]);
    
    // 3. Finally, delete the customer record
    $stmt3 = $pdo->prepare("DELETE FROM customers WHERE id = ?");
    $stmt3->execute([$cid]);
    
    header("Location: customers.php");
    exit();
}

$customers = $pdo->query("SELECT * FROM customers ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TailorFlow | Customers</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
        
        .wrapper { display: flex; min-height: 100vh; }

        .sidebar { 
            width: 260px; 
            background: #0f172a; 
            padding: 2rem 1.2rem; 
            position: fixed;
            height: 100vh;
            color: white;
        }

        .brand { font-size: 1.5rem; font-weight: 800; margin-bottom: 2.5rem; display: flex; align-items: center; gap: 10px; }
        .nav-link { display: flex; align-items: center; padding: 12px 15px; color: #94a3b8; text-decoration: none; border-radius: 8px; margin-bottom: 5px; font-weight: 500; }
        .nav-link:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .nav-link.active { background: #3b82f6; color: #fff; }

        .main-content { 
            margin-left: 260px; 
            flex: 1; 
            padding: 40px; 
            background: #f8fafc;
        }

        .content-card {
            background: white; border-radius: 12px; border: 1px solid #e2e8f0;
            padding: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 30px;
        }

        .table { vertical-align: middle; }
        .table thead th { 
            background: #f1f5f9; color: #64748b; font-size: 0.75rem; 
            text-transform: uppercase; padding: 15px; border: none; 
        }
        .table tbody td { padding: 18px 15px; border-bottom: 1px solid #f1f5f9; }

        .btn-edit {
            background: #f1f5f9; color: #475569; font-size: 0.8rem; font-weight: 600;
            padding: 8px 16px; border-radius: 8px; border: 1px solid #e2e8f0;
            text-decoration: none; display: inline-flex; align-items: center; white-space: nowrap;
        }
        .btn-edit:hover { background: #e2e8f0; color: #1e293b; }

        .btn-delete-cust {
            color: #ef4444; background: #fef2f2; font-size: 1rem;
            padding: 8px 12px; border-radius: 8px; border: 1px solid #fee2e2;
            text-decoration: none; display: inline-flex; align-items: center;
            transition: 0.2s;
        }
        .btn-delete-cust:hover { background: #fee2e2; color: #dc2626; }

        .form-label { font-weight: 600; font-size: 0.8rem; color: #64748b; }
    </style>
</head>
<body>

<div class="wrapper">
    <aside class="sidebar">
        <div class="brand"><i class="bi bi-scissors"></i> TailorFlow</div>
        <nav>
            <a href="home.php" class="nav-link"><i class="bi bi-house-door me-2"></i> Dashboard</a>
            <a href="customers.php" class="nav-link active"><i class="bi bi-people me-2"></i> Customers</a>
            <a href="orders.php" class="nav-link"><i class="bi bi-cart me-2"></i> Orders</a>
            <a href="reports.php" class="nav-link"><i class="bi bi-graph-up me-2"></i> Analytics</a>
            <div style="margin-top: 50px; border-top: 1px solid #1e293b; padding-top: 20px;">
                <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
            </div>
        </nav>
    </aside>

    <main class="main-content">
        <div class="mb-4">
            <h2 class="fw-bold">Customer Directory</h2>
            <p class="text-muted">Register and manage your client records.</p>
        </div>

        <div class="content-card">
            <h6 class="fw-bold mb-3">Register New Client</h6>
            <form method="POST" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">FULL NAME</label>
                    <input name="name" class="form-control" placeholder="John Doe" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">PHONE</label>
                    <input name="phone" class="form-control" placeholder="9876543210">
                </div>
                <div class="col-md-3">
                    <label class="form-label">EMAIL ADDRESS</label>
                    <input name="email" type="email" class="form-control" placeholder="name@email.com">
                </div>
                <div class="col-md-2">
                    <label class="form-label">CITY / LOCATION</label>
                    <input name="address" class="form-control" placeholder="Mumbai">
                </div>
                <div class="col-md-2">
                    <button name="add" class="btn btn-primary w-100 py-2 fw-bold">Save Customer</button>
                </div>
            </form>
        </div>

        <div class="content-card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Client Name</th>
                            <th>Contact</th>
                            <th>Email Address</th>
                            <th>Location</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        foreach($customers as $c): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><span class="fw-bold text-dark"><?php echo htmlspecialchars($c['name']); ?></span></td>
                            <td><?php echo htmlspecialchars($c['phone']); ?></td>
                            <td><?php echo htmlspecialchars($c['email'] ?: 'N/A'); ?></td>
                            <td class="text-muted"><?php echo htmlspecialchars($c['address']); ?></td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="measurement.php?cid=<?php echo $c['id']; ?>" class="btn-edit">
                                        <i class="bi bi-pencil-square me-2"></i> Edit Details
                                    </a>
                                    <a href="customers.php?delete=<?php echo $c['id']; ?>" 
                                       class="btn-delete-cust" 
                                       onclick="return confirm('Are you sure? This will permanently delete this customer and ALL their orders and measurements.')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

</body>
</html>