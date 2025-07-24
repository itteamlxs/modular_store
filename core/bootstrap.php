<?php
declare(strict_types=1);

use Dotenv\Dotenv;
use HtmlSanitizer\Sanitizer;

require_once __DIR__ . '/../vendor/autoload.php';

# Load env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

# Basic runtime config
ini_set('display_errors', $_ENV['APP_ENV'] === 'development' ? '1' : '0');
error_reporting($_ENV['APP_ENV'] === 'development' ? E_ALL : 0);

# Start secure session
session_start([
    'cookie_secure'   => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);

# Global input sanitizer
function sanitize(string $dirty, string $context = 'string'): string
{
    static $sanitizer;
    if (!$sanitizer) {
        $sanitizer = Sanitizer::create(['extensions' => ['basic']]);
    }
    return $sanitizer->sanitize($dirty);
}