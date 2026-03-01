<?php
require_once __DIR__ . '/config/database.php';
$pdo = getDb();
$blogs = $pdo->query('SELECT id, title, slug, excerpt, featured_image, created_at FROM blogs WHERE is_published = 1 ORDER BY created_at DESC')->fetchAll();
$page_title = 'Blog';
$nav_active = 'blog';
include __DIR__ . '/includes/header.php';
?>
        <!-- Header Start -->
        <div class="container-fluid bg-breadcrumb">
            <div class="container text-center py-5" style="max-width: 900px;">
                <h3 class="text-white display-3 mb-4 wow fadeInDown" data-wow-delay="0.1s">Blog</h3>
                <ol class="breadcrumb justify-content-center text-white mb-0 wow fadeInDown" data-wow-delay="0.3s">
                    <li class="breadcrumb-item"><a href="index" class="text-white">Home</a></li>
                    <li class="breadcrumb-item active text-secondary">Blog</li>
                </ol>
            </div>
        </div>
        <!-- Header End -->

        <div class="container-fluid py-5">
            <div class="container py-5">
                <div class="section-title text-center mb-5 wow fadeInUp" data-wow-delay="0.1s">
                    <h1 class="display-5 mb-4">Latest from our Blog</h1>
                    <p class="mb-0">News and updates from Swiis.</p>
                </div>
                <div class="row g-4">
                    <?php foreach ($blogs as $b): 
                        $summary = $b['excerpt'] ?: $b['title'];
                        $img = $b['featured_image'] ?: 'img/service-1.jpg';
                    ?>
                    <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="card h-100 shadow-sm border-0 rounded overflow-hidden">
                            <a href="blog/<?= urlencode($b['slug']) ?>">
                                <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" alt="<?= htmlspecialchars($b['title']) ?>" style="height: 220px; object-fit: cover;">
                            </a>
                            <div class="card-body">
                                <small class="text-muted"><?= date('F j, Y', strtotime($b['created_at'])) ?></small>
                                <h5 class="card-title mt-2">
                                    <a href="blog/<?= urlencode($b['slug']) ?>" class="text-secondary text-decoration-none"><?= htmlspecialchars($b['title']) ?></a>
                                </h5>
                                <p class="card-text text-muted"><?= htmlspecialchars(mb_substr($summary, 0, 120)) ?><?= mb_strlen($summary) > 120 ? '...' : '' ?></p>
                                <a href="blog/<?= urlencode($b['slug']) ?>" class="btn btn-primary btn-sm rounded-pill">Read more</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($blogs)): ?>
                    <div class="col-12 text-center py-5 text-muted">
                        <p class="mb-0">No blog posts yet. Check back soon!</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
