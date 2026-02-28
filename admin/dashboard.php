<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pdo = getDb();
$testimonialCount = $pdo->query('SELECT COUNT(*) FROM testimonials')->fetchColumn();
$blogCount = $pdo->query('SELECT COUNT(*) FROM blogs')->fetchColumn();
$contactCount = $pdo->query('SELECT COUNT(*) FROM contactUs')->fetchColumn();
$contactUnread = $pdo->query('SELECT COUNT(*) FROM contactUs WHERE is_read = 0')->fetchColumn();
$publishedTestimonials = $pdo->query('SELECT COUNT(*) FROM testimonials WHERE is_published = 1')->fetchColumn();
$publishedBlogs = $pdo->query('SELECT COUNT(*) FROM blogs WHERE is_published = 1')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Dashboard - Travisa Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .sidebar { min-height: 100vh; background: #1a1a2e; }
        .sidebar a { color: rgba(255,255,255,.8); text-decoration: none; padding: 12px 16px; display: block; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background: rgba(255,255,255,.1); }
        .card-dash { border: none; border-radius: 12px; transition: transform .2s; }
        .card-dash:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 sidebar">
                <div class="py-3 px-3 text-white fw-bold">Travisa Admin</div>
                <a href="dashboard.php" class="active"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a href="testimonials.php"><i class="bi bi-chat-quote me-2"></i>Testimonials</a>
                <a href="blogs.php"><i class="bi bi-journal-text me-2"></i>Blogs</a>
                <a href="contacts.php"><i class="bi bi-envelope me-2"></i>Contact Us</a>
                <hr class="border-secondary">
                <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </nav>
            <main class="col-md-10 py-4">
                <h1 class="mb-4">Dashboard</h1>
                <p class="text-muted">Welcome, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></p>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card card-dash shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title text-muted">Testimonials</h5>
                                        <h2 class="mb-0"><?= (int) $testimonialCount ?></h2>
                                        <small class="text-success"><?= (int) $publishedTestimonials ?> published</small>
                                    </div>
                                    <a href="testimonials.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Manage</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-dash shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title text-muted">Blogs</h5>
                                        <h2 class="mb-0"><?= (int) $blogCount ?></h2>
                                        <small class="text-success"><?= (int) $publishedBlogs ?> published</small>
                                    </div>
                                    <a href="blogs.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Manage</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-dash shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title text-muted">Contact Us</h5>
                                        <h2 class="mb-0"><?= (int) $contactCount ?></h2>
                                        <small class="<?= $contactUnread ? 'text-warning' : 'text-success' ?>"><?= (int) $contactUnread ?> unread</small>
                                    </div>
                                    <a href="contacts.php" class="btn btn-primary"><i class="bi bi-envelope"></i> View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
