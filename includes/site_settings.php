<?php
/**
 * Load site contact settings and office addresses for frontend use.
 * Sets: $site_email, $site_phone, $site_business_hours, $office_addresses
 */
if (isset($site_email)) {
    return;
}
$site_email = 'info@swiis.in';
$site_phone = '+91-7527008800';
$site_business_hours = "Monday - Friday: 09:00 AM to 07:00 PM\nSaturday: 10:00 AM to 05:00 PM\nSunday: Closed";
$office_addresses = [];

try {
    if (!function_exists('getDb')) {
        require_once __DIR__ . '/../config/database.php';
    }
    $pdo = getDb();
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('contact_email','contact_phone','business_hours')");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['setting_key'] === 'contact_email') $site_email = (string) $row['setting_value'];
        if ($row['setting_key'] === 'contact_phone') $site_phone = (string) $row['setting_value'];
        if ($row['setting_key'] === 'business_hours') $site_business_hours = (string) $row['setting_value'];
    }
    $office_addresses = $pdo->query('SELECT id, title, address, sort_order FROM office_addresses ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $office_addresses = [];
}
