<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload_helper.php';
requireAdmin();

$pdo = getDb();
const PER_PAGE = 10;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = array_filter(array_map('intval', (array) ($_POST['ids'] ?? [])));
    $action = $_POST['action'] ?? '';
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if ($id > 0 && in_array($action, ['delete', 'publish', 'unpublish'], true)) {
        if ($action === 'delete') {
            $row = $pdo->prepare('SELECT featured_image FROM blogs WHERE id = ?');
            $row->execute([$id]);
            $row = $row->fetch();
            if ($row && !empty($row['featured_image'])) upload_delete_image($row['featured_image']);
            $pdo->prepare('DELETE FROM blogs WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = 'Blog deleted.';
        } elseif ($action === 'publish') {
            $pdo->prepare('UPDATE blogs SET is_published = 1 WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = 'Blog published.';
        } else {
            $pdo->prepare('UPDATE blogs SET is_published = 0 WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = 'Blog unpublished.';
        }
        header('Location: blogs?' . http_build_query(array_filter(['page' => max(1, (int) ($_GET['page'] ?? 1)), 'q' => $_GET['q'] ?? null])));
        exit;
    }

    if (!empty($ids) && in_array($action, ['bulk_delete', 'bulk_publish', 'bulk_unpublish'], true)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        if ($action === 'bulk_delete') {
            $rows = $pdo->prepare("SELECT featured_image FROM blogs WHERE id IN ($placeholders)");
            $rows->execute($ids);
            while ($row = $rows->fetch()) {
                if (!empty($row['featured_image'])) upload_delete_image($row['featured_image']);
            }
            $pdo->prepare("DELETE FROM blogs WHERE id IN ($placeholders)")->execute($ids);
            $_SESSION['flash'] = count($ids) . ' blog(s) deleted.';
        } elseif ($action === 'bulk_publish') {
            $pdo->prepare("UPDATE blogs SET is_published = 1 WHERE id IN ($placeholders)")->execute($ids);
            $_SESSION['flash'] = count($ids) . ' blog(s) published.';
        } else {
            $pdo->prepare("UPDATE blogs SET is_published = 0 WHERE id IN ($placeholders)")->execute($ids);
            $_SESSION['flash'] = count($ids) . ' blog(s) unpublished.';
        }
        header('Location: blogs?' . http_build_query(array_filter(['page' => max(1, (int) ($_GET['page'] ?? 1)), 'q' => $_GET['q'] ?? null])));
        exit;
    }
}

$q = trim((string) ($_GET['q'] ?? ''));
$where = '';
$params = [];
if ($q !== '') {
    $where = ' WHERE (title LIKE ? OR slug LIKE ? OR excerpt LIKE ? OR content LIKE ?)';
    $term = '%' . $q . '%';
    $params = [$term, $term, $term, $term];
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$countSql = 'SELECT COUNT(*) FROM blogs' . $where;
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

$sql = 'SELECT * FROM blogs' . $where . ' ORDER BY created_at DESC LIMIT ' . (int) PER_PAGE . ' OFFSET ' . (int) $offset;
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
    <title>Blogs - Swiis Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .sidebar { min-height: 100vh; background: #1a1a2e; }
        .sidebar a { color: rgba(255,255,255,.8); text-decoration: none; padding: 12px 16px; display: block; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background: rgba(255,255,255,.1); }
        .btn-icon { padding: 0.25rem 0.5rem; }
        /* Blog view modal */
        .modal-blog .modal-dialog { max-width: 640px; }
        .modal-blog .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,.25); }
        .modal-blog .modal-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff; border: none; padding: 1.25rem 1.5rem;
        }
        .modal-blog .modal-title { font-weight: 600; font-size: 1.1rem; }
        .modal-blog .btn-close { filter: invert(1); opacity: .8; }
        .modal-blog .btn-close:hover { opacity: 1; }
        .modal-blog .modal-body { padding: 1.5rem; background: #fafbfc; max-height: 70vh; overflow-y: auto; }
        .modal-blog .blog-view-meta { display: grid; gap: 0.75rem; margin-bottom: 1.25rem; }
        .modal-blog .blog-view-meta .meta-item {
            display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.6rem 0.9rem;
            background: #fff; border-radius: 10px; border: 1px solid #e8ecf0; font-size: 0.9rem;
        }
        .modal-blog .blog-view-meta .meta-item i { color: #6366f1; margin-top: 2px; font-size: 1rem; flex-shrink: 0; }
        .modal-blog .blog-view-meta .meta-item .label { color: #64748b; font-weight: 500; min-width: 80px; }
        .modal-blog .blog-view-meta .meta-item .value { color: #1e293b; }
        .modal-blog .blog-view-featured { margin-bottom: 1rem; border-radius: 12px; overflow: hidden; border: 1px solid #e8ecf0; }
        .modal-blog .blog-view-featured img { width: 100%; display: block; }
        .modal-blog .blog-view-excerpt { background: #fff; border-radius: 12px; border: 1px solid #e8ecf0; padding: 1rem 1.25rem; margin-bottom: 1rem; font-size: 0.9rem; color: #475569; }
        .modal-blog .blog-view-content { background: #fff; border-radius: 12px; border: 1px solid #e8ecf0; padding: 1.1rem 1.25rem; }
        .modal-blog .blog-view-content .content-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 600; margin-bottom: 0.5rem; }
        .modal-blog .blog-view-content .content-text { color: #334155; line-height: 1.6; margin: 0; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 sidebar">
                <div class="py-3 px-3 text-white fw-bold">Swiis Admin</div>
                <a href="dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a href="testimonials"><i class="bi bi-chat-quote me-2"></i>Testimonials</a>
                <a href="blogs" class="active"><i class="bi bi-journal-text me-2"></i>Blogs</a>
                <a href="partners"><i class="bi bi-people me-2"></i>Clients</a>
                <a href="geographies"><i class="bi bi-geo-alt me-2"></i>Geographies</a>
                <a href="contacts"><i class="bi bi-envelope me-2"></i>Contact Us</a>
                <a href="site_settings"><i class="bi bi-gear me-2"></i>Site Settings</a>
                <hr class="border-secondary">
                <a href="logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </nav>
            <main class="col-md-10 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="mb-0">Blogs</h1>
                    <a href="blog-edit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Blog</a>
                </div>
                <?php if ($flash): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
                <?php endif; ?>
                <form method="get" class="mb-3" action="blogs">
                    <div class="input-group" style="max-width: 320px;">
                        <input type="search" name="q" class="form-control" placeholder="Search title, slug, content…" value="<?= htmlspecialchars($q) ?>">
                        <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
                        <?php if ($q !== ''): ?><a href="blogs" class="btn btn-outline-secondary">Clear</a><?php endif; ?>
                    </div>
                </form>
                <form method="post" id="bulk-form">
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <span class="text-muted small">With selected:</span>
                        <button type="submit" name="action" value="bulk_publish" class="btn btn-sm btn-success"><i class="bi bi-check-circle me-1"></i>Publish</button>
                        <button type="submit" name="action" value="bulk_unpublish" class="btn btn-sm btn-warning"><i class="bi bi-x-circle me-1"></i>Unpublish</button>
                        <button type="submit" name="action" value="bulk_delete" class="btn btn-sm btn-danger" onclick="return confirm('Delete selected blogs?');"><i class="bi bi-trash me-1"></i>Delete</button>
                    </div>
                    <div class="card shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40"><input type="checkbox" class="form-check-input" id="select-all" title="Select all"></th>
                                        <th>Title</th>
                                        <th>Slug</th>
                                        <th>Excerpt</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th width="140">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $r): ?>
                                        <tr>
                                            <td><input type="checkbox" name="ids[]" value="<?= (int) $r['id'] ?>" class="form-check-input row-cb"></td>
                                            <td><?= htmlspecialchars($r['title']) ?></td>
                                            <td><code><?= htmlspecialchars($r['slug']) ?></code></td>
                                            <td><span class="text-muted"><?= htmlspecialchars(mb_substr($r['excerpt'] ?? $r['content'], 0, 50)) ?>...</span></td>
                                            <td>
                                                <?php if ($r['is_published']): ?>
                                                    <span class="badge bg-success">Published</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Unpublished</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('Y-m-d', strtotime($r['created_at'])) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-info btn-icon" data-bs-toggle="modal" data-bs-target="#blogModal<?= (int) $r['id'] ?>" title="View"><i class="bi bi-eye"></i></button>
                                                <a href="blog-edit?id=<?= (int) $r['id'] ?>" class="btn btn-sm btn-outline-primary btn-icon" title="Edit"><i class="bi bi-pencil"></i></a>
                                                <form method="post" class="d-inline" onsubmit="return confirm('Delete this blog?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" title="Delete"><i class="bi bi-trash"></i></button>
                                                </form>
                                                <?php if ($r['is_published']): ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="unpublish">
                                                        <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-warning btn-icon" title="Unpublish"><i class="bi bi-x-circle"></i></button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="publish">
                                                        <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-success btn-icon" title="Publish"><i class="bi bi-check-circle"></i></button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($rows)): ?>
                                        <tr><td colspan="7" class="text-center text-muted py-4"><?= $q !== '' ? 'No blogs match your search.' : 'No blogs yet. <a href="blog-edit">Add one</a>.' ?></td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php foreach ($rows as $r): ?>
                        <div class="modal fade modal-blog" id="blogModal<?= (int) $r['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><?= htmlspecialchars($r['title']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="blog-view-meta">
                                            <div class="meta-item">
                                                <i class="bi bi-link-45deg"></i>
                                                <span class="label">Slug</span>
                                                <span class="value"><code class="small"><?= htmlspecialchars($r['slug']) ?></code></span>
                                            </div>
                                            <div class="meta-item">
                                                <i class="bi bi-calendar3"></i>
                                                <span class="label">Created</span>
                                                <span class="value"><?= htmlspecialchars(date('M j, Y \a\t g:i A', strtotime($r['created_at']))) ?></span>
                                            </div>
                                            <div class="meta-item">
                                                <i class="bi bi-toggle2-on"></i>
                                                <span class="label">Status</span>
                                                <span class="value">
                                                    <?php if ($r['is_published']): ?>
                                                        <span class="badge bg-success">Published</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Unpublished</span>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?php if (!empty($r['featured_image'])): ?>
                                        <div class="blog-view-featured">
                                            <img src="../<?= htmlspecialchars($r['featured_image']) ?>" alt="">
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty(trim($r['excerpt'] ?? ''))): ?>
                                        <div class="blog-view-excerpt"><?= nl2br(htmlspecialchars($r['excerpt'])) ?></div>
                                        <?php endif; ?>
                                        <div class="blog-view-content">
                                            <div class="content-label">Content</div>
                                            <div class="content-text"><?= nl2br(htmlspecialchars($r['content'] ?? '')) ?></div>
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
