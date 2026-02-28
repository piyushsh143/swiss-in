<?php
$page_title = 'Our Services';
$nav_active = 'service';
include __DIR__ . '/includes/header.php';
?>

        <!-- Header Start -->
        <div class="container-fluid bg-breadcrumb">
            <div class="container text-center py-5" style="max-width: 900px;">
                <h3 class="text-white display-3 mb-4 wow fadeInDown" data-wow-delay="0.1s">Our Services</h3>
                <ol class="breadcrumb justify-content-center text-white mb-0 wow fadeInDown" data-wow-delay="0.3s">
                    <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-white">Pages</a></li>
                    <li class="breadcrumb-item active text-secondary">Services</li>
                </ol>
            </div>
        </div>
        <!-- Header End -->

        <?php include __DIR__ . '/includes/services_section.php'; ?>

        <?php include __DIR__ . '/includes/testimonials_section.php'; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
