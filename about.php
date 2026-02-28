<?php
$page_title = 'About Us';
$nav_active = 'about';
include __DIR__ . '/includes/header.php';
?>

<!-- Header Start -->
<div class="container-fluid bg-breadcrumb">
    <div class="container text-center py-5" style="max-width: 900px;">
        <h3 class="text-white display-3 mb-4 wow fadeInDown" data-wow-delay="0.1s">About Us</h3>
        <ol class="breadcrumb justify-content-center text-white mb-0 wow fadeInDown" data-wow-delay="0.3s">
            <li class="breadcrumb-item"><a href="index" class="text-white">Home</a></li>
            <li class="breadcrumb-item"><a href="#" class="text-white">Pages</a></li>
            <li class="breadcrumb-item active text-secondary">About</li>
        </ol>
    </div>
</div>
<!-- Header End -->

<?php include __DIR__ . '/includes/about_section.php'; ?>

<?php include __DIR__ . '/includes/our_presence_section.php'; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>