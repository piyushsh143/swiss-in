<?php
require_once __DIR__ . '/config/database.php';
$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    header('Location: blog');
    exit;
}
$pdo = getDb();
$stmt = $pdo->prepare('SELECT * FROM blogs WHERE slug = ? AND is_published = 1');
$stmt->execute([$slug]);
$post = $stmt->fetch();
if (!$post) {
    header('Location: blog');
    exit;
}
$page_title = $post['title'];
$nav_active = 'blog';
include __DIR__ . '/includes/header.php';
?>
        <!-- Header Start -->
        <div class="container-fluid bg-breadcrumb">
            <div class="container text-center py-5" style="max-width: 900px;">
                <h3 class="text-white display-5 mb-4 wow fadeInDown" data-wow-delay="0.1s"><?= htmlspecialchars($post['title']) ?></h3>
                <ol class="breadcrumb justify-content-center text-white mb-0 wow fadeInDown" data-wow-delay="0.3s">
                    <li class="breadcrumb-item"><a href="index" class="text-white">Home</a></li>
                    <li class="breadcrumb-item"><a href="blog" class="text-white">Blog</a></li>
                    <li class="breadcrumb-item active text-secondary"><?= htmlspecialchars(mb_substr($post['title'], 0, 30)) ?><?= mb_strlen($post['title']) > 30 ? '...' : '' ?></li>
                </ol>
            </div>
        </div>
        <!-- Header End -->

        <div class="container-fluid py-5">
            <div class="container py-5">
                <article class="mx-auto" style="max-width: 800px;">
                    <p class="text-muted mb-3"><?= date('F j, Y', strtotime($post['created_at'])) ?></p>
                    <?php if ($post['featured_image']): ?>
                    <img src="<?= htmlspecialchars($post['featured_image']) ?>" class="img-fluid rounded mb-4 w-100" alt="<?= htmlspecialchars($post['title']) ?>">
                    <?php endif; ?>
                    <div class="content">
                        <?= nl2br(htmlspecialchars($post['content'])) ?>
                    </div>
                    <hr class="my-5">
                    <a href="blog" class="btn btn-primary rounded-pill">&larr; Back to Blog</a>
                </article>
            </div>
        </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
