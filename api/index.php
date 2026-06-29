<?php

// Vercel Serverless Function entrypoint for Laravel

// Force HTTPS scheme for all URLs (Vercel terminates SSL before PHP)
$_SERVER['HTTPS'] = 'on';
$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
$_SERVER['SERVER_PORT'] = 443;

// Serve static files from public/build/ directly
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (preg_match('/^\/build\//', $uri)) {
    $file = __DIR__ . '/../public' . $uri;
    if (file_exists($file) && is_file($file)) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mimeTypes = [
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'map'  => 'application/json',
            'woff' => 'font/woff',
            'woff2'=> 'font/woff2',
            'ttf'  => 'font/ttf',
            'svg'  => 'image/svg+xml',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'ico'  => 'image/x-icon',
        ];
        $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $contentType);
        header('Cache-Control: public, max-age=31536000, immutable');
        readfile($file);
        exit;
    }
}

require __DIR__ . '/../public/index.php';
