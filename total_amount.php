<?php 
session_start();
if (!isset($_SESSION['loggedin'])) header('Location: index.php');
include 'config.php';

// --- 1. SECURE TRANSACTIONAL SAVING ---
if (isset($_POST['add_order'])) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO orders (customer_id, order_date, delivery_date, total_amount, discount, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['customer_id'],
            $_POST['order_date'],
            $_POST['delivery_date'],
            $_POST['total_amount'],
            $_POST['discount'] ?? 0,
            $_POST['status']
        ]);

        $order_id = $pdo->lastInsertId();

        if (!empty($_POST['items'])) {
            foreach ($_POST['items'] as $item) {
                if (!empty($item['name'])) {
                    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, item_name, quantity, price) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$order_id, $item['name'], $item['quantity'], $item['price']]);
                }
            }
        }
        
        $pdo->commit();
        header("Location: orders.php?msg=success"); exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Critical Error: " . $e->getMessage());
    }
}

// --- 2. FETCH DATA ---
$customers = $pdo->query("SELECT * FROM customers ORDER BY name ASC")->fetchAll();
$orders = $pdo->query("SELECT o.*, c.name as customer FROM orders o JOIN customers c ON o.customer_id=c.id ORDER BY o.id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders Center | TailorFlow</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --brand: #4f46e5; --bg: #f8fafc; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: #1e293b; }
        .sidebar { width: 260px; height: 100vh; position: fixed; background: #fff; border-right: 1px solid #e2e8f0; padding: 2rem; }
        .main-content { margin-left: 260px; padding: 3rem; }
        .nav-link { display: flex; align-items: center; padding: 0.8rem 1rem; color: #64748b; border-radius: 12px; text-decoration: none; transition: 0.3s; }
        .nav-link.active { background: #eff6ff; color: var(--brand); font-weight: 700; }
        
        .order-card { background: #fff; border-radius: 24px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); }
        .glass-input { background: #f1f5f9 !important; border: 1px solid transparent !important; border-radius: 12px !important; padding: 0.75rem !important; }
        .glass-input:focus { background: #fff !important; border-color: var(--brand) !important; box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1) !important; }
        
        .status-badge { padding: 6px 12px; border-radius: 10px; font-size: 0.75rem; font-weight: 700; }
        .status-Pending { background: #fff7ed; color: #c2410c; }
    </style>
</head>
<body>

<div class="sidebar">
    <h4 class="fw-800 mb-5 text-primary">✂ TailorFlow</h4>
    <nav class="nav flex-column gap-2">
        <a href="home.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        <a href="customers.php" class="nav-link"><i class="bi bi-people me-2"></i> Customers</a>
        <a href="orders.php" class="nav-link active"><i class="bi bi-receipt me-2"></i> Orders</a>
        <hr>
        <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-800">Orders Management</h2>
            <p class="text-muted">Track and process your bespoke tailoring requests.</p>
        </div>
        <button class="btn btn-primary px-4 py-2 rounded-4 fw-bold shadow" data-bs-toggle="collapse" data-bs-target="#newOrder">
            + New Order
        </button>
    </div>

    <div class="collapse mb-5" id="newOrder">
        <div class="order-card p-5">
            <h5 class="fw-800 mb-4">Create Master Order</h5>
            <form method="POST">
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted mb-2">CLIENT</label>
                        <select name="customer_id" class="form-select glass-input" required>
                            <option value="">Choose Client...</option>
                            <?php foreach($customers as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted mb-2">ORDER DATE</label>
                        <input type="date" name="order_date" class="form-control glass-input" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted mb-2">DELIVERY DATE</label>
                        <input type="date" name="delivery_date" class="form-control glass-input" required>
                    </div>

                    <div class="col-12"><hr class="my-4"></div>

                    <div class="col-12">
                        <h6 class="fw-bold mb-3">Line Items</h6>
                        <div id="items-container">
                            <div class="row g-2 mb-2">
                                <div class="col-md-6"><input name="items[0][name]" class="form-control glass-input" placeholder="Item Name" required></div>
                                <div class="col-md-2"><input type="number" name="items[0][quantity]" class="form-control glass-input qty" value="1" oninput="calc()"></div>
                                <div class="col-md-3"><input type="number" name="items[0][price]" class="form-control glass-input price" placeholder="Price" oninput="calc()" required></div>
                                <div class="col-md-1"></div>
                            </div>
                        </div>
                        <button type="button" onclick="addItem()" class="btn btn-sm btn-light rounded-pill mt-2">+ Add Row</button>
                    </div>

                    <div class="col-md-3">
                        <label class="small fw-bold text-muted mb-2">DISCOUNT (₹)</label>
                        <input type="number" name="discount" id="discount" class="form-control glass-input" value="0" oninput="calc()">
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold text-muted mb-2">INITIAL STATUS</label>
                        <select name="status" class="form-select glass-input">
                            <option>Pending</option>
                            <option>In Progress</option>
                        </select>
                    </div>
                    <div class="col-md-6 text-end pt-3">
                        <input type="hidden" name="total_amount" id="total_amount_hidden">
                        <p class="small text-muted mb-0">TOTAL ESTIMATE</p>
                        <h2 class="fw-800 text-primary">₹<span id="grand_total">0</span></h2>
                    </div>

                    <div class="col-12 text-center">
                        <button name="add_order" class="btn btn-primary btn-lg px-5 rounded-pill shadow-lg">Confirm & Save Order</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="order-card p-0 overflow-hidden">
        <div class="p-4 border-bottom">
            <h5 class="fw-800 mb-0">Recent Commissions</h5>
        </div>
        <table class="table mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Customer</th>
                    <th>Deadline</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orders as $o): ?>
                <tr class="align-middle">
                    <td class="ps-4 fw-bold text-muted">#<?php echo str_pad($o['id'], 4, '0', STR_PAD_LEFT); ?></td>
                    <td class="fw-bold"><?php echo htmlspecialchars($o['customer']); ?></td>
                    <td><?php echo date('d M, Y', strtotime($o['delivery_date'])); ?></td>
                    <td class="fw-bold text-primary">₹<?php echo number_format($o['total_amount']); ?></td>
                    <td><span class="status-badge status-<?php echo str_replace(' ', '', $o['status']); ?>"><?php echo $o['status']; ?></span></td>
                    <td class="text-end pe-4">
                        <a href="?delete=<?php echo $o['id']; ?>" class="btn btn-light btn-sm text-danger" onclick="return confirm('Delete order?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>



<script>
let idx = 1;
function addItem() {
    const container = document.getElementById('items-container');
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 animate__animated animate__fadeIn';
    row.innerHTML = `<div class="col-md-6"><input name="items[${idx}][name]" class="form-control glass-input" placeholder="Item Name" required></div><div class="col-md-2"><input type="number" name="items[${idx}][quantity]" class="form-control glass-input qty" value="1" oninput="calc()"></div><div class="col-md-3"><input type="number" name="items[${idx}][price]" class="form-control glass-input price" placeholder="Price" oninput="calc()" required></div><div class="col-md-1"><button type="button" onclick="this.parentElement.parentElement.remove(); calc();" class="btn text-danger">×</button></div>`;
    container.appendChild(row); idx++;
}

function calc() {
    let total = 0;
    const qtys = document.querySelectorAll('.qty');
    const prices = document.querySelectorAll('.price');
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    qtys.forEach((q, i) => { total += (q.value * prices[i].value); });
    const final = total - discount;
    document.getElementById('grand_total').innerText = final.toLocaleString();
    document.getElementById('total_amount_hidden').value = final;
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>