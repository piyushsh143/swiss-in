<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload_helper.php';
requireAdmin();

$pdo = getDb();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM testimonials WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) {
        header('Location: testimonials');
        exit;
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author_name = trim($_POST['author_name'] ?? '');
    $profession = trim($_POST['profession'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $rating = isset($_POST['rating']) ? min(5, max(1, (int) $_POST['rating'])) : 5;
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $image_path = $item['image_path'] ?? null;

    if ($author_name === '') $errors[] = 'Author name is required.';
    if ($content === '') $errors[] = 'Content is required.';

    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        list($ok, $uploadErr) = upload_validate_image($_FILES['image']);
        if (!$ok) {
            $errors[] = $uploadErr;
        } else {
            if ($id > 0 && $image_path) {
                upload_delete_image($image_path);
            }
            $newPath = upload_save_image($_FILES['image'], 'testimonials');
            if ($newPath) {
                $image_path = $newPath;
            } else {
                $errors[] = 'Image could not be saved.';
            }
        }
    }

    if (empty($errors)) {
        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE testimonials SET author_name=?, profession=?, content=?, image_path=?, rating=?, is_published=? WHERE id=?');
            $stmt->execute([$author_name, $profession, $content, $image_path ?: null, $rating, $is_published, $id]);
            $_SESSION['flash'] = 'Testimonial updated.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO testimonials (author_name, profession, content, image_path, rating, is_published) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$author_name, $profession, $content, $image_path ?: null, $rating, $is_published]);
            $_SESSION['flash'] = 'Testimonial added.';
        }
        header('Location: testimonials');
        exit;
    }
    $item = array_merge($item ?: [], [
        'author_name' => $author_name,
        'profession' => $profession,
        'content' => $content,
        'image_path' => $image_path,
        'rating' => $rating,
        'is_published' => $is_published,
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $id ? 'Edit' : 'Add' ?> Testimonial - Swiis Admin</title>
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
                <a href="testimonials" class="active"><i class="bi bi-chat-quote me-2"></i>Testimonials</a>
                <a href="blogs"><i class="bi bi-journal-text me-2"></i>Blogs</a>
                <a href="partners"><i class="bi bi-people me-2"></i>Clients</a>
                <a href="geographies"><i class="bi bi-geo-alt me-2"></i>Geographies</a>
                <a href="contacts"><i class="bi bi-envelope me-2"></i>Contact Us</a>
                <a href="site_settings"><i class="bi bi-gear me-2"></i>Site Settings</a>
                <hr class="border-secondary">
                <a href="logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </nav>
            <main class="col-md-10 py-4">
                <h1 class="mb-4"><?= $id ? 'Edit' : 'Add' ?> Testimonial</h1>
                <?php foreach ($errors as $e): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="author_name" class="form-label">Author Name *</label>
                                    <input type="text" class="form-control" id="author_name" name="author_name" value="<?= htmlspecialchars($item['author_name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="profession" class="form-label">Profession</label>
                                    <input type="text" class="form-control" id="profession" name="profession" value="<?= htmlspecialchars($item['profession'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="content" class="form-label">Content *</label>
                                <textarea class="form-control" id="content" name="content" rows="4" required><?= htmlspecialchars($item['content'] ?? '') ?></textarea>
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
                                <div class="col-md-6 mb-3">
                                    <label for="rating" class="form-label">Rating (1-5)</label>
                                    <select class="form-select" id="rating" name="rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <option value="<?= $i ?>" <?= (($item['rating'] ?? 5) == $i) ? 'selected' : '' ?>><?= $i ?> star<?= $i > 1 ? 's' : '' ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_published" value="1" id="is_published" <?= ($item['is_published'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_published">Published (visible on site)</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Testimonial</button>
                            <a href="testimonials" class="btn btn-outline-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
