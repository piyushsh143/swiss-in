<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pdo = getDb();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM geographies WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) {
        header('Location: geographies');
        exit;
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $state_code = trim($_POST['state_code'] ?? '') ?: null;
    $coverage_type = $_POST['coverage_type'] ?? 'both';
    if (!in_array($coverage_type, ['telecalling', 'field', 'both'], true)) $coverage_type = 'both';
    $sort_order = isset($_POST['sort_order']) ? (int) $_POST['sort_order'] : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($name === '') $errors[] = 'Name is required.';

    if (empty($errors)) {
        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE geographies SET name=?, state_code=?, coverage_type=?, sort_order=?, is_active=? WHERE id=?');
            $stmt->execute([$name, $state_code, $coverage_type, $sort_order, $is_active, $id]);
            $_SESSION['flash'] = 'Geography updated.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO geographies (name, state_code, coverage_type, sort_order, is_active) VALUES (?,?,?,?,?)');
            $stmt->execute([$name, $state_code, $coverage_type, $sort_order, $is_active]);
            $_SESSION['flash'] = 'Geography added.';
        }
        header('Location: geographies');
        exit;
    }
    $item = array_merge($item ?: [], [
        'name' => $name,
        'state_code' => $state_code,
        'coverage_type' => $coverage_type,
        'sort_order' => $sort_order,
        'is_active' => $is_active,
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $id ? 'Edit' : 'Add' ?> Geography - Swiis Admin</title>
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
                <a href="geographies" class="active"><i class="bi bi-geo-alt me-2"></i>Geographies</a>
                <a href="contacts"><i class="bi bi-envelope me-2"></i>Contact Us</a>
                <a href="site_settings"><i class="bi bi-gear me-2"></i>Site Settings</a>
                <hr class="border-secondary">
                <a href="logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </nav>
            <main class="col-md-10 py-4">
                <h1 class="mb-4"><?= $id ? 'Edit' : 'Add' ?> Geography</h1>
                <?php foreach ($errors as $e): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="post">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($item['name'] ?? '') ?>" placeholder="e.g. Punjab, Himachal Pradesh, Rajasthan, Haryana" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="state_code" class="form-label">State code (for map)</label>
                                    <input type="text" class="form-control" id="state_code" name="state_code" value="<?= htmlspecialchars($item['state_code'] ?? '') ?>" placeholder="e.g. IN-PB, IN-HP, IN-RJ, IN-HR">
                                    <small class="text-muted">ISO 3166-2 state code used to highlight on India map.</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="coverage_type" class="form-label">Coverage type</label>
                                    <select class="form-select" id="coverage_type" name="coverage_type">
                                        <option value="telecalling" <?= ($item['coverage_type'] ?? '') === 'telecalling' ? 'selected' : '' ?>>Telecalling</option>
                                        <option value="field" <?= ($item['coverage_type'] ?? '') === 'field' ? 'selected' : '' ?>>Field</option>
                                        <option value="both" <?= ($item['coverage_type'] ?? 'both') === 'both' ? 'selected' : '' ?>>Both</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="sort_order" class="form-label">Sort order</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order" value="<?= (int) ($item['sort_order'] ?? 0) ?>">
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" <?= ($item['is_active'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active">Active (show on site)</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Geography</button>
                            <a href="geographies" class="btn btn-outline-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
