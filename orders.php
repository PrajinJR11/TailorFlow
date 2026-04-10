<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit();
}
include 'config.php';

// Add Order Logic
if (isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO orders (customer_id, order_date, delivery_date, total_amount, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['customer_id'],
        $_POST['order_date'],
        $_POST['delivery_date'],
        $_POST['amount'],
        $_POST['status']
    ]);
    header("Location: orders.php");
    exit();
}

// DELETE ORDER LOGIC
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: orders.php");
    exit();
}

// Fetch Customers for the dropdown
$customers = $pdo->query("SELECT * FROM customers ORDER BY name ASC")->fetchAll();
// Fetch Orders with Customer Names
$orders = $pdo->query("SELECT o.*, c.name FROM orders o JOIN customers c ON o.customer_id=c.id ORDER BY o.id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TailorFlow | Orders</title>
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
            padding: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .table { margin-bottom: 0; }
        .table thead th { 
            background: #f1f5f9;
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 15px;
            border: none;
        }
        .table tbody td { 
            padding: 15px; 
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .serial-no { font-weight: 700; color: #3b82f6; width: 60px; }

        .status-badge {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .bg-pending { background: #fef3c7; color: #92400e; }
        .bg-delivered { background: #dcfce7; color: #166534; }
        .bg-default { background: #e2e8f0; color: #475569; }

        .form-label { font-weight: 600; font-size: 0.85rem; color: #475569; margin-bottom: 8px; }
        .form-control, .form-select {
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
        .btn-submit:hover { background: #2563eb; }
        
        .btn-delete {
            color: #ef4444;
            background: #fef2f2;
            border: none;
            padding: 5px 10px;
            border-radius: 6px;
            transition: 0.2s;
        }
        .btn-delete:hover { background: #fee2e2; color: #dc2626; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand"><i class="bi bi-scissors"></i> TailorFlow</div>
        <nav>
            <a href="home.php" class="nav-link"><i class="bi bi-house-door me-2"></i> Dashboard</a>
            <a href="customers.php" class="nav-link"><i class="bi bi-people me-2"></i> Customers</a>
            <a href="orders.php" class="nav-link active"><i class="bi bi-cart-check me-2"></i> Orders</a>
            <a href="reports.php" class="nav-link"><i class="bi bi-graph-up me-2"></i> Analytics</a>
            <div style="margin-top: 50px; border-top: 1px solid #1e293b; padding-top: 20px;">
                <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
            </div>
        </nav>
    </aside>

    <main class="main-content">
        <div class="mb-4">
            <h2 class="fw-bold">Orders</h2>
            <p class="text-muted">Manage measurements, delivery dates, and payment status.</p>
        </div>

        <div class="content-card">
            <h5 class="mb-4 fw-bold">Create New Order</h5>
            <form method="POST" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Customer Name</label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">Select Customer...</option>
                        <?php foreach($customers as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Order Date</label>
                    <input type="date" name="order_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Delivery Date</label>
                    <input type="date" name="delivery_date" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Amount (₹)</label>
                    <input type="number" name="amount" class="form-control" placeholder="0.00" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option>Pending</option>
                        <option>Ready</option>
                        <option>Delivered</option>
                    </select>
                </div>
                <div class="col-12 mt-4">
                    <button name="add" class="btn btn-submit">Save New Order</button>
                </div>
            </form>
        </div>

        <div class="content-card">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $serial = 1;
                    foreach($orders as $o): 
                    ?>
                    <tr>
                        <td class="serial-no"><?php echo $serial++; ?></td>
                        <td class="fw-semibold"><?php echo htmlspecialchars($o['name']); ?></td>
                        <td class="text-muted"><?php echo date('d M, Y', strtotime($o['order_date'])); ?></td>
                        <td class="fw-bold text-dark">₹<?php echo number_format($o['total_amount']); ?></td>
                        <td>
                            <?php 
                                if($o['status'] == 'Pending') {
                                    $statusClass = 'bg-pending';
                                } elseif($o['status'] == 'Delivered') {
                                    $statusClass = 'bg-delivered';
                                } else {
                                    $statusClass = 'bg-default';
                                }
                            ?>
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <?php echo strtoupper($o['status']); ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="orders.php?delete=<?php echo $o['id']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this order?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>