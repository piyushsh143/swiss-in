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
?>
<!-- Trusted Partners Start -->
<div class="container-fluid contact overflow-hidden py-5">
    <div class="container pb-5">
        <div class="office">
            <div class="section-title text-center mb-5 wow fadeInUp" data-wow-delay="0.1s">
                <div class="sub-style">
                    <h5 class="sub-title text-primary px-3">Trusted Partners</h5>
                </div>
                <h1 class="display-5 mb-4">Financial Institutions We Support</h1>
                <p class="mb-0">
                    We collaborate with leading banks, NBFCs, fintech companies, and legal advisors
                    to deliver compliant, ethical, and performance-driven debt recovery solutions.
                    Our partnerships are built on transparency, trust, and measurable recovery outcomes.
                </p>
            </div>

            <div class="row g-4 justify-content-center partners-cards">
                <?php if (!empty($partners)): ?>
                <?php foreach ($partners as $i => $p): ?>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="<?= 0.1 + ($i * 0.2) ?>s">
                    <div class="partner-card">
                        <div class="partner-card__top">
                            <?php if (!empty($p['image_path'])): ?>
                            <img src="<?= htmlspecialchars($p['image_path']) ?>" class="partner-card__img" alt="">
                            <?php endif; ?>
                            <i class="bi bi-gem partner-card__top-icon" aria-hidden="true"></i>
                        </div>
                        <h4 class="partner-card__heading"><?= htmlspecialchars($p['title']) ?></h4>
                        <?php if (!empty($p['description'])): ?>
                        <p class="partner-card__desc"><?= nl2br(htmlspecialchars($p['description'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <!-- Fallback: static partners when none in DB -->
                <?php
                    $fallback = [
                        ['title' => 'Banking Partners', 'desc' => 'Supporting national and private sector banks in recovering retail loans, credit card dues, and high-value NPA portfolios.'],
                        ['title' => 'NBFC Partners', 'desc' => 'Specialized recovery services for NBFC loan portfolios including personal loans, microfinance accounts, and unsecured lending.'],
                        ['title' => 'Fintech Companies', 'desc' => 'Technology-enabled recovery solutions for digital lenders, BNPL providers, and emerging fintech platforms.'],
                        ['title' => 'Legal & Compliance Advisors', 'desc' => 'Coordinated legal and pre-litigation support to ensure fully compliant recovery escalation procedures.'],
                    ];
                    foreach ($fallback as $i => $p):
                        ?>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="<?= 0.1 + ($i * 0.2) ?>s">
                    <div class="partner-card">
                        <div class="partner-card__top">
                            <span class="partner-card__top-watermark"
                                aria-hidden="true"><?= htmlspecialchars($p['title']) ?></span>
                            <i class="bi bi-gem partner-card__top-icon" aria-hidden="true"></i>
                        </div>
                        <h4 class="partner-card__heading"><?= htmlspecialchars($p['title']) ?></h4>
                        <p class="partner-card__desc"><?= htmlspecialchars($p['desc']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- Trusted Partners End -->