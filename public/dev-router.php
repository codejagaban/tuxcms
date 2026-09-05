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

if ($candidate && is_dir($candidate) && is_file($candidate . '/index.html')) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile($candidate . '/index.html');
    return true;
}

if ($candidate && is_file($candidate)) {
    return false; // let the server handle the static file
}

$html = $root . rtrim($path, '/') . '/index.html';
if (is_file($html)) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile($html);
    return true;
}

if (is_file($root . '/404.html')) {
    http_response_code(404);
    readfile($root . '/404.html');
    return true;
}

return false;
