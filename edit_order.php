<?php
session_start();
include 'config.php';

// Check if 'id' exists in the URL
if (isset($_GET['id'])) {
    $order_id = (int)$_GET['id']; // This defines the variable causing the error
} else {
    // If no ID is provided, redirect back or show an error
    die("Error: No Order ID specified.");
}

// Rest of your logic (fetching the order from the database)
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Order | TailorFlow</title>

    <!-- Fonts & Bootstrap -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            padding: 40px;
        }

        .card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 40px rgba(0,0,0,0.05);
        }

        .form-label {
            font-weight: 600;
            font-size: 0.8rem;
            color: #64748b;
            text-transform: uppercase;
        }

        .btn-save {
            background: #3b82f6;
            color: white;
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
            width: 100%;
            border: none;
        }

        .btn-save:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>

<div class="container" style="max-width: 600px;">

    <a href="orders.php" class="btn btn-sm btn-light mb-4">← Back to Orders</a>

    <div class="card p-5">
        <h2 class="fw-bold mb-1">Edit Order</h2>
        <p class="text-muted mb-4">Order ID: <?php echo $order_id; ?></p>

        <?php if(isset($message)) echo "<div class='alert alert-success'>$message</div>"; ?>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

        <form method="POST">

            <div class="mb-4">
                <label class="form-label">Total Amount (₹)</label>
                <input 
                    type="number" 
                    step="0.01" 
                    name="total_amount" 
                    class="form-control form-control-lg" 
                    value="<?php echo $order['total_amount']; ?>" 
                    required
                >
            </div>

            <button name="update" class="btn btn-save">
                Update Order
            </button>

        </form>
    </div>

</div>

</body>
</html>