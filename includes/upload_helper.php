<?php
/**
 * Image upload helper for admin: testimonials and blogs.
 * Only images allowed; max 5MB. Deletes files when record is deleted.
 */

define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_MIMES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('UPLOAD_EXT_BY_MIME', [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
]);

/** @return string Project root path (no trailing slash) */
function upload_base_path(): string {
    return dirname(__DIR__);
}

/**
 * Validate uploaded file is an image and <= 5MB.
 * @return array [bool $ok, string $errorMessage, string|null $mime]
 */
function upload_validate_image(array $file): array {
    if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return [false, 'No file uploaded.', null];
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, UPLOAD_ALLOWED_MIMES, true)) {
        return [false, 'Only images are allowed (JPEG, PNG, GIF, WebP).', null];
    }
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        return [false, 'Image must be 5MB or smaller.', null];
    }
    return [true, '', $mime];
}

/**
 * Save uploaded image to uploads/$subdir/ with a unique name.
 * @param array $file $_FILES['field_name']
 * @param string $subdir e.g. 'testimonials' or 'blogs'
 * @return string|null Relative path (e.g. uploads/testimonials/abc.jpg) or null on failure
 */
function upload_save_image(array $file, string $subdir): ?string {
    list($ok, $err, $mime) = upload_validate_image($file);
    if (!$ok) {
        return null;
    }
    $base = upload_base_path();
    $dir = $base . '/uploads/' . $subdir;
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0755, true)) {
            return null;
        }
    }
    $ext = UPLOAD_EXT_BY_MIME[$mime] ?? 'jpg';
    $name = uniqid($subdir . '_', true) . '.' . $ext;
    $path = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $path)) {
        return null;
    }
    return 'uploads/' . $subdir . '/' . $name;
}

/**
 * Delete image file if it belongs to our uploads (testimonials or blogs). Safe to call with null/empty.
 */
function upload_delete_image(?string $relativePath): void {
    if ($relativePath === null || $relativePath === '') {
        return;
    }
    $relativePath = str_replace('\\', '/', trim($relativePath));
    if (strpos($relativePath, '..') !== false) {
        return;
    }
    $allowedPrefixes = ['uploads/testimonials/', 'uploads/blogs/'];
    $ok = false;
    foreach ($allowedPrefixes as $prefix) {
        if (strpos($relativePath, $prefix) === 0) {
            $ok = true;
            break;
        }
    }
    if (!$ok) {
        return;
    }
    $full = upload_base_path() . '/' . $relativePath;
    if (is_file($full)) {
        @unlink($full);
    }
}
