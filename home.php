<?php
session_start();
// Security: Redirect if not logged in
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit();
}

include 'config.php';

// --- DELETE LOGIC ---
if (isset($_GET['delete_id'])) {
    try {
        $id = (int)$_GET['delete_id'];
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: home.php?msg=deleted");
        exit();
    } catch (PDOException $e) {
        $error = "Delete Error: " . $e->getMessage();
    }
}

// --- DATA FETCHING ---
try {
    $total_customers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $total_revenue = $pdo->query("SELECT SUM(total_amount) FROM orders")->fetchColumn();

    // Fetching recent orders with Customer Names
    $recent_orders = $pdo->query("SELECT o.*, c.name FROM orders o 
        JOIN customers c ON o.customer_id = c.id 
        ORDER BY o.id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TailorFlow | Dashboard</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body { 
            background-color: #f8fafc; 
            color: #1e293b; 
            font-family: 'Inter', sans-serif;
            margin: 0;
        }

        /* Sidebar - Consistent Navy Style */
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

        /* Main Content area */
        .main-content { 
            margin-left: 260px; 
            padding: 40px; 
            width: calc(100% - 260px); 
        }

        /* Stat Cards */
        .stat-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-card h6 { color: #64748b; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin: 0; }
        .stat-card h2 { font-weight: 800; margin-top: 5px; color: #0f172a; }

        .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            background: #eff6ff;
            color: #3b82f6;
        }

        /* Table Card */
        .content-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .table thead th { 
            background: #f1f5f9;
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            padding: 12px 15px;
            border: none;
        }
        
        .table tbody td { 
            padding: 15px; 
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .status-badge {
            background: #dcfce7;
            color: #166534;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .btn-delete {
            color: #ef4444;
            padding: 5px 10px;
            border-radius: 6px;
            transition: 0.2s;
            text-decoration: none;
        }
        .btn-delete:hover {
            background: #fef2f2;
            color: #b91c1c;
        }

        .btn-edit {
            color: #3b82f6;
            padding: 5px 10px;
            border-radius: 6px;
            transition: 0.2s;
            text-decoration: none;
            margin-right: 5px;
        }
        .btn-edit:hover {
            background: #eff6ff;
            color: #1d4ed8;
        }

    </style>
</head>

<body>

    <aside class="sidebar">
        <div class="brand"><i class="bi bi-scissors"></i> TailorFlow</div>
        <nav>
            <a href="home.php" class="nav-link active"><i class="bi bi-house-door me-2"></i> Dashboard</a>
            <a href="customers.php" class="nav-link"><i class="bi bi-people me-2"></i> Customers</a>
            <a href="orders.php" class="nav-link"><i class="bi bi-cart me-2"></i> Orders</a>
            <a href="reports.php" class="nav-link"><i class="bi bi-graph-up me-2"></i> Analytics</a>
            <div style="margin-top: 50px; border-top: 1px solid #1e293b; padding-top: 20px;">
                <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
            </div>
        </nav>
    </aside>

    <main class="main-content">
        <div class="mb-4">
            <h2 class="fw-bold">Dashboard Overview</h2>
            <p class="text-muted">Welcome back! Here is what's happening today.</p>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Order deleted successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card">
                    <div>
                        <h6>Total Clients</h6>
                        <h2><?php echo $total_customers; ?></h2>
                    </div>
                    <div class="icon-box"><i class="bi bi-people"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div>
                        <h6>Total Orders</h6>
                        <h2><?php echo $total_orders; ?></h2>
                    </div>
                    <div class="icon-box"><i class="bi bi-journal-text"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div>
                        <h6>Gross Revenue</h6>
                        <h2>₹<?php echo number_format($total_revenue ?? 0); ?></h2>
                    </div>
                    <div class="icon-box" style="background: #f0fdf4; color: #22c55e;"><i class="bi bi-currency-rupee"></i></div>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Recent Orders</h5>
                <a href="orders.php" class="btn btn-sm btn-outline-primary fw-bold">View All</a>
            </div>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer Name</th>
                            <th>Order Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $serial = 1;
                        foreach($recent_orders as $o): 
                        ?>
                        <tr>
                            <td class="fw-bold text-primary"><?php echo $serial++; ?></td>
                            <td class="fw-semibold"><?php echo htmlspecialchars($o['name']); ?></td>
                            <td class="text-muted"><?php echo date('d M, Y', strtotime($o['order_date'])); ?></td>
                            <td class="fw-bold">₹<?php echo number_format($o['total_amount']); ?></td>
                            <td>
                                <span class="status-badge">
                                    <?php echo strtoupper($o['status']); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="edit_order.php?id=<?php echo $o['id']; ?>" class="btn-edit" title="Edit Order">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                
                                <a href="home.php?delete_id=<?php echo $o['id']; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Permanently delete this order?');"
                                   title="Delete Order">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($recent_orders)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No orders found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>