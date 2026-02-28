<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload_helper.php';
requireAdmin();

$pdo = getDb();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $id = (int) $_POST['id'];
    if ($id > 0) {
        if ($_POST['action'] === 'delete') {
            $row = $pdo->prepare('SELECT image_path FROM partners WHERE id = ?');
            $row->execute([$id]);
            $row = $row->fetch();
            if ($row && !empty($row['image_path'])) {
                upload_delete_image($row['image_path']);
            }
            $pdo->prepare('DELETE FROM partners WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = 'Partner deleted.';
        } elseif ($_POST['action'] === 'publish') {
            $pdo->prepare('UPDATE partners SET is_published = 1 WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = 'Partner published.';
        } elseif ($_POST['action'] === 'unpublish') {
            $pdo->prepare('UPDATE partners SET is_published = 0 WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = 'Partner unpublished.';
        }
    }
    header('Location: partners.php');
    exit;
}

$rows = $pdo->query('SELECT * FROM partners ORDER BY sort_order ASC, id ASC')->fetchAll();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Partners - Travisa Admin</title>
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
                <a href="partners.php" class="active"><i class="bi bi-people me-2"></i>Partners</a>
                <a href="geographies.php"><i class="bi bi-geo-alt me-2"></i>Geographies</a>
                <a href="contacts.php"><i class="bi bi-envelope me-2"></i>Contact Us</a>
                <hr class="border-secondary">
                <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </nav>
            <main class="col-md-10 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="mb-0">Partners</h1>
                    <a href="partner-edit.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Partner</a>
                </div>
                <?php if ($flash): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
                <?php endif; ?>
                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $r): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($r['image_path'])): ?>
                                                <img src="../<?= htmlspecialchars($r['image_path']) ?>" alt="" class="img-thumbnail" style="max-height: 50px;">
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($r['title']) ?></td>
                                        <td><span class="text-muted"><?= htmlspecialchars(mb_substr($r['description'] ?? '', 0, 50)) ?><?= mb_strlen($r['description'] ?? '') > 50 ? '...' : '' ?></span></td>
                                        <td><?= (int) $r['sort_order'] ?></td>
                                        <td>
                                            <?php if ($r['is_published']): ?>
                                                <span class="badge bg-success">Published</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Unpublished</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="partner-edit.php?id=<?= (int) $r['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this partner?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                            <?php if ($r['is_published']): ?>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="unpublish">
                                                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-warning">Unpublish</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="publish">
                                                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-success">Publish</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($rows)): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">No partners yet. <a href="partner-edit.php">Add one</a>.</td></tr>
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
