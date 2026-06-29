<?php

// Vercel Serverless Function entrypoint for Laravel

// Force HTTPS scheme for all URLs (Vercel terminates SSL before PHP)
$_SERVER['HTTPS'] = 'on';
$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

// Explicitly set env vars that vercel-php may not propagate properly
// These match the values in vercel.json env section
$forceEnv = [
    'CLOUDINARY_URL' => 'cloudinary://833929652849232:qhtMHUhAFkUV6mWS5zx8pOFt2ZU@ddotmjjow',
];
foreach ($forceEnv as $key => $value) {
    if (empty(getenv($key)) && empty($_ENV[$key] ?? '')) {
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

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
