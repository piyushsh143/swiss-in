<?php
$partners = [];
try {
    if (!function_exists('getDb')) {
        require_once __DIR__ . '/../config/database.php';
    }
    $pdo = getDb();
    $partners = $pdo->query('SELECT id, title, description, image_path FROM partners WHERE is_published = 1 ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $partners = [];
}

$items = $partners;
if (empty($items)) {
    $items = [
        ['title' => 'Banking Partners', 'description' => 'Supporting national and private sector banks in recovering retail loans, credit card dues, and high-value NPA portfolios.', 'image_path' => null],
        ['title' => 'NBFC Partners', 'description' => 'Specialized recovery services for NBFC loan portfolios including personal loans, microfinance accounts, and unsecured lending.', 'image_path' => null],
        ['title' => 'Fintech Companies', 'description' => 'Technology-enabled recovery solutions for digital lenders, BNPL providers, and emerging fintech platforms.', 'image_path' => null],
        ['title' => 'Legal & Compliance Advisors', 'description' => 'Coordinated legal and pre-litigation support to ensure fully compliant recovery escalation procedures.', 'image_path' => null],
    ];
}
?>
<!-- Trusted Partners Start -->
<div class="container-fluid contact overflow-hidden py-5">
    <div class="container pb-5">
        <div class="office">
            <div class="section-title text-center mb-5 wow fadeInUp" data-wow-delay="0.1s">
                <div class="sub-style">
                    <h5 class="sub-title text-primary px-3">Our Clients</h5>
                </div>
                <h1 class="display-5 mb-4">Financial Institutions We Support</h1>
                <p class="mb-0">
                    We collaborate with leading banks, NBFCs, fintech companies, and legal advisors
                    to deliver compliant, ethical, and performance-driven debt recovery solutions.
                    Our partnerships are built on transparency, trust, and measurable recovery outcomes.
                </p>
            </div>
        </div>
    </div>

    <div class="partners-carousel-wrap">
        <div class="partners-carousel-track" aria-hidden="true">
            <?php for ($copy = 0; $copy < 2; $copy++): ?>
            <?php foreach ($items as $p): ?>
            <div class="partner-card partner-card--carousel">
                <div class="partner-card__top">
                    <?php if (!empty($p['image_path'])): ?>
                    <img src="<?= htmlspecialchars($p['image_path']) ?>" class="partner-card__img"
                        alt="<?= htmlspecialchars($p['title']) ?>">
                    <?php else: ?>
                    <span class="partner-card__top-watermark" aria-hidden="true"><?= htmlspecialchars($p['title']) ?></span>
                    <?php endif; ?>
                    <i class="bi bi-gem partner-card__top-icon" aria-hidden="true"></i>
                </div>
                <h4 class="partner-card__heading"><?= htmlspecialchars($p['title']) ?></h4>
                <?php if (!empty($p['description'])): ?>
                <p class="partner-card__desc"><?= nl2br(htmlspecialchars($p['description'])) ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endfor; ?>
        </div>
    </div>
</div>
<!-- Trusted Partners End -->
