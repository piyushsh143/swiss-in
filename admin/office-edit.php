<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pdo = getDb();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM office_addresses WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) {
        header('Location: site_settings');
        exit;
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $sort_order = isset($_POST['sort_order']) ? (int) $_POST['sort_order'] : 0;
    if ($title === '') $errors[] = 'Title is required.';
    if ($address === '') $errors[] = 'Address is required.';
    if (empty($errors)) {
        if ($id > 0) {
            $pdo->prepare('UPDATE office_addresses SET title=?, address=?, sort_order=? WHERE id=?')->execute([$title, $address, $sort_order, $id]);
            $_SESSION['flash'] = 'Office address updated.';
        } else {
            $pdo->prepare('INSERT INTO office_addresses (title, address, sort_order) VALUES (?,?,?)')->execute([$title, $address, $sort_order]);
            $_SESSION['flash'] = 'Office address added.';
        }
        header('Location: site_settings');
        exit;
    }
    $item = $item ?: [];
    $item['title'] = $title;
    $item['address'] = $address;
    $item['sort_order'] = $sort_order;
}
$item = $item ?: ['title' => '', 'address' => '', 'sort_order' => 0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $id ? 'Edit' : 'Add' ?> Office Address - Swiis Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .sidebar { min-height: 100vh; background: #1a1a2e; }
        .sidebar a { color: rgba(255,255,255,.8); text-decoration: none; padding: 12px 16px; display: block; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background: rgba(255,255,255,.1); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 sidebar">
                <div class="py-3 px-3 text-white fw-bold">Swiis Admin</div>
                <a href="dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a href="testimonials"><i class="bi bi-chat-quote me-2"></i>Testimonials</a>
                <a href="blogs"><i class="bi bi-journal-text me-2"></i>Blogs</a>
                <a href="partners"><i class="bi bi-people me-2"></i>Clients</a>
                <a href="geographies"><i class="bi bi-geo-alt me-2"></i>Geographies</a>
                <a href="contacts"><i class="bi bi-envelope me-2"></i>Contact Us</a>
                <a href="site_settings" class="active"><i class="bi bi-gear me-2"></i>Site Settings</a>
                <hr class="border-secondary">
                <a href="logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </nav>
            <main class="col-md-10 py-4">
                <h1 class="mb-4"><?= $id ? 'Edit' : 'Add' ?> Office Address</h1>
                <?php foreach ($errors as $e): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title *</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($item['title']) ?>" placeholder="e.g. Head Office, Branch Office" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address *</label>
                                <textarea class="form-control" id="address" name="address" rows="4" required><?= htmlspecialchars($item['address']) ?></textarea>
                                <small class="text-muted">Full address; line breaks will be shown on the site.</small>
                            </div>
                            <div class="mb-3">
                                <label for="sort_order" class="form-label">Sort order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" value="<?= (int) $item['sort_order'] ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="site_settings" class="btn btn-outline-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
