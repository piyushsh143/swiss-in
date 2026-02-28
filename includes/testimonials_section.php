<?php
if (!function_exists('getDb')) {
    require_once __DIR__ . '/../config/database.php';
}
$pdo = getDb();
$testimonials = $pdo->query('SELECT * FROM testimonials WHERE is_published = 1 ORDER BY created_at DESC')->fetchAll();
?>
<!-- Testimonial Start -->
<div class="container-fluid testimonial overflow-hidden py-5">
    <div class="container">
        <div class="section-title text-center mb-5 wow fadeInUp" data-wow-delay="0.1s">
            <div class="sub-style">
                <h5 class="sub-title text-primary px-3">OUR CLIENTS REVIEWS</h5>
            </div>
            <h1 class="display-5 mb-4">What Our Clients Say</h1>
            <p class="mb-0">We are proud to support banks, NBFCs, and financial institutions with ethical,
                technology-driven debt recovery solutions. Our clients trust us for our structured processes, regulatory
                compliance, and measurable recovery performance across diverse loan portfolios.</p>
        </div>
        <div class="owl-carousel testimonial-carousel wow zoomInDown" data-wow-delay="0.2s">
            <?php foreach ($testimonials as $t):
                $img = $t['image_path'] ?: 'img/testimonial-1.jpg';
                $stars = (int) $t['rating'];
                ?>
            <div class="testimonial-item">
                <div class="testimonial-content p-4 mb-5">
                    <p class="fs-5 mb-0"><?= nl2br(htmlspecialchars($t['content'])) ?></p>
                    <div class="d-flex justify-content-end">
                        <?php for ($i = 0; $i < $stars; $i++): ?>
                        <i class="fas fa-star text-secondary"></i>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="rounded-circle me-4" style="width: 100px; height: 100px;">
                        <img class="img-fluid rounded-circle" src="<?= htmlspecialchars($img) ?>"
                            alt="<?= htmlspecialchars($t['author_name']) ?>">
                    </div>
                    <div class="my-auto">
                        <h5><?= htmlspecialchars($t['author_name']) ?></h5>
                        <p class="mb-0"><?= htmlspecialchars($t['profession'] ?? '') ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($testimonials)): ?>
            <div class="testimonial-item">
                <div class="testimonial-content p-4 mb-5">
                    <p class="fs-5 mb-0">No testimonials yet. Check back soon!</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Testimonial End -->