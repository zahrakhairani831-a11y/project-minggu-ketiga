<?php
require_once __DIR__ . '/../config/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}
verify_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    flash('error', 'ID produk tidak valid.');
    redirect('index.php');
}
$check = $pdo->prepare('SELECT id FROM products WHERE id = :id LIMIT 1');
$check->execute(['id' => $id]);

if (!$check->fetch()) {
    flash('error', 'Produk tidak ditemukan.');
    redirect('index.php');
}
$stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
$stmt->execute(['id' => $id]);
flash('success', 'Produk berhasil dihapus.');
redirect('index.php');