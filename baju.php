<?php
// =====================================
// baju.php - Full CRUD + Import CSV + Kategori + Kelengkapan + Pagination
// =====================================
include 'config.php';

// ================================
// Tambah Baju Manual
// ================================
if(isset($_POST['tambah'])){
    $nama = trim($conn->real_escape_string($_POST['nama']));
    $kategori = trim($conn->real_escape_string($_POST['kategori']));
    $jenis = trim($conn->real_escape_string($_POST['jenis']));
    $ukuran = trim($conn->real_escape_string($_POST['ukuran']));
    $harga = (int)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    $kelengkapan = trim($conn->real_escape_string($_POST['kelengkapan']));

    if($nama && $kategori && $jenis && $ukuran && $harga>0 && $stok>0){
        $cek = $conn->query("
            SELECT * FROM baju 
            WHERE nama='$nama' AND kategori='$kategori' AND jenis='$jenis' AND ukuran='$ukuran'
        ")->fetch_assoc();

        if($cek){
            $conn->query("UPDATE baju SET harga='$harga', kelengkapan='$kelengkapan' WHERE id=".$cek['id']);
        } else {
            $sql = "INSERT INTO baju(nama,kategori,jenis,ukuran,harga,stok,kelengkapan)
                    VALUES ('$nama','$kategori','$jenis','$ukuran','$harga','$stok','$kelengkapan')";
            if(!$conn->query($sql)){
                echo "<script>alert('Error tambah baju: ".$conn->error."');</script>";
            }
        }

        header("Location: baju.php");
        exit;
    } else {
        echo "<script>alert('Semua field wajib diisi dan harga/stok > 0!');</script>";
    }
}

// ================================
// Import CSV
// ================================
if(isset($_POST['import'])){
    if(isset($_FILES['file']['tmp_name']) && !empty($_FILES['file']['tmp_name'])){
        $file = fopen($_FILES['file']['tmp_name'], "r");
        if($file){
            $first = true;
            while(($row = fgetcsv($file, 0, ",")) !== false){
                if($first){ $first=false; continue; }
                if(count($row) < 6) continue;
                $row = array_map('trim', $row);
                $nama = $conn->real_escape_string($row[0]);
                $kategori = $conn->real_escape_string($row[1]);
                $jenis = $conn->real_escape_string($row[2]);
                $ukuran = $conn->real_escape_string($row[3]);
                $harga = (int)$row[4];
                $stok = (int)$row[5];
                $kelengkapan = isset($row[6]) ? $conn->real_escape_string($row[6]) : '';
                if(!$nama || !$kategori || !$jenis || !$ukuran || $harga <= 0 || $stok <= 0) continue;
                $cek = $conn->query("
                    SELECT * FROM baju 
                    WHERE nama='$nama' AND kategori='$kategori' AND jenis='$jenis' AND ukuran='$ukuran'
                ")->fetch_assoc();
                if($cek){
                    $conn->query("UPDATE baju SET harga='$harga', kelengkapan='$kelengkapan' WHERE id=".$cek['id']);
                } else {
                    $conn->query("INSERT INTO baju(nama,kategori,jenis,ukuran,harga,stok,kelengkapan)
                                 VALUES ('$nama','$kategori','$jenis','$ukuran','$harga','$stok','$kelengkapan')");
                }
            }
            fclose($file);
            header("Location: baju.php");
            exit;
        }
    } else {
        echo "<script>alert('File CSV belum dipilih!');</script>";
    }
}

// ================================
// Delete ALL
// ================================
if(isset($_POST['delete_all'])){
    $conn->query("SET FOREIGN_KEY_CHECKS=0");
    $conn->query("DELETE FROM baju");
    $conn->query("SET FOREIGN_KEY_CHECKS=1");
    header("Location: baju.php");
    exit;
}

// ================================
// Hapus Satu Baju
// ================================
if(isset($_GET['hapus'])){
    $id = (int)$_GET['hapus'];
    $conn->query("DELETE FROM baju WHERE id=$id");
    header("Location: baju.php");
    exit;
}

// ================================
// Pencarian & Pagination
// ================================
$cari = isset($_GET['cari']) ? $conn->real_escape_string($_GET['cari']) : '';
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$cari_sql = "";
if($cari){
    $cari_sql = "WHERE nama LIKE '%$cari%' OR kategori LIKE '%$cari%' OR jenis LIKE '%$cari%' OR ukuran LIKE '%$cari%'";
}

// Hitung total data
$total_result = $conn->query("SELECT COUNT(*) as total FROM baju $cari_sql");
$total_row = $total_result->fetch_assoc();
$total_data = $total_row['total'];
$total_page = ceil($total_data / $limit);

// Ambil data sesuai halaman
$data = $conn->query("SELECT * FROM baju $cari_sql ORDER BY id DESC LIMIT $limit OFFSET $offset");

// ================================
// Include Header
// ================================
include 'header.php';
?>

<h2 class="title">Data Baju</h2>

<!-- Form Tambah Manual -->
<div class="card p-3 mb-4 shadow-sm">
    <h5>Tambah Baju</h5>
    <form method="post">
        <div class="row g-2">
            <div class="col-md-2"><input type="text" name="nama" class="form-control" placeholder="Nama Baju" required></div>
            <div class="col-md-2">
                <select name="kategori" class="form-control" required>
                    <option value="">Pilih Kategori</option>
                    <option value="Anak-anak">Anak-anak</option>
                    <option value="Dewasa">Dewasa</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="jenis" class="form-control" required>
                    <option value="">Pilih Jenis</option>
                    <option value="Laki-laki">Laki-laki</option>
                    <option value="Perempuan">Perempuan</option>
                    <option value="Unisex">Unisex</option>
                </select>
            </div>
            <div class="col-md-2"><input type="text" name="ukuran" class="form-control" placeholder="Ukuran" required></div>
            <div class="col-md-2"><input type="number" name="harga" class="form-control" placeholder="Harga" required></div>
            <div class="col-md-2"><input type="number" name="stok" class="form-control" placeholder="Jumlah" value="1" min="1" required></div>
            <div class="col-12 col-md-4 mt-2"><input type="text" name="kelengkapan" class="form-control" placeholder="Contoh: Ikat pinggang, sabuk, kalung, dll"></div>
            <div class="col-auto mt-2">
                <button type="submit" name="tambah" class="btn btn-primary px-3 w-auto d-inline-flex align-items-center">
                    <i class="bi bi-plus-circle me-1"></i> Tambah
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Import CSV -->
<div class="card p-3 mb-4 shadow-sm">
    <h5>Import CSV</h5>
    <form method="post" enctype="multipart/form-data">
        <p class="text-muted">Format CSV: <strong>nama,kategori,jenis,ukuran,harga,stok,kelengkapan</strong></p>
        <div class="row g-3">
            <div class="col-md-6"><input type="file" name="file" accept=".csv" class="form-control" required></div>
            <button type="submit" name="import" class="btn btn-success w-auto d-inline-block">
    <i class="bi bi-file-earmark-spreadsheet"></i> Import CSV
</button>

        </div>
    </form>
</div>

<!-- Delete Semua -->
<button class="btn btn-danger px-4 mb-3" data-bs-toggle="modal" data-bs-target="#confirmDeleteAll">
    <i class="bi bi-trash"></i> Hapus Semua
</button>

<!-- Pencarian -->
<form method="get" class="mb-3">
    <div class="input-group">
        <input type="text" name="cari" value="<?=$cari?>" class="form-control" placeholder="Cari baju...">
        <button class="btn btn-secondary">Cari</button>
    </div>
</form>

<!-- Tabel -->
<div class="card p-3 shadow-sm">
<table class="table table-bordered table-striped align-middle">
    <tr>
        <th>No</th>
        <th>Nama</th>
        <th>Kategori</th>
        <th>Jenis</th>
        <th>Ukuran</th>
        <th>Harga</th>
        <th>Status</th>
        <th>Stok</th>
        <th>Kelengkapan</th>
        <th>Aksi</th>
    </tr>

    <?php $no = $offset+1; while($row = $data->fetch_assoc()){ ?>
    <tr>
        <td><?=$no++?></td>
        <td><?=$row['nama']?></td>
        <td><?=$row['kategori']?></td>
        <td><?=$row['jenis']?></td>
        <td><?=$row['ukuran']?></td>
        <td>Rp <?=number_format($row['harga'])?></td>
        <td>
            <?php if($row['stok'] > 0){ ?>
                <span class="badge bg-success">Ready</span>
            <?php } else { ?>
                <span class="badge bg-danger">Keluar</span>
            <?php } ?>
        </td>
        <td><?=$row['stok']?></td>
        <td><?=$row['kelengkapan']?></td>
        <td>
            <a href="edit_baju.php?id=<?=$row['id']?>" class="btn btn-warning btn-sm">Edit</a>
            <a href="baju.php?hapus=<?=$row['id']?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus baju?')">Hapus</a>
        </td>
    </tr>
    <?php } ?>
</table>
</div>

<!-- Pagination Lengkap Bahasa Indonesia -->
<nav>
  <ul class="pagination justify-content-center mt-3">
    <?php
    $start_page = max(1, $page - 2);
    $end_page = min($total_page, $page + 2);

    // Tombol Awal
    if($page > 1){
        echo '<li class="page-item"><a class="page-link" href="baju.php?page=1'.($cari?'&cari='.$cari:'').'">Awal</a></li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">Awal</span></li>';
    }

    // Tombol Sebelumnya
    if($page > 1){
        echo '<li class="page-item"><a class="page-link" href="baju.php?page='.($page-1).($cari?'&cari='.$cari:'').'">Sebelumnya</a></li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">Sebelumnya</span></li>';
    }

    // Halaman Tengah (maks 5 halaman)
    for($i = $start_page; $i <= $end_page; $i++){
        $active = ($i == $page) ? 'active' : '';
        echo '<li class="page-item '.$active.'"><a class="page-link" href="baju.php?page='.$i.($cari?'&cari='.$cari:'').'">'.$i.'</a></li>';
    }

    // Tombol Berikutnya
    if($page < $total_page){
        echo '<li class="page-item"><a class="page-link" href="baju.php?page='.($page+1).($cari?'&cari='.$cari:'').'">Berikutnya</a></li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">Berikutnya</span></li>';
    }

    // Tombol Akhir
    if($page < $total_page){
        echo '<li class="page-item"><a class="page-link" href="baju.php?page='.$total_page.($cari?'&cari='.$cari:'').'">Akhir</a></li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">Akhir</span></li>';
    }
    ?>
  </ul>
</nav>

<!-- Modal Hapus Semua -->
<div class="modal fade" id="confirmDeleteAll" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Konfirmasi Hapus Semua</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <p class="mb-0">
          Apakah Anda yakin ingin menghapus <strong>SEMUA DATA BAJU</strong>?<br>
          Tindakan ini tidak dapat dibatalkan!
        </p>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <form method="post">
            <button type="submit" name="delete_all" class="btn btn-danger">Ya, Hapus Semua</button>
        </form>
      </div>

    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
