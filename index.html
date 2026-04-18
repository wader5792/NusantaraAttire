<?php
include 'config.php';
include 'header.php';

// Total stok baju
$total_stok = $conn->query("SELECT SUM(stok) AS total FROM baju")->fetch_assoc()['total'] ?? 0;

// Baju sedang disewa (belum kembali)
$baju_keluar = $conn->query("
    SELECT COUNT(*) AS total 
    FROM sewa 
    WHERE status_sewa='dipinjam'
")->fetch_assoc()['total'] ?? 0;

// Total baju
$total_baju = $total_stok + $baju_keluar;

// Baju ready
$baju_ready = $conn->query("
    SELECT SUM(stok) AS total 
    FROM baju 
    WHERE stok > 0
")->fetch_assoc()['total'] ?? 0;

// Total transaksi
$total_sewa = $conn->query("SELECT COUNT(*) AS jml FROM sewa")->fetch_assoc()['jml'] ?? 0;
?>

<h2 class="title">Dashboard</h2>

<style>
.icon-box {
    font-size: 38px;
    color: #8B5E3C;
    margin-bottom: 8px;
    opacity: .85;
}

.card {
    border-radius: 14px !important;
    border: 1px solid #e8e2db;
}

.card:hover {
    background: #faf7f3;
    transform: translateY(-3px);
    transition: 0.25s;
}
</style>

<div class="row g-3">

    <!-- TOTAL BAJU -->
    <div class="col-md-3">
        <div class="card p-3 shadow-sm text-center">
            <div class="icon-box">
                <i class="bi bi-boxes"></i>
            </div>
            <h6 class="fw-semibold">Total Baju</h6>
            <p class="fs-2 fw-bold"><?= $total_baju ?></p>
        </div>
    </div>

    <!-- BAJU READY -->
    <div class="col-md-3">
        <div class="card p-3 shadow-sm text-center">
            <div class="icon-box">
                <i class="bi bi-check2-circle"></i>
            </div>
            <h6 class="fw-semibold">Baju Ready</h6>
            <p class="fs-2 fw-bold"><?= $baju_ready ?></p>
        </div>
    </div>

    <!-- BAJU KELUAR -->
    <div class="col-md-3">
        <div class="card p-3 shadow-sm text-center">
            <div class="icon-box">
                <i class="bi bi-arrow-up-circle"></i>
            </div>
            <h6 class="fw-semibold">Baju Keluar</h6>
            <p class="fs-2 fw-bold"><?= $baju_keluar ?></p>
        </div>
    </div>

    <!-- TOTAL TRANSAKSI -->
    <div class="col-md-3">
        <div class="card p-3 shadow-sm text-center">
            <div class="icon-box">
                <i class="bi bi-journal-text"></i>
            </div>
            <h6 class="fw-semibold">Total Transaksi</h6>
            <p class="fs-2 fw-bold"><?= $total_sewa ?></p>
        </div>
    </div>

</div>

<?php include 'footer.php'; ?>
