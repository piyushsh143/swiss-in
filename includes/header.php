<?php
$page_title = $page_title ?? 'Swiis';
$nav_active = $nav_active ?? '';
require_once __DIR__ . '/site_settings.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link rel="icon" href="img/logo.cdr.png" type="image/png">
    <link rel="apple-touch-icon" href="img/logo.cdr.png">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@200;300;400;500;600&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>

    <!-- Spinner Start -->
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-secondary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->
    <script>
    (function() {
        function hideSpinner() {
            var s = document.getElementById('spinner');
            if (s) s.classList.remove('show');
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(hideSpinner, 100);
            });
        } else {
            setTimeout(hideSpinner, 100);
        }
        setTimeout(hideSpinner, 3000);
    })();
    </script>

    <!-- Topbar Start -->
    <div class="container-fluid bg-primary px-5 d-none d-lg-block">
        <div class="row gx-0 align-items-center">
            <div class="col-lg-5 text-center text-lg-start mb-lg-0">
                <div class="d-flex">
                    <a href="mailto:<?= htmlspecialchars($site_email) ?>" class="text-muted me-4"><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($site_email) ?></a>
                    <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $site_phone)) ?>" class="text-muted me-0"><i class="fas fa-phone-alt me-2"></i><?= htmlspecialchars($site_phone) ?></a>
                </div>
            </div>
            <div class="col-lg-3 row-cols-1 text-center mb-2 mb-lg-0">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <a class="btn btn-sm btn-outline-light btn-square rounded-circle me-2"
                        href="https://x.com/swiisconsultant"><i class="fa-brands fa-x-twitter fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-square rounded-circle me-2"
                        href="https://www.facebook.com/profile.php?id=61556441472731"><i
                            class="fab fa-facebook-f fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-square rounded-circle me-2"
                        href="https://www.linkedin.com/in/swiis-consultants-limited-private-8b6451301"><i
                            class="fab fa-instagram fw-normal"></i></a>
                </div>
            </div>
            <div class="col-lg-4 text-center text-lg-end">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <a href="contact" class="text-muted ms-2"> Contact</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Topbar End -->

    <!-- Navbar & Hero Start -->
    <div class="container-fluid nav-bar p-0">
        <nav class="navbar navbar-expand-lg navbar-light bg-white px-4 px-lg-5 py-3 py-lg-0">
            <a href="/" class="navbar-brand p-0">
                <h1 class="display-5 text-secondary m-0"><img src="img/logo.png" class="img-fluid" alt="Swiis">
                </h1>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="fa fa-bars"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto py-0">
                    <a href="index" class="nav-item nav-link <?= $nav_active === 'home' ? 'active' : '' ?>">Home</a>
                    <a href="about"
                        class="nav-item nav-link <?= $nav_active === 'about' ? 'active' : '' ?>">About</a>
                    <a href="service"
                        class="nav-item nav-link <?= $nav_active === 'service' ? 'active' : '' ?>">Service</a>
                    <a href="blog" class="nav-item nav-link <?= $nav_active === 'blog' ? 'active' : '' ?>">Blog</a>
                    <a href="contact"
                        class="nav-item nav-link <?= $nav_active === 'contact' ? 'active' : '' ?>">Contact</a>
                </div>
            </div>
        </nav>
    </div>
    <!-- Navbar & Hero End -->