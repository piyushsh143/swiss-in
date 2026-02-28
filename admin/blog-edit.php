<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload_helper.php';
requireAdmin();

function slugify(string $s): string {
    $s = preg_replace('/[^a-z0-9]+/i', '-', strtolower($s));
    return trim($s, '-');
}

$pdo = getDb();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM blogs WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) {
        header('Location: blogs.php');
        exit;
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '') ?: slugify($title);
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $featured_image = $item['featured_image'] ?? null;
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    if ($title === '') $errors[] = 'Title is required.';
    if ($content === '') $errors[] = 'Content is required.';

    if (!empty($_FILES['featured_image']['name']) && $_FILES['featured_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        list($ok, $uploadErr) = upload_validate_image($_FILES['featured_image']);
        if (!$ok) {
            $errors[] = $uploadErr;
        } else {
            if ($id > 0 && $featured_image) {
                upload_delete_image($featured_image);
            }
            $newPath = upload_save_image($_FILES['featured_image'], 'blogs');
            if ($newPath) {
                $featured_image = $newPath;
            } else {
                $errors[] = 'Image could not be saved.';
            }
        }
    }

    if (empty($errors)) {
        $stmtCheck = $pdo->prepare('SELECT id FROM blogs WHERE slug = ? AND id != ?');
        $stmtCheck->execute([$slug, $id]);
        if ($stmtCheck->fetch()) {
            $errors[] = 'Slug already in use.';
        }
    }

    if (empty($errors)) {
        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE blogs SET title=?, slug=?, content=?, excerpt=?, featured_image=?, is_published=? WHERE id=?');
            $stmt->execute([$title, $slug, $content, $excerpt ?: null, $featured_image ?: null, $is_published, $id]);
            $_SESSION['flash'] = 'Blog updated.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO blogs (title, slug, content, excerpt, featured_image, is_published) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$title, $slug, $content, $excerpt ?: null, $featured_image ?: null, $is_published]);
            $_SESSION['flash'] = 'Blog added.';
        }
        header('Location: blogs.php');
        exit;
    }
    $item = array_merge($item ?: [], [
        'title' => $title,
        'slug' => $slug,
        'content' => $content,
        'excerpt' => $excerpt,
        'featured_image' => $featured_image,
        'is_published' => $is_published,
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $id ? 'Edit' : 'Add' ?> Blog - Travisa Admin</title>
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
                <a href="blogs.php" class="active"><i class="bi bi-journal-text me-2"></i>Blogs</a>
                <a href="partners.php"><i class="bi bi-people me-2"></i>Partners</a>
                <a href="geographies.php"><i class="bi bi-geo-alt me-2"></i>Geographies</a>
                <a href="contacts.php"><i class="bi bi-envelope me-2"></i>Contact Us</a>
                <hr class="border-secondary">
                <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </nav>
            <main class="col-md-10 py-4">
                <h1 class="mb-4"><?= $id ? 'Edit' : 'Add' ?> Blog</h1>
                <?php foreach ($errors as $e): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title *</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($item['title'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="slug" class="form-label">URL Slug (leave blank to auto-generate)</label>
                                <input type="text" class="form-control" id="slug" name="slug" value="<?= htmlspecialchars($item['slug'] ?? '') ?>" placeholder="e.g. my-first-post">
                            </div>
                            <div class="mb-3">
                                <label for="excerpt" class="form-label">Excerpt (short summary)</label>
                                <textarea class="form-control" id="excerpt" name="excerpt" rows="2"><?= htmlspecialchars($item['excerpt'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="content" class="form-label">Content *</label>
                                <textarea class="form-control" id="content" name="content" rows="12" required><?= htmlspecialchars($item['content'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="featured_image" class="form-label">Featured image (JPEG, PNG, GIF, WebP – max 5MB)</label>
                                <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/jpeg,image/png,image/gif,image/webp">
                                <?php if (!empty($item['featured_image'])): ?>
                                    <div class="mt-2">
                                        <img src="../<?= htmlspecialchars($item['featured_image']) ?>" alt="Current" class="img-thumbnail" style="max-height: 80px;">
                                        <small class="d-block text-muted">Current image. Upload a new file to replace.</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_published" value="1" id="is_published" <?= ($item['is_published'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_published">Published (visible on site)</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Blog</button>
                            <a href="blogs.php" class="btn btn-outline-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('title').addEventListener('blur', function() {
            var slug = document.getElementById('slug');
            if (!slug.value) slug.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        });
    </script>
</body>
</html>
