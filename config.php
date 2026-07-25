<?php
// config.php — shared configuration, session bootstrap, and constants
declare(strict_types=1);

// Compress responses on the wire
if (!ob_get_level() && extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
    ob_start('ob_gzhandler');
}

session_start();

define('DATA_FILE', __DIR__ . '/database.json');
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('DEFAULT_PASSWORD', 'neural60f');
define('DAYS', ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']);

/**
 * Returns true if the current session is authenticated as admin.
 */
function isAdmin(): bool {
    return !empty($_SESSION['isAdmin']);
}

/** Require admin session or stop with a JSON 403 error. */
function requireAdmin(): void {
    if (!isAdmin()) {
        jsonResponse(['ok' => false, 'error' => 'Admin authorization required.'], 403);
    }
}

/** Send a JSON response and terminate. */
function jsonResponse(array $payload, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/** Simple unique id generator. */
function makeId(string $prefix): string {
    return $prefix . '_' . bin2hex(random_bytes(4));
}

/** Trim + coerce to string, safe for arbitrary input. */
function s($v): string {
    return is_string($v) ? trim($v) : (is_scalar($v) ? trim((string)$v) : '');
}

/**
 * Saves base64 data URIs to files if present.
 */
function saveDataUriIfPresent(string $value, string $subdir, string $id): string {
    if (!preg_match('/^data:image\/([a-zA-Z0-9+.\-]+);[^,]*base64,(.*)$/s', $value, $m)) {
        return $value;
    }
    $ext = strtolower($m[1]);
    if ($ext === 'jpeg') $ext = 'jpg';
    if (!in_array($ext, ['jpg', 'png', 'gif', 'webp'], true)) $ext = 'jpg';

    $raw = base64_decode($m[2], true);
    if ($raw === false) return '';

    $dir = UPLOAD_DIR . '/' . $subdir;
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $path = $dir . '/' . $id . '.' . $ext;
    file_put_contents($path, $raw);

    return 'uploads/' . $subdir . '/' . $id . '.' . $ext;
}