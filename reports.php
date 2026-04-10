<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit();
}
include 'config.php';

// FETCH DATA
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0;
$total_customers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn() ?: 0;
$total_revenue = $pdo->query("SELECT SUM(total_amount) FROM orders")->fetchColumn() ?: 0;
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='Pending'")->fetchColumn() ?: 0;
$completed_orders = $total_orders - $pending_orders;

// FETCH REVENUE GROUPED BY MONTH
$monthly_query = $pdo->query("SELECT DATE_FORMAT(order_date, '%b %Y') as month_name, SUM(total_amount) as revenue 
                             FROM orders 
                             GROUP BY YEAR(order_date), MONTH(order_date) 
                             ORDER BY YEAR(order_date) ASC, MONTH(order_date) ASC");
$monthly_data = $monthly_query->fetchAll(PDO::FETCH_ASSOC);

$months = [];
$revenues = [];
foreach($monthly_data as $row) {
    $months[] = $row['month_name'];
    $revenues[] = (float)$row['revenue'];
}

// Fallback for empty charts
if (empty($months)) {
    $months = ['No Data'];
    $revenues = [0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics | TailorFlow</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #1e293b;
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

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-card h6 { color: #64748b; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin: 0; }
        .stat-card h3 { font-weight: 800; margin-top: 5px; margin-bottom: 0; color: #0f172a; }

        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .bg-blue-light { background: #eff6ff; color: #3b82f6; }
        .bg-green-light { background: #f0fdf4; color: #22c55e; }
        .bg-orange-light { background: #fff7ed; color: #f97316; }

        /* FIXED: Added position relative and a wrapper height to stabilize charts */
        .chart-container {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #e2e8f0;
            position: relative;
            margin-bottom: 20px;
        }

        .chart-wrapper {
            position: relative;
            height: 350px;
            width: 100%;
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <div class="brand"><i class="bi bi-scissors"></i> TailorFlow</div>
        <nav>
            <a href="home.php" class="nav-link"><i class="bi bi-house-door me-2"></i> Dashboard</a>
            <a href="customers.php" class="nav-link"><i class="bi bi-people me-2"></i> Customers</a>
            <a href="orders.php" class="nav-link"><i class="bi bi-cart me-2"></i> Orders</a>
            <a href="reports.php" class="nav-link active"><i class="bi bi-graph-up me-2"></i> Analytics</a>
            <div style="margin-top: 50px; border-top: 1px solid #1e293b; padding-top: 20px;">
                <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
            </div>
        </nav>
    </aside>

    <main class="main-content">
        <div class="mb-4">
            <h2 class="fw-bold">Business Analytics</h2>
            <p class="text-muted">In-depth performance data for your tailoring shop.</p>
        </div>

        <div class="row mb-4 g-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <h6>Total Orders</h6>
                        <h3><?php echo $total_orders; ?></h3>
                    </div>
                    <div class="icon-box bg-blue-light"><i class="bi bi-bag"></i></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <h6>Customers</h6>
                        <h3><?php echo $total_customers; ?></h3>
                    </div>
                    <div class="icon-box bg-blue-light"><i class="bi bi-people"></i></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <h6>Revenue</h6>
                        <h3>₹<?php echo number_format($total_revenue); ?></h3>
                    </div>
                    <div class="icon-box bg-green-light"><i class="bi bi-currency-rupee"></i></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <h6>Pending</h6>
                        <h3><?php echo $pending_orders; ?></h3>
                    </div>
                    <div class="icon-box bg-orange-light"><i class="bi bi-clock-history"></i></div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="fw-bold mb-4">Order Status Distribution</h5>
                    <div class="chart-wrapper">
                        <canvas id="ordersChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="fw-bold mb-4">Revenue Overview</h5>
                    <div class="chart-wrapper">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    };

    // Orders Chart (Doughnut)
    new Chart(document.getElementById('ordersChart'), {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Pending'],
            datasets: [{
                data: [<?php echo $completed_orders; ?>, <?php echo $pending_orders; ?>],
                backgroundColor: ['#22c55e', '#f97316'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: chartOptions
    });

    // Revenue Chart (Bar)
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Revenue (₹)',
                data: <?php echo json_encode($revenues); ?>,
                backgroundColor: '#3b82f6',
                borderRadius: 8,
                barThickness: 'flex',
                maxBarThickness: 40
            }]
        },
        options: {
            ...chartOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return '₹' + value.toLocaleString(); }
                    }
                }
            }
        }
    });
    </script>
</body>
</html>