<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pdo = getDb();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $id = (int) $_POST['id'];
    if ($id > 0) {
        if ($_POST['action'] === 'delete') {
            $pdo->prepare('DELETE FROM geographies WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = 'Geography deleted.';
        } elseif ($_POST['action'] === 'activate') {
            $pdo->prepare('UPDATE geographies SET is_active = 1 WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = 'Geography activated.';
        } elseif ($_POST['action'] === 'deactivate') {
            $pdo->prepare('UPDATE geographies SET is_active = 0 WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = 'Geography deactivated.';
        }
    }
    header('Location: geographies.php');
    exit;
}

$rows = $pdo->query('SELECT * FROM geographies ORDER BY sort_order ASC, name ASC')->fetchAll();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$coverageLabels = ['telecalling' => 'Telecalling', 'field' => 'Field', 'both' => 'Both'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Geographies - Travisa Admin</title>
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
                <div class="py-3 px-3 text-white fw-bold">Travisa Admin</div>
                <a href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a href="testimonials.php"><i class="bi bi-chat-quote me-2"></i>Testimonials</a>
                <a href="blogs.php"><i class="bi bi-journal-text me-2"></i>Blogs</a>
                <a href="partners.php"><i class="bi bi-people me-2"></i>Partners</a>
                <a href="geographies.php" class="active"><i class="bi bi-geo-alt me-2"></i>Geographies</a>
                <a href="contacts.php"><i class="bi bi-envelope me-2"></i>Contact Us</a>
                <hr class="border-secondary">
                <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </nav>
            <main class="col-md-10 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="mb-0">Geographies</h1>
                    <a href="geography-edit.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Geography</a>
                </div>
                <p class="text-muted">Regions we cater to (e.g. Punjab, HP, Rajasthan, Haryana). Shown on the Office page with India map — Telecalling and Field wise.</p>
                <?php if ($flash): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
                <?php endif; ?>
                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>State code (map)</th>
                                    <th>Coverage</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $r): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['name']) ?></td>
                                        <td><code><?= htmlspecialchars($r['state_code'] ?? '—') ?></code></td>
                                        <td><span class="badge bg-info"><?= $coverageLabels[$r['coverage_type']] ?? $r['coverage_type'] ?></span></td>
                                        <td><?= (int) $r['sort_order'] ?></td>
                                        <td>
                                            <?php if ($r['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="geography-edit.php?id=<?= (int) $r['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this geography?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                            <?php if ($r['is_active']): ?>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="deactivate">
                                                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-warning">Deactivate</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="activate">
                                                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-success">Activate</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($rows)): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">No geographies yet. <a href="geography-edit.php">Add one</a> (e.g. Punjab, HP, Rajasthan, Haryana).</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
