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

    if ($id > 0 && in_array($action, ['delete', 'read', 'unread'], true)) {
        if ($action === 'delete') {
            $pdo->prepare('DELETE FROM contactUs WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = 'Contact message deleted.';
        } elseif ($action === 'read') {
            $pdo->prepare('UPDATE contactUs SET is_read = 1 WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = 'Marked as read.';
        } else {
            $pdo->prepare('UPDATE contactUs SET is_read = 0 WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = 'Marked as unread.';
        }
        header('Location: contacts?' . http_build_query(array_filter(['page' => max(1, (int) ($_GET['page'] ?? 1)), 'q' => $_GET['q'] ?? null])));
        exit;
    }

    if (!empty($ids) && in_array($action, ['bulk_delete', 'bulk_read', 'bulk_unread'], true)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        if ($action === 'bulk_delete') {
            $pdo->prepare("DELETE FROM contactUs WHERE id IN ($placeholders)")->execute($ids);
            $_SESSION['flash'] = count($ids) . ' message(s) deleted.';
        } elseif ($action === 'bulk_read') {
            $pdo->prepare("UPDATE contactUs SET is_read = 1 WHERE id IN ($placeholders)")->execute($ids);
            $_SESSION['flash'] = count($ids) . ' message(s) marked read.';
        } else {
            $pdo->prepare("UPDATE contactUs SET is_read = 0 WHERE id IN ($placeholders)")->execute($ids);
            $_SESSION['flash'] = count($ids) . ' message(s) marked unread.';
        }
        header('Location: contacts?' . http_build_query(array_filter(['page' => max(1, (int) ($_GET['page'] ?? 1)), 'q' => $_GET['q'] ?? null])));
        exit;
    }
}

$q = trim((string) ($_GET['q'] ?? ''));
$where = '';
$params = [];
if ($q !== '') {
    $where = ' WHERE (name LIKE ? OR email LIKE ? OR phone LIKE ? OR project LIKE ? OR subject LIKE ? OR message LIKE ?)';
    $term = '%' . $q . '%';
    $params = [$term, $term, $term, $term, $term, $term];
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$countSql = 'SELECT COUNT(*) FROM contactUs' . $where;
if ($params) {
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int) $stmt->fetchColumn();
} else {
    $total = (int) $pdo->query($countSql)->fetchColumn();
}
$totalPages = max(1, (int) ceil($total / PER_PAGE));
$page = min($page, $totalPages);
$offset = ($page - 1) * PER_PAGE;

$sql = 'SELECT * FROM contactUs' . $where . ' ORDER BY created_at DESC LIMIT ' . (int) PER_PAGE . ' OFFSET ' . (int) $offset;
if ($params) {
    $rows = $pdo->prepare($sql);
    $rows->execute($params);
} else {
    $rows = $pdo->query($sql);
}
$rows = $rows->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Contact Us - Swiis Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .sidebar { min-height: 100vh; background: #1a1a2e; }
        .sidebar a { color: rgba(255,255,255,.8); text-decoration: none; padding: 12px 16px; display: block; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background: rgba(255,255,255,.1); }
        .message-cell { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .btn-icon { padding: 0.25rem 0.5rem; }
        /* Modern message modal */
        .modal-msg .modal-dialog { max-width: 560px; }
        .modal-msg .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,.25); }
        .modal-msg .modal-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff; border: none; padding: 1.25rem 1.5rem;
            align-items: center;
        }
        .modal-msg .modal-title {
            font-weight: 600; font-size: 1.1rem; letter-spacing: -0.02em;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .modal-msg .modal-title .msg-avatar {
            width: 40px; height: 40px; border-radius: 12px; background: rgba(255,255,255,.2);
            display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 700;
        }
        .modal-msg .btn-close { filter: invert(1); opacity: .8; }
        .modal-msg .btn-close:hover { opacity: 1; }
        .modal-msg .modal-body { padding: 1.5rem; background: #fafbfc; }
        .modal-msg .msg-meta { display: grid; gap: 0.75rem; margin-bottom: 1.25rem; }
        .modal-msg .msg-meta-item {
            display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.6rem 0.9rem;
            background: #fff; border-radius: 10px; border: 1px solid #e8ecf0; font-size: 0.9rem;
        }
        .modal-msg .msg-meta-item i { color: #6366f1; margin-top: 2px; font-size: 1rem; flex-shrink: 0; }
        .modal-msg .msg-meta-item .label { color: #64748b; font-weight: 500; min-width: 70px; }
        .modal-msg .msg-meta-item .value { color: #1e293b; }
        .modal-msg .msg-meta-item a.value { color: #6366f1; text-decoration: none; }
        .modal-msg .msg-meta-item a.value:hover { text-decoration: underline; }
        .modal-msg .msg-body-wrap {
            background: #fff; border-radius: 12px; border: 1px solid #e8ecf0;
            padding: 1.1rem 1.25rem; margin-top: 0.5rem;
        }
        .modal-msg .msg-body-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 600; margin-bottom: 0.5rem; }
        .modal-msg .msg-body-text { color: #334155; line-height: 1.6; margin: 0; white-space: pre-wrap; }
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
                <a href="contacts" class="active"><i class="bi bi-envelope me-2"></i>Contact Us</a>
                <a href="site_settings"><i class="bi bi-gear me-2"></i>Site Settings</a>
                <hr class="border-secondary">
                <a href="logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </nav>
            <main class="col-md-10 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="mb-0">Contact Us Submissions</h1>
                </div>
                <?php if ($flash): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
                <?php endif; ?>
                <form method="get" class="mb-3" action="contacts">
                    <div class="input-group" style="max-width: 320px;">
                        <input type="search" name="q" class="form-control" placeholder="Search name, email, subject, message…" value="<?= htmlspecialchars($q) ?>">
                        <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
                        <?php if ($q !== ''): ?><a href="contacts" class="btn btn-outline-secondary">Clear</a><?php endif; ?>
                    </div>
                </form>
                <form method="post" id="bulk-form">
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <span class="text-muted small">With selected:</span>
                        <button type="submit" name="action" value="bulk_read" class="btn btn-sm btn-success"><i class="bi bi-envelope-open me-1"></i>Mark read</button>
                        <button type="submit" name="action" value="bulk_unread" class="btn btn-sm btn-warning"><i class="bi bi-envelope me-1"></i>Mark unread</button>
                        <button type="submit" name="action" value="bulk_delete" class="btn btn-sm btn-danger" onclick="return confirm('Delete selected messages?');"><i class="bi bi-trash me-1"></i>Delete</button>
                    </div>
                    <div class="card shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40"><input type="checkbox" class="form-check-input" id="select-all" title="Select all"></th>
                                        <th>Date</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Project</th>
                                        <th>Subject</th>
                                        <th>Message</th>
                                        <th>Read</th>
                                        <th width="140">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $r): ?>
                                        <tr class="<?= $r['is_read'] ? '' : 'table-warning' ?>">
                                            <td><input type="checkbox" name="ids[]" value="<?= (int) $r['id'] ?>" class="form-check-input row-cb"></td>
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
                                                <button type="button" class="btn btn-sm btn-outline-info btn-icon" data-bs-toggle="modal" data-bs-target="#msgModal<?= (int) $r['id'] ?>" title="View"><i class="bi bi-eye"></i></button>
                                                <?php if ($r['is_read']): ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="unread">
                                                        <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-warning btn-icon" title="Mark unread"><i class="bi bi-envelope"></i></button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="read">
                                                        <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-success btn-icon" title="Mark read"><i class="bi bi-envelope-open"></i></button>
                                                    </form>
                                                <?php endif; ?>
                                                <form method="post" class="d-inline" onsubmit="return confirm('Delete this message?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" title="Delete"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($rows)): ?>
                                        <tr><td colspan="10" class="text-center text-muted py-4">No contact submissions yet.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php foreach ($rows as $r):
                            $initial = mb_strtoupper(mb_substr(trim($r['name']), 0, 1)) ?: '?';
                        ?>
                        <div class="modal fade modal-msg" id="msgModal<?= (int) $r['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <span class="msg-avatar"><?= htmlspecialchars($initial) ?></span>
                                            Message from <?= htmlspecialchars($r['name']) ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="msg-meta">
                                            <div class="msg-meta-item">
                                                <i class="bi bi-calendar3"></i>
                                                <span class="label">Date</span>
                                                <span class="value"><?= htmlspecialchars(date('M j, Y \a\t g:i A', strtotime($r['created_at']))) ?></span>
                                            </div>
                                            <div class="msg-meta-item">
                                                <i class="bi bi-envelope"></i>
                                                <span class="label">Email</span>
                                                <a href="mailto:<?= htmlspecialchars($r['email']) ?>" class="value"><?= htmlspecialchars($r['email']) ?></a>
                                            </div>
                                            <div class="msg-meta-item">
                                                <i class="bi bi-telephone"></i>
                                                <span class="label">Phone</span>
                                                <span class="value"><?= htmlspecialchars($r['phone'] ?? '—') ?></span>
                                            </div>
                                            <div class="msg-meta-item">
                                                <i class="bi bi-briefcase"></i>
                                                <span class="label">Project</span>
                                                <span class="value"><?= htmlspecialchars($r['project'] ?? '—') ?></span>
                                            </div>
                                            <div class="msg-meta-item">
                                                <i class="bi bi-tag"></i>
                                                <span class="label">Subject</span>
                                                <span class="value"><?= htmlspecialchars($r['subject'] ?? '—') ?></span>
                                            </div>
                                        </div>
                                        <div class="msg-body-wrap">
                                            <div class="msg-body-label">Message</div>
                                            <div class="msg-body-text"><?= nl2br(htmlspecialchars($r['message'])) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php $qparam = $q !== '' ? '&q=' . urlencode($q) : ''; if ($totalPages > 1): ?>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <small class="text-muted"><?= $total ?> total<?= $q !== '' ? ' (filtered)' : '' ?></small>
                            <nav>
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page - 1 ?><?= $qparam ?>">Prev</a>
                                    </li>
                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?><?= $qparam ?>"><?= $i ?></a></li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page + 1 ?><?= $qparam ?>">Next</a>
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
