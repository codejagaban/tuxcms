<?php
/**
 * Router for `php -S` local previews only — never used in production.
 *
 * PHP's built-in server prefers index.php over index.html, while our .htaccess
 * sets `DirectoryIndex index.html index.php`. This reproduces the production
 * ordering so what you see locally matches what LiteSpeed will serve.
 */
$root = __DIR__;
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$candidate = realpath($root . $path);

// The generated pages normally sit in this directory. When the site is built
// somewhere else (SITE_OUTPUT_PATH, as the demo does), read the pages from
// there and keep everything else, the dashboard, assets and API, here.
$site = rtrim(getenv('SITE_OUTPUT_PATH') ?: $root, '/');

// Uploads normally arrive through the public/storage link. When they are kept
// somewhere else (PUBLIC_DISK_ROOT, as the demo does), serve them from there.
$uploads = getenv('PUBLIC_DISK_ROOT');
if ($uploads && str_starts_with($path, '/storage/')) {
    $file = realpath($uploads . substr($path, strlen('/storage')));
    if ($file && is_file($file) && str_starts_with($file, realpath($uploads) . DIRECTORY_SEPARATOR)) {
        header('Content-Type: ' . (mime_content_type($file) ?: 'application/octet-stream'));
        readfile($file);
        return true;
    }
    http_response_code(404);
    return true;
}

// Laravel owns only these, exactly as the .htaccess does.
if (preg_match('#^/(api|up)(/|$)#', $path)) {
    return false;
}

// The dashboard SPA does its own routing below /admin.
if (str_starts_with($path, '/admin') && !($candidate && is_file($candidate))) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile($root . '/admin/index.html');
    return true;
}

if ($site === $root && $candidate && is_dir($candidate) && is_file($candidate . '/index.html')) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile($candidate . '/index.html');
    return true;
}

if ($candidate && is_file($candidate)) {
    return false; // let the server handle the static file
}

// Never follow `..` out of the site directory.
$html = realpath($site . rtrim($path, '/') . '/index.html');
if ($html && str_starts_with($html, realpath($site) . DIRECTORY_SEPARATOR)) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile($html);
    return true;
}

$other = realpath($site . $path);
if ($site !== $root && $other && is_file($other) && str_starts_with($other, realpath($site) . DIRECTORY_SEPARATOR)) {
    $types = ['xml' => 'application/xml', 'txt' => 'text/plain; charset=UTF-8', 'html' => 'text/html; charset=UTF-8'];
    header('Content-Type: ' . ($types[pathinfo($other, PATHINFO_EXTENSION)] ?? 'application/octet-stream'));
    readfile($other);
    return true;
}

if (is_file($site . '/404.html')) {
    http_response_code(404);
    header('Content-Type: text/html; charset=UTF-8');
    readfile($site . '/404.html');
    return true;
}

return false;
