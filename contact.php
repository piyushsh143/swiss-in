<?php
require_once __DIR__ . '/config/database.php';

$page_title = 'Contact Us';
$nav_active = 'contact';

$success = false;
$error = '';
$form_data = ['name' => '', 'email' => '', 'phone' => '', 'project' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data['name'] = trim($_POST['name'] ?? '');
    $form_data['email'] = trim($_POST['email'] ?? '');
    $form_data['phone'] = trim($_POST['phone'] ?? '');
    $form_data['project'] = trim($_POST['project'] ?? '');
    $form_data['subject'] = trim($_POST['subject'] ?? '');
    $form_data['message'] = trim($_POST['message'] ?? '');

    if ($form_data['name'] === '') {
        $error = 'Please enter your name.';
    } elseif ($form_data['email'] === '') {
        $error = 'Please enter your email.';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($form_data['message'] === '') {
        $error = 'Please enter your message.';
    } else {
        try {
            $pdo = getDb();
            $stmt = $pdo->prepare('INSERT INTO contactUs (name, email, phone, project, subject, message) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $form_data['name'],
                $form_data['email'],
                $form_data['phone'] ?: null,
                $form_data['project'] ?: null,
                $form_data['subject'] ?: null,
                $form_data['message'],
            ]);
            header('Location: contact.php?sent=1');
            exit;
        } catch (Exception $e) {
            $error = 'Unable to send your message. Please try again later.';
        }
    }
}

if (isset($_GET['sent']) && $_GET['sent'] == '1') {
    $success = true;
}

include __DIR__ . '/includes/header.php';
?>

<!-- Header Start -->
<div class="container-fluid bg-breadcrumb">
    <div class="container text-center py-5" style="max-width: 900px;">
        <h3 class="text-white display-3 mb-4 wow fadeInDown" data-wow-delay="0.1s">Contact Us</h3>
        <ol class="breadcrumb justify-content-center text-white mb-0 wow fadeInDown" data-wow-delay="0.3s">
            <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
            <li class="breadcrumb-item"><a href="#" class="text-white">Pages</a></li>
            <li class="breadcrumb-item active text-secondary">Contact</li>
        </ol>
    </div>
</div>
<!-- Header End -->

<!-- Contact Start -->
<div class="container-fluid contact overflow-hidden py-5">
    <div class="container py-5">
        <?php if ($success): ?>
            <div class="alert alert-success mb-4">Thank you. Your message has been sent successfully. We will get back to you soon.</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <div class="row g-5">
            <div class="col-lg-6 wow fadeInLeft" data-wow-delay="0.1s">
                <div class="sub-style">
                    <h5 class="sub-title text-primary pe-3">Quick Contact</h5>
                </div>
                <h1 class="display-5 mb-4">Have Questions? Don't Hesitate to Contact Us</h1>
                <p class="mb-5">Get in touch for debt recovery services, partnerships, or general enquiries. We're here to help.</p>
                <div class="d-flex border-bottom mb-4 pb-4">
                    <i class="fas fa-map-marked-alt fa-4x text-primary bg-light p-3 rounded"></i>
                    <div class="ps-3">
                        <h5>Location</h5>
                        <p>Swiis Debt Management Pvt. Ltd., Bahowal, C/o Gurdev Singh, Village Mahilpur, Hoshiarpur, Punjab, India - 146105.</p>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-xl-6">
                        <div class="d-flex">
                            <i class="fas fa-tty fa-3x text-primary"></i>
                            <div class="ps-3">
                                <h5 class="mb-3">Quick Contact</h5>
                                <div class="mb-3">
                                    <h6 class="mb-0">Phone:</h6>
                                    <a href="tel:+917527008800" class="fs-5 text-primary">+91-7527008800</a>
                                </div>
                                <div class="mb-3">
                                    <h6 class="mb-0">Email:</h6>
                                    <a href="mailto:info@swiis.in" class="fs-5 text-primary">info@swiis.in</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="d-flex">
                            <i class="fas fa-clock fa-3x text-primary"></i>
                            <div class="ps-3">
                                <h5 class="mb-3">Opening Hrs</h5>
                                <div class="mb-3">
                                    <h6 class="mb-0">Mon - Friday:</h6>
                                    <span class="fs-5 text-primary">09.00 am to 07.00 pm</span>
                                </div>
                                <div class="mb-3">
                                    <h6 class="mb-0">Saturday:</h6>
                                    <span class="fs-5 text-primary">10.00 am to 05.00 pm</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center pt-3">
                    <div class="me-4">
                        <div class="bg-light d-flex align-items-center justify-content-center" style="width: 90px; height: 90px; border-radius: 10px;"><i class="fas fa-share fa-3x text-primary"></i></div>
                    </div>
                    <div class="d-flex">
                        <a class="btn btn-secondary border-secondary me-2 p-0" href="https://www.facebook.com/profile.php?id=61556441472731">facebook <i class="fas fa-chevron-circle-right"></i></a>
                        <a class="btn btn-secondary border-secondary mx-2 p-0" href="https://x.com/swiisconsultant">twitter <i class="fas fa-chevron-circle-right"></i></a>
                        <a class="btn btn-secondary border-secondary mx-2 p-0" href="https://www.linkedin.com/in/swiis-consultants-limited-private-8b6451301">linkedin <i class="fas fa-chevron-circle-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 wow fadeInRight" data-wow-delay="0.3s">
                <div class="sub-style">
                    <h5 class="sub-title text-primary pe-3">Let's Connect</h5>
                </div>
                <h1 class="display-5 mb-4">Send Your Message</h1>
                <p class="lh-base mb-4">Fill in the form below and we'll get back to you as soon as possible.</p>
                <form method="post" action="contact.php">
                    <div class="row g-4">
                        <div class="col-lg-12 col-xl-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="name" name="name" placeholder="Your Name" value="<?= htmlspecialchars($form_data['name']) ?>" required>
                                <label for="name">Your Name</label>
                            </div>
                        </div>
                        <div class="col-lg-12 col-xl-6">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="email" placeholder="Your Email" value="<?= htmlspecialchars($form_data['email']) ?>" required>
                                <label for="email">Your Email</label>
                            </div>
                        </div>
                        <div class="col-lg-12 col-xl-6">
                            <div class="form-floating">
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone" value="<?= htmlspecialchars($form_data['phone']) ?>">
                                <label for="phone">Your Phone</label>
                            </div>
                        </div>
                        <div class="col-lg-12 col-xl-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="project" name="project" placeholder="Project" value="<?= htmlspecialchars($form_data['project']) ?>">
                                <label for="project">Your Project</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject" value="<?= htmlspecialchars($form_data['subject']) ?>">
                                <label for="subject">Subject</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control" placeholder="Leave a message here" id="message" name="message" style="height: 160px" required><?= htmlspecialchars($form_data['message']) ?></textarea>
                                <label for="message">Message</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100 py-3">Send Message</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Contact End -->

<?php include __DIR__ . '/includes/footer.php'; ?>
