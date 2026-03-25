<?php
$slug = isset($_GET['slug']) ? preg_replace('/[^a-z0-9-]/', '', strtolower((string) $_GET['slug'])) : '';
$services = require __DIR__ . '/includes/service_pages_data.php';

if ($slug === '' || !isset($services[$slug])) {
    header('Location: service', true, 302);
    exit;
}

$s = $services[$slug];
$page_title = $s['page_title'];
$page_description = $s['meta_description'];
$page_keywords = $s['meta_keywords'];
$nav_active = 'service';

include __DIR__ . '/includes/header.php';
?>

<!-- Header Start -->
<div class="container-fluid bg-breadcrumb">
    <div class="container text-center py-5" style="max-width: 900px;">
        <h3 class="text-white display-3 mb-4 wow fadeInDown" data-wow-delay="0.1s"><?= htmlspecialchars($s['hero_title']) ?></h3>
        <ol class="breadcrumb justify-content-center text-white mb-0 wow fadeInDown" data-wow-delay="0.3s">
            <li class="breadcrumb-item"><a href="index" class="text-white">Home</a></li>
            <li class="breadcrumb-item"><a href="service" class="text-white">Services</a></li>
            <li class="breadcrumb-item active text-secondary"><?= htmlspecialchars($s['breadcrumb']) ?></li>
        </ol>
    </div>
</div>
<!-- Header End -->

<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 wow fadeInUp" data-wow-delay="0.1s">
                <div class="sub-style mb-3">
                    <h5 class="sub-title text-primary px-3">Our Services</h5>
                </div>
                <h1 class="display-5 mb-4"><?= htmlspecialchars($s['lead_heading']) ?></h1>
                <p class="lead text-muted mb-5"><?= htmlspecialchars($s['intro']) ?></p>

                <h2 class="h3 text-secondary mb-3"><?= htmlspecialchars($s['why_title']) ?></h2>
                <?php if (!empty($s['why_intro'])): ?>
                    <p class="mb-3"><?= htmlspecialchars($s['why_intro']) ?></p>
                <?php endif; ?>
                <?php if (!empty($s['why_points'])): ?>
                    <ul class="<?= !empty($s['why_closing']) ? 'mb-3' : 'mb-5' ?>">
                        <?php foreach ($s['why_points'] as $pt): ?>
                            <li class="mb-2"><?= htmlspecialchars($pt) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if (!empty($s['why_closing'])): ?>
                    <p class="mb-5"><?= htmlspecialchars($s['why_closing']) ?></p>
                <?php endif; ?>

                <h2 class="h3 text-secondary mb-4"><?= htmlspecialchars($s['approach_title']) ?></h2>
                <div class="row g-4 mb-5">
                    <?php foreach ($s['approach_items'] as $i => $item): ?>
                        <div class="col-md-6">
                            <div class="border rounded p-4 h-100 bg-light">
                                <div class="d-flex align-items-start">
                                    <span class="btn-lg-square bg-primary text-white rounded-circle me-3 flex-shrink-0 d-inline-flex align-items-center justify-content-center" style="width: 2.5rem; height: 2.5rem;"><?= $i + 1 ?></span>
                                    <div>
                                        <h3 class="h5 mb-2"><?= htmlspecialchars($item['title']) ?></h3>
                                        <p class="mb-0"><?= htmlspecialchars($item['text']) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h2 class="h3 text-secondary mb-3"><?= htmlspecialchars($s['benefits_title']) ?></h2>
                <ul class="mb-5">
                    <?php foreach ($s['benefits'] as $b): ?>
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i><?= htmlspecialchars($b) ?></li>
                    <?php endforeach; ?>
                </ul>

                <div class="border-start border-primary border-4 ps-4 py-2 mb-5">
                    <p class="mb-0 fs-5"><?= htmlspecialchars($s['conclusion']) ?></p>
                </div>

                <p class="mb-0">
                    <a href="contact" class="btn btn-primary rounded-pill py-3 px-5">Discuss your requirements</a>
                    <a href="service" class="btn btn-outline-secondary rounded-pill py-3 px-5 ms-2 mt-2 mt-sm-0">All services</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
