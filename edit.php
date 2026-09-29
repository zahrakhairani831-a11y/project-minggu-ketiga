<?php
require_once __DIR__ . '/../config/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    flash('error', 'ID produk tidak valid.');
    redirect('index.php');
}
$stmt = $pdo->prepare('SELECT id, name, category, price, stock FROM products WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    flash('error', 'Produk tidak ditemukan.');
    redirect('index.php');
}
$errors = [];
$old = [
    'name' => $product['name'],
    'category' => $product['category'],
    'price' => $product['price'],
    'stock' => $product['stock'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $old['name'] = trim($_POST['name'] ?? '');
    $old['category'] = trim($_POST['category'] ?? '');
    $old['price'] = trim($_POST['price'] ?? '');
    $old['stock'] = trim($_POST['stock'] ?? '');
    $nameLength = function_exists('mb_strlen') ? mb_strlen($old['name']) : strlen($old['name']);
    if ($nameLength < 3) {
        $errors[] = 'Nama produk minimal 3 karakter.';
    }
    if ($old['category'] === '') {
        $errors[] = 'Kategori wajib diisi.';
    }
    if (!is_numeric($old['price']) || (float) $old['price'] <= 0 || (float) $old['price'] > 9999999999.99) {
        $errors[] = 'Harga harus berupa angka, lebih besar dari 0, dan maksimal 9.999.999.999.';
    }
    $stockRule = ['options' => ['min_range' => 0, 'max_range' => 2147483647]];
    if (filter_var($old['stock'], FILTER_VALIDATE_INT, $stockRule) === false) {
        $errors[] = 'Stok harus berupa bilangan bulat, tidak negatif, dan maksimal 2.147.483.647.';
    }
    if (!$errors) {
        $check = $pdo->prepare('SELECT id FROM products WHERE name = :name AND id <> :id LIMIT 1');
        $check->execute(['name' => $old['name'], 'id' => $id]);
        if ($check->fetch()) {
            $errors[] = 'Nama produk sudah digunakan oleh produk lain.';
        }
    }
    if (!$errors) {
        $update = $pdo->prepare(
            'UPDATE products
             SET name = :name, category = :category, price = :price, stock = :stock
             WHERE id = :id'
        );
        $update->execute([
            'name' => $old['name'],
            'category' => $old['category'],
            'price' => number_format((float) $old['price'], 2, '.', ''),
            'stock' => (int) $old['stock'],
            'id' => $id,
        ]);
        flash('success', 'Produk berhasil diperbarui.');
        redirect('index.php');
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tugas Week 3</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="page-shell narrow-shell">
    <header class="topbar">
        <a class="brand" href="index.php">
            <span class="brand-mark">ZK</span>
            <span><strong>Zahra Khairani</strong><small>250180073<?= (int) $id ?></small></span>
        </a>
        <a class="button button-secondary" href="index.php">← Kembali</a>
    </header>
    <main class="form-page">
        <div class="form-heading">
            <h1>Edit Produk</h1>
        </div>
        <?php if ($errors): ?>
            <div class="alert alert-error" role="alert">
                <strong>Periksa kembali input:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form class="form-card" method="post" action="edit.php?id=<?= (int) $id ?>" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="field">
                <label for="name">Nama Produk <span>*</span></label>
                <input id="name" name="name" type="text" maxlength="100" value="<?= e($old['name']) ?>" required autofocus>
                <small>Minimal 3 karakter dan harus unik.</small>
            </div>
            <div class="field">
                <label for="category">Kategori <span>*</span></label>
                <input id="category" name="category" type="text" maxlength="50" value="<?= e($old['category']) ?>" required>
            </div>
            <div class="field-grid">
                <div class="field">
                    <label for="price">Harga <span>*</span></label>
                    <div class="input-prefix"><span>Rp</span><input id="price" name="price" type="number" min="0.01" step="0.01" value="<?= e($old['price']) ?>" required></div>
                    <small>Harus lebih besar dari 0.</small>
                </div>
                <div class="field">
                    <label for="stock">Stok <span>*</span></label>
                    <input id="stock" name="stock" type="number" min="0" step="1" value="<?= e($old['stock']) ?>" required>
                    <small>Tidak boleh bernilai negatif.</small>
                </div>
            </div>
            <div class="form-actions">
                <a class="button button-secondary" href="index.php">Batal</a>
                <button class="button button-primary" type="submit">Simpan Perubahan</button>
            </div>
        </form>
    </main>
</div>
</body>
</html>