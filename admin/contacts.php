<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pdo = getDb();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $id = (int) $_POST['id'];
    if ($id > 0) {
        if ($_POST['action'] === 'delete') {
            $pdo->prepare('DELETE FROM contactUs WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = 'Contact message deleted.';
        } elseif ($_POST['action'] === 'read') {
            $pdo->prepare('UPDATE contactUs SET is_read = 1 WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = 'Marked as read.';
        } elseif ($_POST['action'] === 'unread') {
            $pdo->prepare('UPDATE contactUs SET is_read = 0 WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = 'Marked as unread.';
        }
    }
    header('Location: contacts.php');
    exit;
}

$rows = $pdo->query('SELECT * FROM contactUs ORDER BY created_at DESC')->fetchAll();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Contact Us - Travisa Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .sidebar { min-height: 100vh; background: #1a1a2e; }
        .sidebar a { color: rgba(255,255,255,.8); text-decoration: none; padding: 12px 16px; display: block; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background: rgba(255,255,255,.1); }
        .message-cell { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
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
                <a href="geographies.php"><i class="bi bi-geo-alt me-2"></i>Geographies</a>
                <a href="contacts.php" class="active"><i class="bi bi-envelope me-2"></i>Contact Us</a>
                <hr class="border-secondary">
                <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </nav>
            <main class="col-md-10 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="mb-0">Contact Us Submissions</h1>
                </div>
                <?php if ($flash): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
                <?php endif; ?>
                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Project</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Read</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $r): ?>
                                    <tr class="<?= $r['is_read'] ? '' : 'table-warning' ?>">
                                        <td><?= htmlspecialchars(date('M j, Y H:i', strtotime($r['created_at']))) ?></td>
                                        <td><?= htmlspecialchars($r['name']) ?></td>
                                        <td><a href="mailto:<?= htmlspecialchars($r['email']) ?>"><?= htmlspecialchars($r['email']) ?></a></td>
                                        <td><?= htmlspecialchars($r['phone'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($r['project'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($r['subject'] ?? '-') ?></td>
                                        <td class="message-cell" title="<?= htmlspecialchars($r['message']) ?>"><?= htmlspecialchars(mb_substr($r['message'], 0, 50)) ?><?= mb_strlen($r['message']) > 50 ? '...' : '' ?></td>
                                        <td>
                                            <?php if ($r['is_read']): ?>
                                                <span class="badge bg-secondary">Read</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary">New</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#msgModal<?= (int) $r['id'] ?>">View</button>
                                            <?php if ($r['is_read']): ?>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="unread">
                                                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-warning">Unread</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="read">
                                                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-success">Mark read</button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this message?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <!-- Modal for full message -->
                                    <div class="modal fade" id="msgModal<?= (int) $r['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Message from <?= htmlspecialchars($r['name']) ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Date:</strong> <?= htmlspecialchars($r['created_at']) ?></p>
                                                    <p><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($r['email']) ?>"><?= htmlspecialchars($r['email']) ?></a></p>
                                                    <p><strong>Phone:</strong> <?= htmlspecialchars($r['phone'] ?? '-') ?></p>
                                                    <p><strong>Project:</strong> <?= htmlspecialchars($r['project'] ?? '-') ?></p>
                                                    <p><strong>Subject:</strong> <?= htmlspecialchars($r['subject'] ?? '-') ?></p>
                                                    <hr>
                                                    <p class="mb-0"><?= nl2br(htmlspecialchars($r['message'])) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($rows)): ?>
                                    <tr><td colspan="9" class="text-center text-muted py-4">No contact submissions yet.</td></tr>
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
