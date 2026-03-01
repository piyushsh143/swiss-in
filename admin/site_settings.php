<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$pdo = getDb();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save_settings') {
        $email = trim($_POST['contact_email'] ?? '');
        $phone = trim($_POST['contact_phone'] ?? '');
        $hours = trim($_POST['business_hours'] ?? '');
        $stmt = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        $stmt->execute(['contact_email', $email]);
        $stmt->execute(['contact_phone', $phone]);
        $stmt->execute(['business_hours', $hours]);
        $_SESSION['flash'] = 'Contact settings saved.';
        header('Location: site_settings');
        exit;
    }
    if ($action === 'delete_office') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare('DELETE FROM office_addresses WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = 'Office address deleted.';
        }
        header('Location: site_settings');
        exit;
    }
}

$settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('contact_email','contact_phone','business_hours')");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$contact_email = $settings['contact_email'] ?? 'info@swiis.in';
$contact_phone = $settings['contact_phone'] ?? '+91-7527008800';
$business_hours = $settings['business_hours'] ?? "Monday - Friday: 09:00 AM to 07:00 PM\nSaturday: 10:00 AM to 05:00 PM\nSunday: Closed";

$offices = $pdo->query('SELECT * FROM office_addresses ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Site Settings - Swiis Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .sidebar { min-height: 100vh; background: #1a1a2e; }
        .sidebar a { color: rgba(255,255,255,.8); text-decoration: none; padding: 12px 16px; display: block; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background: rgba(255,255,255,.1); }
        .btn-icon { padding: 0.25rem 0.5rem; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 sidebar">
                <div class="py-3 px-3 text-white fw-bold">Swiis Admin</div>
                <a href="dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a href="testimonials"><i class="bi bi-chat-quote me-2"></i>Testimonials</a>
                <a href="blogs"><i class="bi bi-journal-text me-2"></i>Blogs</a>
                <a href="partners"><i class="bi bi-people me-2"></i>Clients</a>
                <a href="geographies"><i class="bi bi-geo-alt me-2"></i>Geographies</a>
                <a href="contacts"><i class="bi bi-envelope me-2"></i>Contact Us</a>
                <a href="site_settings" class="active"><i class="bi bi-gear me-2"></i>Site Settings</a>
                <hr class="border-secondary">
                <a href="logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </nav>
            <main class="col-md-10 py-4">
                <h1 class="mb-4">Site Settings</h1>
                <?php if ($flash): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
                <?php endif; ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Contact Info & Business Hours</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="action" value="save_settings">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="contact_email" value="<?= htmlspecialchars($contact_email) ?>" placeholder="info@example.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control" name="contact_phone" value="<?= htmlspecialchars($contact_phone) ?>" placeholder="+91-XXXXXXXXXX">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Business Hours</label>
                                    <textarea class="form-control" name="business_hours" rows="5" placeholder="e.g. Monday - Friday: 09:00 AM to 07:00 PM"><?= htmlspecialchars($business_hours) ?></textarea>
                                    <small class="text-muted">One line per schedule. Shown on footer and contact page.</small>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Save contact info & hours</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Office Addresses</h5>
                        <a href="office-edit" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i> Add address</a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($offices)): ?>
                            <p class="text-muted p-4 mb-0">No office addresses yet. <a href="office-edit">Add one</a>.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Title</th>
                                            <th>Address</th>
                                            <th>Order</th>
                                            <th width="100">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($offices as $o): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($o['title']) ?></td>
                                                <td><span class="text-muted"><?= htmlspecialchars(mb_substr(str_replace("\n", ' ', $o['address']), 0, 60)) ?>...</span></td>
                                                <td><?= (int) $o['sort_order'] ?></td>
                                                <td>
                                                    <a href="office-edit?id=<?= (int) $o['id'] ?>" class="btn btn-sm btn-outline-primary btn-icon" title="Edit"><i class="bi bi-pencil"></i></a>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this office address?');">
                                                        <input type="hidden" name="action" value="delete_office">
                                                        <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" title="Delete"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
