<?php
// =====================================
// laporan.php – Laporan Transaksi + Pagination + Pendapatan Per Bulan
// =====================================
include 'config.php';
include 'header.php';

// ==========================
// FUNGSI FORMAT TANGGAL INDONESIA
// ==========================
function formatTanggal($tgl){
    if(!$tgl) return "-";
    setlocale(LC_TIME, 'id_ID.UTF-8', 'id_ID', 'Indonesian_indonesia.1252');
    return strftime('%A, %d %B %Y', strtotime($tgl));
}

// ==========================
// AMBIL FILTER
// ==========================
$mulai   = $_GET['mulai']   ?? '';
$selesai = $_GET['selesai'] ?? '';
$status  = $_GET['status']  ?? '';
$page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit   = 20; // maksimal 20 transaksi per halaman
$start   = ($page - 1) * $limit;

// ==========================
// ==========================
// HITUNG TOTAL DATA TRANSAKSI
// ==========================
$qCount = "SELECT COUNT(*) AS total FROM sewa s
           JOIN penyewa p ON s.id_penyewa = p.id
           WHERE 1";

if($mulai && $selesai){
    $qCount .= " AND tgl_sewa BETWEEN '$mulai' AND '$selesai'";
}
if($status){
    if($status == 'selesai') {
        $qCount .= " AND status_sewa='kembali'";
    } else {
        $qCount .= " AND status_bayar='$status'";
    }
}

$totalData = $conn->query($qCount)->fetch_assoc()['total'];
$totalPage = ceil($totalData / $limit);

// ==========================
// AMBIL DATA TRANSAKSI
// ==========================
$q = "SELECT 
        s.*, 
        p.nama AS penyewa
      FROM sewa s
      JOIN penyewa p ON s.id_penyewa = p.id
      WHERE 1";

if($mulai && $selesai){
    $q .= " AND tgl_sewa BETWEEN '$mulai' AND '$selesai'";
}
if($status){
    if($status == 'selesai') {
        $q .= " AND status_sewa='kembali'";
    } else {
        $q .= " AND status_bayar='$status'";
    }
}

$q .= " ORDER BY s.id DESC LIMIT $start, $limit";
$tabel = $conn->query($q);

// ==========================
// PENDAPATAN PER BULAN DENGAN PAGINATION
// ==========================
$limitPendapatan = 5; // maksimal 5 bulan per halaman
$pagePendapatan  = isset($_GET['pagePendapatan']) ? (int)$_GET['pagePendapatan'] : 1;
$startPendapatan = ($pagePendapatan - 1) * $limitPendapatan;

// Hitung total bulan
$totalBulanResult = $conn->query("
    SELECT COUNT(DISTINCT YEAR(tgl_sewa), MONTH(tgl_sewa)) AS total
    FROM sewa
    WHERE bayar > 0
    ".($mulai && $selesai ? " AND tgl_sewa BETWEEN '$mulai' AND '$selesai'" : "")
);
$totalBulan = $totalBulanResult->fetch_assoc()['total'];
$totalPagePendapatan = ceil($totalBulan / $limitPendapatan);

// Ambil data pendapatan per bulan dengan LIMIT
$sqlPendapatanLimit = "
    SELECT YEAR(tgl_sewa) AS tahun, MONTH(tgl_sewa) AS bulan, SUM(bayar) AS total
    FROM sewa
    WHERE bayar > 0
    ".($mulai && $selesai ? " AND tgl_sewa BETWEEN '$mulai' AND '$selesai'" : "")."
    GROUP BY YEAR(tgl_sewa), MONTH(tgl_sewa)
    ORDER BY YEAR(tgl_sewa), MONTH(tgl_sewa)
    LIMIT $startPendapatan, $limitPendapatan
";
$resPendapatanLimit = $conn->query($sqlPendapatanLimit);

// Hitung total keseluruhan
$totalPendapatanKeseluruhan = $conn->query("
    SELECT SUM(bayar) AS total
    FROM sewa
    WHERE bayar > 0
    ".($mulai && $selesai ? " AND tgl_sewa BETWEEN '$mulai' AND '$selesai'" : "")
)->fetch_assoc()['total'];

// Nama bulan manual
$namaBulan = [
    1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
    7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
];
?>

<h2 class="title">Laporan Transaksi</h2>

<!-- FILTER -->
<div class="card p-3 mb-4 shadow-sm">
<form method="get">
    <div class="row g-2">
        <div class="col-md-3">
            <label>Mulai</label>
            <input type="date" name="mulai" class="form-control" value="<?=$mulai?>">
        </div>
        <div class="col-md-3">
            <label>Sampai</label>
            <input type="date" name="selesai" class="form-control" value="<?=$selesai?>">
        </div>
        <div class="col-md-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="">Semua</option>
                <option value="belum"   <?=$status=='belum'?'selected':''?>>Belum Lunas</option>
                <option value="lunas"   <?=$status=='lunas'?'selected':''?>>Lunas</option>
                <option value="selesai" <?=$status=='selesai'?'selected':''?>>Selesai</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-primary w-auto d-inline-block"
                    style="height:38px; display:flex; align-items:center; justify-content:center; gap:5px;">
                <i class="bi bi-search"></i> Tampilkan
            </button>
        </div>
    </div>
</form>
</div>

<!-- TABEL TRANSAKSI -->
<div class="card p-3 shadow-sm">
<table class="table table-bordered table-striped align-middle">
    <tr>
        <th>No</th>
        <th>Penyewa</th>
        <th>Baju</th>
        <th>Tgl Sewa</th>
        <th>Tgl Kembali</th>
        <th>Total Harga</th>
        <th>Bayar</th>
        <th>Status</th>
        <th>Keterangan</th>
    </tr>

    <?php $no = $totalData - $start; ?>
    <?php while($row = $tabel->fetch_assoc()){ ?>
    <tr>
        <td><?=$no--?></td>
        <td><?=$row['penyewa']?></td>
        <td><?= $row['nama_baju_list'] ?></td>
        <td><?=formatTanggal($row['tgl_sewa'])?></td>
        <td><?=formatTanggal($row['tgl_kembali'])?></td>
        <td>Rp <?=number_format($row['total_harga'])?></td>
        <td>Rp <?=number_format($row['bayar'])?></td>
        <td>
            <?php 
                if($row['status_sewa']=='kembali'){ 
                    echo '<span class="badge bg-primary">Selesai</span>';
                } elseif($row['status_bayar']=='lunas'){ 
                    echo '<span class="badge bg-success">Lunas</span>'; 
                } else { 
                    echo '<span class="badge bg-danger">Belum Lunas</span>'; 
                }
            ?>
        </td>
        <td><?= $row['keterangan'] ? htmlspecialchars($row['keterangan']) : "-" ?></td>
    </tr>
    <?php } ?>
</table>

<!-- PAGINATION TRANSAKSI -->
<nav>
  <ul class="pagination justify-content-center mt-3">
    <?php
    $start_page = max(1, $page - 2);
    $end_page = min($totalPage, $page + 2);

    if($page > 1){
        echo '<li class="page-item"><a class="page-link" href="?mulai='.$mulai.'&selesai='.$selesai.'&status='.$status.'&page=1">Awal</a></li>';
        echo '<li class="page-item"><a class="page-link" href="?mulai='.$mulai.'&selesai='.$selesai.'&status='.$status.'&page='.($page-1).'">Sebelumnya</a></li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">Awal</span></li>';
        echo '<li class="page-item disabled"><span class="page-link">Sebelumnya</span></li>';
    }

    for($i=$start_page; $i<=$end_page; $i++){
        $active = ($i==$page)?'active':'';
        echo '<li class="page-item '.$active.'"><a class="page-link" href="?mulai='.$mulai.'&selesai='.$selesai.'&status='.$status.'&page='.$i.'">'.$i.'</a></li>';
    }

    if($page < $totalPage){
        echo '<li class="page-item"><a class="page-link" href="?mulai='.$mulai.'&selesai='.$selesai.'&status='.$status.'&page='.($page+1).'">Berikutnya</a></li>';
        echo '<li class="page-item"><a class="page-link" href="?mulai='.$mulai.'&selesai='.$selesai.'&status='.$status.'&page='.$totalPage.'">Akhir</a></li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">Berikutnya</span></li>';
        echo '<li class="page-item disabled"><span class="page-link">Akhir</span></li>';
    }
    ?>
  </ul>
</nav>

<!-- TOTAL PENDAPATAN PER BULAN -->
<div class="card p-3 shadow-sm mt-4">
    <h5>Total Pendapatan Per Bulan</h5>
    <table class="table table-bordered table-striped align-middle">
        <tr>
            <th>Bulan</th>
            <th>Total Pendapatan</th>
        </tr>
        <?php
        while($p = $resPendapatanLimit->fetch_assoc()){
            $bulan = $namaBulan[$p['bulan']] . " " . $p['tahun'];
            echo "<tr>";
            echo "<td>$bulan</td>";
            echo "<td>Rp ".number_format($p['total'])."</td>";
            echo "</tr>";
        }
        ?>
    </table>

    <!-- PAGINATION PENDAPATAN -->
    <nav>
        <ul class="pagination justify-content-center mt-2">
            <?php
            $start_page = max(1, $pagePendapatan - 2);
            $end_page = min($totalPagePendapatan, $pagePendapatan + 2);

            if($pagePendapatan > 1){
                echo '<li class="page-item"><a class="page-link" href="?mulai='.$mulai.'&selesai='.$selesai.'&status='.$status.'&pagePendapatan=1">Awal</a></li>';
                echo '<li class="page-item"><a class="page-link" href="?mulai='.$mulai.'&selesai='.$selesai.'&status='.$status.'&pagePendapatan='.($pagePendapatan-1).'">Sebelumnya</a></li>';
            } else {
                echo '<li class="page-item disabled"><span class="page-link">Awal</span></li>';
                echo '<li class="page-item disabled"><span class="page-link">Sebelumnya</span></li>';
            }

            for($i=$start_page; $i<=$end_page; $i++){
                $active = ($i==$pagePendapatan)?'active':'';
                echo '<li class="page-item '.$active.'"><a class="page-link" href="?mulai='.$mulai.'&selesai='.$selesai.'&status='.$status.'&pagePendapatan='.$i.'">'.$i.'</a></li>';
            }

            if($pagePendapatan < $totalPagePendapatan){
                echo '<li class="page-item"><a class="page-link" href="?mulai='.$mulai.'&selesai='.$selesai.'&status='.$status.'&pagePendapatan='.($pagePendapatan+1).'">Berikutnya</a></li>';
                echo '<li class="page-item"><a class="page-link" href="?mulai='.$mulai.'&selesai='.$selesai.'&status='.$status.'&pagePendapatan='.$totalPagePendapatan.'">Akhir</a></li>';
            } else {
                echo '<li class="page-item disabled"><span class="page-link">Berikutnya</span></li>';
                echo '<li class="page-item disabled"><span class="page-link">Akhir</span></li>';
            }
            ?>
        </ul>
    </nav>

    <h5 class="mt-3 text-success">
        Total Pendapatan Keseluruhan:  
        <b>Rp <?=number_format($totalPendapatanKeseluruhan)?></b>
    </h5>
</div>

<?php include 'footer.php'; ?>
