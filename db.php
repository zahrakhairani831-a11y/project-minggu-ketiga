<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$host = 'localhost';
$dbname = 'store_db';
$username = 'root';
$password = '';
$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Koneksi database gagal. Pastikan MySQL XAMPP aktif dan database <strong>store_db</strong> sudah tersedia.');
}
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}
function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (!$sessionToken || !$token || !hash_equals($sessionToken, $token)) {
        http_response_code(403);
        exit('Permintaan ditolak: CSRF token tidak valid.');
    }
}
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}
function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}