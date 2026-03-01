<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pdo = getDb();
const PER_PAGE = 10;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = array_filter(array_map('intval', (array) ($_POST['ids'] ?? [])));
    $action = $_POST['action'] ?? '';
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if ($id > 0 && in_array($action, ['delete', 'activate', 'deactivate'], true)) {
        if ($action === 'delete') {
            $pdo->prepare('DELETE FROM geographies WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = 'Geography deleted.';
        } elseif ($action === 'activate') {
            $pdo->prepare('UPDATE geographies SET is_active = 1 WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = 'Geography activated.';
        } else {
            $pdo->prepare('UPDATE geographies SET is_active = 0 WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = 'Geography deactivated.';
        }
        header('Location: geographies?page=' . max(1, (int) ($_GET['page'] ?? 1)));
        exit;
    }

    if (!empty($ids) && in_array($action, ['bulk_delete', 'bulk_activate', 'bulk_deactivate'], true)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        if ($action === 'bulk_delete') {
            $pdo->prepare("DELETE FROM geographies WHERE id IN ($placeholders)")->execute($ids);
            $_SESSION['flash'] = count($ids) . ' geography(ies) deleted.';
        } elseif ($action === 'bulk_activate') {
            $pdo->prepare("UPDATE geographies SET is_active = 1 WHERE id IN ($placeholders)")->execute($ids);
            $_SESSION['flash'] = count($ids) . ' geography(ies) activated.';
        } else {
            $pdo->prepare("UPDATE geographies SET is_active = 0 WHERE id IN ($placeholders)")->execute($ids);
            $_SESSION['flash'] = count($ids) . ' geography(ies) deactivated.';
        }
        header('Location: geographies?page=' . max(1, (int) ($_GET['page'] ?? 1)));
        exit;
    }
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$total = (int) $pdo->query('SELECT COUNT(*) FROM geographies')->fetchColumn();
$totalPages = max(1, (int) ceil($total / PER_PAGE));
$page = min($page, $totalPages);
$offset = ($page - 1) * PER_PAGE;

$rows = $pdo->prepare('SELECT * FROM geographies ORDER BY sort_order ASC, name ASC LIMIT ' . PER_PAGE . ' OFFSET ' . $offset);
$rows->execute();
$rows = $rows->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$coverageLabels = ['telecalling' => 'Telecalling', 'field' => 'Field', 'both' => 'Both'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Geographies - Swiis Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .sidebar { min-height: 100vh; background: #1a1a2e; }
        .sidebar a { color: rgba(255,255,255,.8); text-decoration: none; padding: 12px 16px; display: block; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background: rgba(255,255,255,.1); }
        .btn-icon { padding: 0.25rem 0.5rem; }
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="mb-0">Geographies</h1>
                    <a href="geography-edit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Geography</a>
                </div>
                <p class="text-muted">Regions we cater to (e.g. Punjab, HP, Rajasthan, Haryana). Shown on the Office page with India map — Telecalling and Field wise.</p>
                <?php if ($flash): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
                <?php endif; ?>
                <form method="post" id="bulk-form">
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <span class="text-muted small">With selected:</span>
                        <button type="submit" name="action" value="bulk_activate" class="btn btn-sm btn-success"><i class="bi bi-check-circle me-1"></i>Activate</button>
                        <button type="submit" name="action" value="bulk_deactivate" class="btn btn-sm btn-warning"><i class="bi bi-x-circle me-1"></i>Deactivate</button>
                        <button type="submit" name="action" value="bulk_delete" class="btn btn-sm btn-danger" onclick="return confirm('Delete selected geographies?');"><i class="bi bi-trash me-1"></i>Delete</button>
                    </div>
                    <div class="card shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40"><input type="checkbox" class="form-check-input" id="select-all" title="Select all"></th>
                                        <th>Name</th>
                                        <th>State code (map)</th>
                                        <th>Coverage</th>
                                        <th>Order</th>
                                        <th>Status</th>
                                        <th width="120">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $r): ?>
                                        <tr>
                                            <td><input type="checkbox" name="ids[]" value="<?= (int) $r['id'] ?>" class="form-check-input row-cb"></td>
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
                                                <a href="geography-edit?id=<?= (int) $r['id'] ?>" class="btn btn-sm btn-outline-primary btn-icon" title="Edit"><i class="bi bi-pencil"></i></a>
                                                <form method="post" class="d-inline" onsubmit="return confirm('Delete this geography?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" title="Delete"><i class="bi bi-trash"></i></button>
                                                </form>
                                                <?php if ($r['is_active']): ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="deactivate">
                                                        <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-warning btn-icon" title="Deactivate"><i class="bi bi-x-circle"></i></button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="activate">
                                                        <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-success btn-icon" title="Activate"><i class="bi bi-check-circle"></i></button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($rows)): ?>
                                        <tr><td colspan="7" class="text-center text-muted py-4">No geographies yet. <a href="geography-edit">Add one</a> (e.g. Punjab, HP, Rajasthan, Haryana).</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if ($totalPages > 1): ?>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <small class="text-muted"><?= $total ?> total</small>
                            <nav>
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>">Prev</a>
                                    </li>
                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>
                    </div>
                </form>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('select-all')?.addEventListener('change', function() {
        document.querySelectorAll('.row-cb').forEach(function(cb) { cb.checked = this.checked; }, this);
    });
    </script>
</body>
</html>
