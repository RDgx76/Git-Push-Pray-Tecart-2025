<?php
// Minimal security headers — include this at top of entry scripts
// Example: include __DIR__ . '/security.php';

if (!headers_sent()) {
    // Content Security Policy (very conservative)
    // allow scripts/styles from same origin and trusted sources only.
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; frame-ancestors 'none';");

    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: no-referrer-when-downgrade");
    header("Permissions-Policy: geolocation=(), microphone=()"); // restrict features
    // HSTS — enable only if using HTTPS in production
    if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443) {
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
    }
}
