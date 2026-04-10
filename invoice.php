<?php 
session_start();
if (!isset($_SESSION['loggedin'])) header('Location: index.php');
include 'config.php';

$order_id = $_GET['id'] ?? 0;
// Fetching logic remains the same...
$stmt = $pdo->prepare("SELECT o.*, c.name, c.phone, c.address FROM orders o JOIN customers c ON o.customer_id = c.id WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();
if (!$order) exit("Order not found.");

$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id=?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Premium_Invoice_<?php echo $order['id']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --accent: #6366f1;
            --dark-sidebar: #0f172a;
            --glass: rgba(255, 255, 255, 0.9);
        }

        body { 
            background: #e2e8f0; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            padding: 40px 0;
        }

        /* The Main Container */
        .invoice-card {
            max-width: 950px;
            margin: auto;
            background: #fff;
            border-radius: 30px;
            overflow: hidden;
            display: flex;
            box-shadow: 0 40px 100px -20px rgba(0,0,0,0.2);
            min-height: 700px;
        }

        /* Left Side: Dark Info Panel */
        .info-sidebar {
            width: 35%;
            background: var(--dark-sidebar);
            color: #fff;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .brand-logo {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .client-section { margin-top: 60px; }
        .label-muted { color: #64748b; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 2px; font-weight: 700; margin-bottom: 10px; display: block;}

        /* Right Side: Billing Table */
        .billing-main {
            width: 65%;
            padding: 50px 60px;
            background: #fff;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 50px;
        }

        .big-text { font-size: 3.5rem; font-weight: 800; line-height: 1; color: #f1f5f9; position: absolute; top: 20px; right: 40px; z-index: 0; }

        .table-premium thead th {
            background: transparent;
            border-bottom: 2px solid #f1f5f9;
            color: #64748b;
            font-size: 0.8rem;
            padding-bottom: 20px;
        }

        .table-premium tbody td {
            padding: 25px 0;
            border-bottom: 1px solid #f8fafc;
            vertical-align: middle;
        }

        .item-title { font-weight: 700; color: #1e293b; margin-bottom: 4px; display: block;}
        
        .summary-card {
            background: #f8fafc;
            border-radius: 20px;
            padding: 30px;
            margin-top: 40px;
        }

        .grand-total {
            font-size: 2rem;
            font-weight: 800;
            color: var(--accent);
        }

        .badge-status {
            background: rgba(99, 102, 241, 0.1);
            color: var(--accent);
            padding: 8px 16px;
            border-radius: 100px;
            font-weight: 700;
            font-size: 0.8rem;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .invoice-card { box-shadow: none; border-radius: 0; width: 100%; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="container no-print mb-4 d-flex justify-content-center gap-3">
    <button onclick="window.print()" class="btn btn-dark rounded-pill px-4 shadow"><i class="bi bi-printer me-2"></i> Print Invoice</button>
    <a href="orders.php" class="btn btn-outline-secondary rounded-pill px-4">Close</a>
</div>

<div class="invoice-card">
    <div class="info-sidebar">
        <div>
            <div class="brand-logo">
                <div style="width: 40px; height: 40px; background: var(--accent); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-scissors"></i>
                </div>
                SIVA
            </div>

            <div class="client-section">
                <span class="label-muted">Customer Details</span>
                <h4 class="fw-bold mb-1"><?php echo $order['name']; ?></h4>
                <p class="small opacity-75 mb-4"><?php echo $order['phone']; ?></p>
                
                <span class="label-muted">Fitting Address</span>
                <p class="small opacity-75"><?php echo $order['address'] ?: 'Walk-in Customer'; ?></p>
            </div>
        </div>

        <div>
            <span class="label-muted">Store Location</span>
            <p class="small mb-0">Elite Tower, Crosscut Road</p>
            <p class="small opacity-50">Coimbatore, TN 641012</p>
        </div>
    </div>

    <div class="billing-main position-relative">
        <div class="big-text">INVOICE</div>
        
        <div class="invoice-header position-relative">
            <div>
                <span class="label-muted">Reference</span>
                <h5 class="fw-bold">#ORD-<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></h5>
            </div>
            <div class="text-end">
                <span class="label-muted">Due Date</span>
                <h5 class="fw-bold"><?php echo date('M d, Y', strtotime($order['delivery_date'])); ?></h5>
            </div>
        </div>

        <table class="table table-premium mt-4">
            <thead>
                <tr>
                    <th>DESCRIPTION</th>
                    <th class="text-center">QTY</th>
                    <th class="text-end">PRICE</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $subtotal = 0;
                foreach($items as $i): 
                    $row_total = $i['quantity'] * $i['price'];
                    $subtotal += $row_total;
                ?>
                <tr>
                    <td>
                        <span class="item-title"><?php echo $i['item_name']; ?></span>
                        <span class="text-muted small">Custom Tailoring & Stitching</span>
                    </td>
                    <td class="text-center fw-bold"><?php echo $i['quantity']; ?></td>
                    <td class="text-end fw-bold">₹<?php echo number_format($row_total, 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="summary-card">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted fw-600">Subtotal</span>
                <span class="fw-bold">₹<?php echo number_format($subtotal, 2); ?></span>
            </div>
            <?php if($order['discount'] > 0): ?>
            <div class="d-flex justify-content-between mb-2 text-danger">
                <span class="fw-600">Seasonal Discount</span>
                <span class="fw-bold">- ₹<?php echo number_format($order['discount'], 2); ?></span>
            </div>
            <?php endif; ?>
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                <div>
                    <span class="label-muted mb-0">Total Payable</span>
                    <div class="grand-total">₹<?php echo number_format($subtotal - $order['discount'], 2); ?></div>
                </div>
                <span class="badge-status"><i class="bi bi-check-all me-1"></i> <?php echo strtoupper($order['status']); ?></span>
            </div>
        </div>

        <div class="mt-5 text-center">
            <p class="text-muted small">Payment secured by <strong>Siva Pay</strong>. <br> Thank you for choosing excellence.</p>
        </div>
    </div>
</div>

</body>
</html>