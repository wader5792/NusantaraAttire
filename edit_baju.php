<?php
include 'config.php';
include 'header.php';

// Pastikan ada ID
if (!isset($_GET['id'])) {
    echo "<script>window.location='baju.php';</script>";
    exit;
}

$id = (int)$_GET['id'];

// Ambil data baju
$baju = $conn->query("SELECT * FROM baju WHERE id=$id")->fetch_assoc();
if (!$baju) {
    echo "<script>window.location='baju.php';</script>";
    exit;
}

// Update data
if (isset($_POST['update'])) {

    $nama        = $conn->real_escape_string(trim($_POST['nama']));
    $kategori    = $conn->real_escape_string(trim($_POST['kategori']));
    $jenis       = $conn->real_escape_string(trim($_POST['jenis']));
    $ukuran      = $conn->real_escape_string(trim($_POST['ukuran']));
    $harga       = (int)$_POST['harga'];
    $stok        = (int)$_POST['stok'];
    $kelengkapan = $conn->real_escape_string(trim($_POST['kelengkapan']));

    if ($nama && $kategori && $jenis && $ukuran && $harga >= 0 && $stok >= 0) {

        $conn->query("
            UPDATE baju SET 
                nama='$nama',
                kategori='$kategori',
                jenis='$jenis',
                ukuran='$ukuran',
                harga='$harga',
                stok='$stok',
                kelengkapan='$kelengkapan'
            WHERE id=$id
        ");

        echo "<script>
                alert('Data berhasil diperbarui!');
                window.location='baju.php';
              </script>";
        exit;
    } else {
        echo "<script>alert('Pastikan semua field terisi dan harga/stok ≥ 0');</script>";
    }
}
?>

<h2 class="title">Edit Data Baju</h2>

<div class="card p-3 shadow-sm">
    <form method="post">
        <div class="row g-2">

            <!-- Nama Baju -->
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label">Nama Baju</label>
                <input type="text" name="nama" class="form-control" 
                       value="<?= htmlspecialchars($baju['nama']) ?>" required>
            </div>

            <!-- Kategori -->
            <div class="col-6 col-sm-4 col-md-2">
                <label class="form-label">Kategori</label>
                <select name="kategori" class="form-control" required>
                    <option value="Anak-anak" <?= $baju['kategori']=='Anak-anak'?'selected':'' ?>>Anak-anak</option>
                    <option value="Dewasa" <?= $baju['kategori']=='Dewasa'?'selected':'' ?>>Dewasa</option>
                </select>
            </div>

            <!-- Jenis -->
            <div class="col-6 col-sm-4 col-md-2">
                <label class="form-label">Jenis</label>
                <select name="jenis" class="form-control" required>
                    <option value="Laki-laki" <?= $baju['jenis']=='Laki-laki'?'selected':'' ?>>Laki-laki</option>
                    <option value="Perempuan" <?= $baju['jenis']=='Perempuan'?'selected':'' ?>>Perempuan</option>
                    <option value="Unisex" <?= $baju['jenis']=='Unisex'?'selected':'' ?>>Unisex</option>
                </select>
            </div>

            <!-- Ukuran -->
            <div class="col-6 col-sm-4 col-md-2">
                <label class="form-label">Ukuran</label>
                <input type="text" name="ukuran" class="form-control" 
                       value="<?= htmlspecialchars($baju['ukuran']) ?>" required>
            </div>

            <!-- Harga -->
            <div class="col-6 col-sm-3 col-md-1">
                <label class="form-label">Harga</label>
                <input type="number" name="harga" class="form-control"
                       value="<?= $baju['harga'] ?>" min="0" required>
            </div>

            <!-- Stok -->
            <div class="col-6 col-sm-3 col-md-1">
                <label class="form-label">Stok</label>
                <input type="number" name="stok" class="form-control"
                       value="<?= $baju['stok'] ?>" min="0" required>
            </div>

            <!-- Kelengkapan -->
            <div class="col-12 col-sm-12 col-md-4">
                <label class="form-label">Kelengkapan</label>
                <input type="text" name="kelengkapan" class="form-control"
                    value="<?= htmlspecialchars($baju['kelengkapan']) ?>" 
                    placeholder="Contoh: Ikat pinggang, sabuk, kalung, dll">
            </div>


            <!-- Tombol -->
            <div class="col-12 mt-2">
                <button type="submit" class="btn btn-primary px-3" name="update">
                    <i class="bi bi-check2-circle"></i> Update Data
                </button>
            </div>

        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
