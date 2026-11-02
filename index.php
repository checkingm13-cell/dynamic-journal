<?php
/**
 * Unified Front Controller & Dynamic Journal Route Dispatcher
 */

declare(strict_types=1);
define('WWJ_EXEC', true);

require_once __DIR__ . '/config.php';

// If journal is not configured/locked, redirect directly to setup gate
if (!$current_journal) {
    header("Location: /admin/setup.php");
    exit;
}

$active_key = $current_journal['key']; // 'gjra', 'paripex', 'ijsr', 'ijar'
$template_base = __DIR__ . "/views/journals/{$active_key}";

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = rtrim($uri, '/');

// Clean Route Resolution for the 3 Target Contracts per active journal
switch (true) {
    case ($uri === '' || $uri === '/index.php' || strpos($uri, 'upload-your-article') !== false):
        $file = "{$template_base}/upload_form.php";
        if (file_exists($file)) {
            require $file;
        } else {
            require __DIR__ . "/views/journals/ijar/upload_form.php";
        }
        break;

    case (strpos($uri, 'author-guidelines') !== false):
        $file = "{$template_base}/guidelines.php";
        if (file_exists($file)) {
            require $file;
        } else {
            require __DIR__ . "/views/journals/ijar/guidelines.php";
        }
        break;

    case (strpos($uri, 'downloads') !== false):
        $file = "{$template_base}/downloads.php";
        if (file_exists($file)) {
            require $file;
        } else {
            require __DIR__ . "/views/journals/ijar/downloads.php";
        }
        break;

    case (strpos($uri, 'admin/setup') !== false):
        require __DIR__ . '/admin/setup.php';
        break;

    default:
        header("Location: /page/u/upload-your-article");
        exit;
}
