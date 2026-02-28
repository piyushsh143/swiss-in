<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload_helper.php';
requireAdmin();

$pdo = getDb();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM partners WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) {
        header('Location: partners.php');
        exit;
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sort_order = isset($_POST['sort_order']) ? (int) $_POST['sort_order'] : 0;
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $image_path = $item['image_path'] ?? null;

    if ($title === '') $errors[] = 'Title is required.';

    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        list($ok, $uploadErr) = upload_validate_image($_FILES['image']);
        if (!$ok) {
            $errors[] = $uploadErr;
        } else {
            if ($id > 0 && $image_path) {
                upload_delete_image($image_path);
            }
            $newPath = upload_save_image($_FILES['image'], 'partners');
            if ($newPath) {
                $image_path = $newPath;
            } else {
                $errors[] = 'Image could not be saved.';
            }
        }
    }

    if (empty($errors)) {
        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE partners SET title=?, description=?, image_path=?, sort_order=?, is_published=? WHERE id=?');
            $stmt->execute([$title, $description ?: null, $image_path ?: null, $sort_order, $is_published, $id]);
            $_SESSION['flash'] = 'Partner updated.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO partners (title, description, image_path, sort_order, is_published) VALUES (?,?,?,?,?)');
            $stmt->execute([$title, $description ?: null, $image_path ?: null, $sort_order, $is_published]);
            $_SESSION['flash'] = 'Partner added.';
        }
        header('Location: partners.php');
        exit;
    }
    $item = array_merge($item ?: [], [
        'title' => $title,
        'description' => $description,
        'image_path' => $image_path,
        'sort_order' => $sort_order,
        'is_published' => $is_published,
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $id ? 'Edit' : 'Add' ?> Partner - Travisa Admin</title>
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
                <h1 class="mb-4"><?= $id ? 'Edit' : 'Add' ?> Partner</h1>
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
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="image" class="form-label">Image (JPEG, PNG, GIF, WebP – max 5MB)</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                                    <?php if (!empty($item['image_path'])): ?>
                                        <div class="mt-2">
                                            <img src="../<?= htmlspecialchars($item['image_path']) ?>" alt="Current" class="img-thumbnail" style="max-height: 80px;">
                                            <small class="d-block text-muted">Current image. Upload a new file to replace.</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="sort_order" class="form-label">Sort order</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order" value="<?= (int) ($item['sort_order'] ?? 0) ?>">
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_published" value="1" id="is_published" <?= ($item['is_published'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_published">Published (visible on site)</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Partner</button>
                            <a href="partners.php" class="btn btn-outline-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
