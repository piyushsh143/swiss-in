<?php
/**
 * One-time setup: create database swiis_db, tables, and default admin.
 * Default login: username = admin, password = admin123
 * DELETE or protect this folder after running once.
 */
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'swiis_db';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(64) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS testimonials (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            author_name VARCHAR(128) NOT NULL,
            profession VARCHAR(128) DEFAULT NULL,
            content TEXT NOT NULL,
            image_path VARCHAR(255) DEFAULT NULL,
            rating TINYINT UNSIGNED DEFAULT 5,
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS blogs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            content LONGTEXT NOT NULL,
            excerpt TEXT DEFAULT NULL,
            featured_image VARCHAR(255) DEFAULT NULL,
            is_published TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS contactUs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(64) DEFAULT NULL,
            project VARCHAR(255) DEFAULT NULL,
            subject VARCHAR(255) DEFAULT NULL,
            message TEXT NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS partners (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            image_path VARCHAR(255) DEFAULT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS geographies (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(128) NOT NULL,
            state_code VARCHAR(20) DEFAULT NULL,
            coverage_type ENUM('telecalling','field','both') NOT NULL DEFAULT 'both',
            sort_order INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS site_settings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(64) NOT NULL UNIQUE,
            setting_value TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS office_addresses (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            address TEXT NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT IGNORE INTO admins (username, password_hash) VALUES (?, ?)');
    $stmt->execute(['admin', $hash]);

    $stmtSet = $pdo->prepare('INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)');
    foreach ([
        ['contact_email', 'info@swiis.in'],
        ['contact_phone', '+91-7527008800'],
        ['business_hours', "Monday - Friday: 09:00 AM to 07:00 PM\nSaturday: 10:00 AM to 05:00 PM\nSunday: Closed"],
    ] as $row) {
        $stmtSet->execute($row);
    }
    if ($pdo->query('SELECT COUNT(*) FROM office_addresses')->fetchColumn() == 0) {
        $pdo->prepare('INSERT INTO office_addresses (title, address, sort_order) VALUES (?, ?, ?)')->execute([
            'Head Office',
            "Swiis Debt Management Pvt. Ltd., Bahowal, C/o Gurdev Singh, Village Mahilpur, Hoshiarpur, Punjab, India - 146105",
            1
        ]);
    }
    if ($pdo->query('SELECT COUNT(*) FROM geographies')->fetchColumn() == 0) {
        $defaultGeographies = [
            ['Punjab', 'IN-PB', 'both', 1],
            ['Himachal Pradesh', 'IN-HP', 'both', 2],
            ['Rajasthan', 'IN-RJ', 'both', 3],
            ['Haryana', 'IN-HR', 'both', 4],
        ];
        $stmtGeo = $pdo->prepare('INSERT INTO geographies (name, state_code, coverage_type, sort_order) VALUES (?, ?, ?, ?)');
        foreach ($defaultGeographies as $g) {
            $stmtGeo->execute($g);
        }
    }

    echo '<h1>Setup complete</h1><p>Database <strong>swiis_db</strong> and tables created. Default admin: <strong>admin</strong> / <strong>admin123</strong>. <a href="../admin/">Go to admin login</a>. Delete or protect the <code>install/</code> folder.</p>';
} catch (Exception $e) {
    echo '<h1>Error</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
}
