<?php
ob_start(); // ---- Mencegah error "Cannot modify header"

include 'config.php';
include 'header.php';

// Cek ID
if (!isset($_GET['id'])) {
    header("Location: penyewa.php");
    exit;
}

$id = (int)$_GET['id'];

// Ambil data penyewa
$penyewa = $conn->query("SELECT * FROM penyewa WHERE id=$id")->fetch_assoc();

// Jika data tidak ditemukan
if (!$penyewa) {
    header("Location: penyewa.php");
    exit;
}

// Update data
if (isset($_POST['update'])) {
    $nama   = $conn->real_escape_string($_POST['nama']);
    $telp   = $conn->real_escape_string($_POST['telp']);
    $alamat = $conn->real_escape_string($_POST['alamat']);

    $conn->query("
        UPDATE penyewa SET 
            nama='$nama', 
            telp='$telp', 
            alamat='$alamat' 
        WHERE id=$id
    ");

    header("Location: penyewa.php");
    exit;
}
?>

<h2 class="title">Edit Penyewa</h2>

<div class="card p-3 shadow-sm">
    <form method="post">
        <div class="row g-2">
            <div class="col-md-3">
                <input type="text" name="nama" class="form-control" 
                       value="<?= htmlspecialchars($penyewa['nama']) ?>" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="telp" class="form-control" 
                       value="<?= htmlspecialchars($penyewa['telp']) ?>" required>
            </div>
            <div class="col-md-4">
                <input type="text" name="alamat" class="form-control" 
                       value="<?= htmlspecialchars($penyewa['alamat']) ?>" required>
            </div>
            <div class="col-md-2">
                <button <button class="btn btn-primary px-3" name="update">
                    <i class="bi bi-check-circle"></i> Update Data
                </button>
            </div>
        </div>
    </form>
</div>

<?php 
include 'footer.php'; 
ob_end_flush(); // ---- Tutup buffer
?>
