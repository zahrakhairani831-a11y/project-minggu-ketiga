<?php
require_once __DIR__ . '/../config/db.php';
$q = trim((string) filter_input(INPUT_GET, 'q'));
$stmt = $pdo->prepare(
    'SELECT id, name, category, price, stock, created_at
     FROM products
     WHERE name LIKE :q1 OR category LIKE :q2
     ORDER BY id DESC'
);
$stmt->execute(['q1' => "%$q%", 'q2' => "%$q%"]);
$products = $stmt->fetchAll();
$stats = $pdo->query('SELECT COUNT(*) AS total, COALESCE(SUM(stock), 0) AS stock FROM products')->fetch();
$totalProducts = (int) $stats['total'];
$totalStock = (int) $stats['stock'];
$flash = get_flash();
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
<div class="page-shell">
    <header class="topbar">
        <a class="brand" href="index.php" aria-label="Product Manager">
            <span class="brand-mark">ZK</span>
            <span>
                <strong>Zahra Khairani</strong>
                <small>250180073</small>
            </span>
        </a>
        <a class="button button-primary" href="create.php">
            <span class="button-icon">+</span> Tambah Produk
        </a>
    </header>
    <main>
        <section class="hero">
            <h1>PRODUK MANAGER</h1>
            <div class="stats">
                <div class="stat-card">
                    <span class="stat-label">Total Produk</span>
                    <strong><?= $totalProducts ?></strong>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Total Stok</span>
                    <strong><?= $totalStock ?></strong>
                </div>
            </div>
        </section>
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" role="status">
                <span class="alert-dot"></span>
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>
        <section class="section-heading">
            <div>
                <p class="eyebrow">DAFTAR PRODUK</p>
                <h2>Produk Toko <span class="product-count"><?= count($products) ?> item</span></h2>
            </div>
            <form class="search-form" method="get" action="index.php">
                <input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari nama atau kategori...">
                <button class="button button-primary" type="submit">Cari</button>
            </form>
        </section>
        <?php if (!$products): ?>
            <section class="empty-state">
                <?php if ($q !== ''): ?>
                    <div class="empty-icon">?</div>
                    <h3>Produk tidak ditemukan</h3>
                    <p>Tidak ada hasil untuk "<?= e($q) ?>".</p>
                    <a class="button button-secondary" href="index.php">Reset pencarian</a>
                <?php else: ?>
                    <div class="empty-icon">+</div>
                    <h3>Belum ada produk</h3>
                    <p>Mulai dengan menambahkan produk pertama ke database.</p>
                    <a class="button button-primary" href="create.php">Tambah Produk</a>
                <?php endif; ?>
            </section>
        <?php else: ?>
            <section class="product-grid" aria-label="Daftar produk">
                <?php foreach ($products as $product): ?>
                    <article class="product-card">
                        <div class="product-card-top">
                            <span class="category-pill">
                                <?= e($product['category']) ?>
                            </span>
                            <span class="product-id">
                                #<?= (int) $product['id'] ?>
                            </span>
                        </div>
                        <h3><?= e($product['name']) ?></h3>
                        <div class="price-row">
                            <span>Harga</span>
                            <strong>
                                Rp <?= number_format((float) $product['price'], 0, ',', '.') ?>
                            </strong>
                        </div>
                        <div class="stock-row">
                            <span>Stok</span>
                            <strong>
                                <?= (int) $product['stock'] ?> unit
                            </strong>
                        </div>
                        <div class="card-divider"></div>
                        <div class="card-actions">
                            <a class="button button-secondary" href="edit.php?id=<?= (int) $product['id'] ?>">Edit</a>
                            <form
                                action="delete.php"
                                method="post"
                                class="inline-form delete-form"
                                data-name="<?= e($product['name']) ?>">
                                <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <button class="button button-danger" type="button" onclick="openDelete(this)">Hapus</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
    <footer class="footer">
        <span>PEMROGRAMAN WEB • PERTEMUAN 3</span>
    </footer>
</div>
<div class="modal" id="deleteModal">
    <div class="modal-box">
        <h3>Hapus produk?</h3>
        <p>Yakin ingin menghapus<strong id="deleteName"></strong>?</p>
        <div class="modal-actions">
            <button
                type="button"
                class="button button-secondary"
                onclick="closeDelete()">Batal</button>
            <button
                type="button"
                class="button button-danger"
                onclick="confirmDelete()">Hapus</button>
        </div>
    </div>
</div>
<script>
let deleteForm = null;
function openDelete(button) {
    deleteForm = button.closest('.delete-form');

    document.getElementById('deleteName').textContent =
        deleteForm.dataset.name;

    document.getElementById('deleteModal').classList.add('show');
}
function closeDelete() {
    document.getElementById('deleteModal').classList.remove('show');
    deleteForm = null;
}
function confirmDelete() {
    if (deleteForm) {
        deleteForm.submit();
    }
}
</script>
</body>
</html>