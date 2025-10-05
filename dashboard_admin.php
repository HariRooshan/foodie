<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "foodie");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = "";
$error = "";

// Update vendor
if (isset($_POST['update_vendor'])) {
    $vendor_id = intval($_POST['vendor_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE vendors SET name=?, email=?, phone=?, address=?, is_active=? WHERE vendor_id=?");
    $stmt->bind_param("ssssii", $name, $email, $phone, $address, $is_active, $vendor_id);
    if ($stmt->execute()) {
        $success = "Vendor updated successfully.";
    } else {
        $error = "Error updating vendor: " . $conn->error;
    }
    $stmt->close();
}

// Delete vendor
if (isset($_POST['delete_vendor'])) {
    $vendor_id = intval($_POST['vendor_id']);
    $stmt = $conn->prepare("DELETE FROM vendors WHERE vendor_id=?");
    $stmt->bind_param("i", $vendor_id);
    if ($stmt->execute()) {
        $success = "Vendor deleted successfully.";
    } else {
        $error = "Error deleting vendor: " . $conn->error;
    }
    $stmt->close();
}

// Fetch all vendors
$vendors = $conn->query("SELECT * FROM vendors ORDER BY created_at DESC");

// Fetch vendor foods if a vendor is selected
$vendorFoods = null;
if (isset($_GET['vendor_id'])) {
    $vendor_id = intval($_GET['vendor_id']);
    $stmt = $conn->prepare("SELECT * FROM foods WHERE vendor_id=?");
    $stmt->bind_param("i", $vendor_id);
    $stmt->execute();
    $vendorFoods = $stmt->get_result();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Vendors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Make tables more readable and less cramped */
        .table-custom thead th {
            vertical-align: middle;
            background: #343a40;
            color: #fff;
        }
        .table-custom tbody td {
            padding: .45rem .6rem;
            vertical-align: middle;
        }
        /* make inline form controls smaller to fit in table */
        .table-custom .form-control-sm {
            padding: .25rem .4rem;
            font-size: .85rem;
            height: auto;
        }
        .action-btns .btn {
            margin-right: .25rem;
        }
        /* responsive tweaks */
        @media (max-width: 767px) {
            .table-custom thead { display: none; }
            .table-custom tbody td { display: block; width: 100%; }
            .table-custom tbody tr { margin-bottom: 1rem; display: block; border: 1px solid #e9ecef; border-radius: 6px; }
            .table-custom tbody td:before { content: attr(data-label); font-weight: 600; display: inline-block; width: 110px; }
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <!-- Brand on the left -->
        <a class="navbar-brand fw-bold" href="#">Admin - Cravings Site</a>

        <!-- Buttons on the right -->
        <div class="d-flex ms-auto">
            <a href="index.php" class="btn btn-success me-2">View Page</a>
            <a href="logout_admin.php" class="btn btn-primary">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5 dashboard-inner">
    <h2 class="mb-4 text-center">Admin Dashboard - Vendors</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="mb-3 text-end">
        <a href="add_vendor.php" class="btn btn-success">+ Add Vendor</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="card-title mb-3">All Vendors</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle table-custom">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Active</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($vendors->num_rows > 0): ?>
                        <?php while ($v = $vendors->fetch_assoc()): ?>
                            <tr>
                                <form method="POST">
                                    <input type="hidden" name="vendor_id" value="<?= $v['vendor_id'] ?>">
                                    <td data-label="ID"><?= $v['vendor_id'] ?></td>
                                    <td data-label="Name"><input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($v['name']) ?>"></td>
                                    <td data-label="Email"><input type="email" name="email" class="form-control form-control-sm" value="<?= htmlspecialchars($v['email']) ?>"></td>
                                    <td data-label="Phone"><input type="text" name="phone" class="form-control form-control-sm" value="<?= htmlspecialchars($v['phone']) ?>"></td>
                                    <td data-label="Address"><input type="text" name="address" class="form-control form-control-sm" value="<?= htmlspecialchars($v['address']) ?>"></td>
                                    <td class="text-center" data-label="Active"><input type="checkbox" name="is_active" <?= $v['is_active'] ? 'checked' : '' ?>></td>
                                    <td data-label="Created At"><?= $v['created_at'] ?></td>
                                    <td data-label="Actions">
                                        <div class="d-flex justify-content-center">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item action-update" href="#">Update</a></li>
                                                    <li><a class="dropdown-item action-delete" href="#">Delete</a></li>
                                                    <li><a class="dropdown-item" href="dashboard_admin.php?vendor_id=<?= $v['vendor_id'] ?>">View Foods</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </form>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center">No vendors found</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($vendorFoods): ?>
        <div class="card shadow-sm vendor-foods-card">
            <div class="card-body">
                <h4 class="card-title">Foods Sold by Vendor #<?= $vendor_id ?></h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-info">
                            <tr>
                                <th>Food ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Price (₹)</th>
                                <th>Quantity</th>
                                <th>Dietary</th>
                                <th>Available</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($vendorFoods->num_rows > 0): ?>
                            <?php while ($food = $vendorFoods->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $food['food_id'] ?></td>
                                    <td><?= htmlspecialchars($food['name']) ?></td>
                                    <td><?= htmlspecialchars($food['description']) ?></td>
                                    <td><?= number_format($food['price'], 2) ?></td>
                                    <td><?= $food['quantity'] ?></td>
                                    <td><?= ucfirst($food['dietary']) ?></td>
                                    <td><?= $food['available'] ? 'Yes' : 'No' ?></td>
                                    <td><?= $food['created_at'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center">No foods found for this vendor</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

    <style>
        /* widen content a bit and add bottom spacing */
        .dashboard-inner { max-width: 1200px; padding-bottom: 3rem; }
        /* give vendor foods card extra bottom space so it doesn't clash with page bottom */
        .vendor-foods-card { margin-bottom: 4rem; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Delegate clicks from dropdown to the row form
    document.addEventListener('click', function(e){
        if (e.target.matches('.action-update')) {
            e.preventDefault();
            var row = e.target.closest('tr');
            var form = row.querySelector('form');
            // create hidden input to trigger update
            var inp = document.createElement('input'); inp.type='hidden'; inp.name='update_vendor'; inp.value='1';
            form.appendChild(inp);
            form.submit();
        }
        if (e.target.matches('.action-delete')) {
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this vendor?')) return;
            var row = e.target.closest('tr');
            var form = row.querySelector('form');
            var inp = document.createElement('input'); inp.type='hidden'; inp.name='delete_vendor'; inp.value='1';
            form.appendChild(inp);
            form.submit();
        }
    });
    </script>

</body>
</html>

<?php $conn->close(); ?>
