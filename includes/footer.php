<?php
if (!isset($site_email)) {
    require_once __DIR__ . '/site_settings.php';
}
?>
<!-- Footer Start -->
<div class="container-fluid footer py-5 wow fadeIn" data-wow-delay="0.2s">
    <div class="container py-5">
        <div class="row g-5">

            <!-- Contact Info -->
            <div class="col-md-6 col-lg-6 col-xl-3">
                <div class="footer-item d-flex flex-column">
                    <h4 class="text-secondary mb-4">Contact Info</h4>

                    <?php if (!empty($office_addresses)): ?>
                        <?php foreach ($office_addresses as $addr): ?>
                        <div class="mb-2">
                            <?php if (!empty($addr['title'])): ?><strong class="d-block text-secondary small"><?= htmlspecialchars($addr['title']) ?></strong><?php endif; ?>
                            <span class="text-white"><?= nl2br(htmlspecialchars($addr['address'])) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>

                    <a href="mailto:<?= htmlspecialchars($site_email) ?>">
                        <i class="fas fa-envelope me-2"></i> <?= htmlspecialchars($site_email) ?>
                    </a>

                    <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $site_phone)) ?>">
                        <i class="fas fa-phone me-2"></i> <?= htmlspecialchars($site_phone) ?>
                    </a>

                    <div class="d-flex align-items-center mt-3">
                        <i class="fas fa-share fa-2x text-secondary me-2"></i>
                        <a class="btn mx-1" href="#"><i class="fab fa-facebook-f"></i></a>
                        <a class="btn mx-1" href="#"><i class="fa-brands fa-x-twitter"></i></a>
                        <a class="btn mx-1" href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>

            <!-- Business Hours -->
            <div class="col-md-6 col-lg-6 col-xl-3">
                <div class="footer-item d-flex flex-column">
                    <h4 class="text-secondary mb-4">Business Hours</h4>
                    <?php if (!empty(trim($site_business_hours))): ?>
                        <div class="text-white"><?= nl2br(htmlspecialchars($site_business_hours)) ?></div>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Our Services -->
            <div class="col-md-6 col-lg-6 col-xl-3">
                <div class="footer-item d-flex flex-column">
                    <h4 class="text-secondary mb-4">Our Services</h4>

                    <a href="#"><i class="fas fa-angle-right me-2"></i> Credit Card Recovery</a>
                    <a href="#"><i class="fas fa-angle-right me-2"></i> Loan EMI Collection</a>
                    <a href="#"><i class="fas fa-angle-right me-2"></i> NBFC Recovery Services</a>
                    <a href="#"><i class="fas fa-angle-right me-2"></i> NPA & Default Recovery</a>
                    <a href="#"><i class="fas fa-angle-right me-2"></i> Settlement & Negotiation</a>
                    <a href="#"><i class="fas fa-angle-right me-2"></i> Legal & Pre-Litigation Support</a>
                </div>
            </div>

            <!-- Compliance & Quick Links -->
            <div class="col-md-6 col-lg-6 col-xl-3">
                <div class="footer-item d-flex flex-column">
                    <h4 class="text-secondary mb-4">Compliance & Policies</h4>

                    <a href="#"><i class="fas fa-angle-right me-2"></i> About Us</a>
                    <a href="#"><i class="fas fa-angle-right me-2"></i> Recovery Process</a>
                    <a href="#"><i class="fas fa-angle-right me-2"></i> RBI Compliance</a>
                    <a href="#"><i class="fas fa-angle-right me-2"></i> Ethical Collection Policy</a>
                    <a href="#"><i class="fas fa-angle-right me-2"></i> Privacy Policy</a>
                    <a href="#"><i class="fas fa-angle-right me-2"></i> Contact Us</a>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- Footer End -->

<!-- Compliance Add-On -->
<div class="container-fluid bg-dark text-center py-3">
    <small class="text-muted">
        Swiis Consultants Pvt. Ltd. follows ethical recovery practices in compliance with applicable RBI guidelines
        and regulatory standards.
        We are committed to fair communication, data protection, and responsible collection procedures.
    </small>
</div>

<!-- Copyright Start -->
<div class="container-fluid copyright py-4">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-md-12 text-center text-md-start mb-md-0">
                <span class="text-white"><i class="fas fa-copyright text-light me-2"></i>Copyright 2026 SWIIS
                    CONSULTANTS PRIVATE
                    LIMITED. All rights reserved.</span>
            </div>
        </div>
    </div>
</div>
<!-- Copyright End -->

<!-- Back to Top -->
<a href="#" class="btn btn-primary btn-lg-square back-to-top"><i class="fa fa-arrow-up"></i></a>

<!-- JavaScript Libraries -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/wow/wow.min.js"></script>
<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>

<!-- Template Javascript -->
<script src="js/main.js"></script>
</body>

</html>