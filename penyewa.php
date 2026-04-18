<?php
// =====================================
// penyewa.php - CRUD Data Penyewa + Pencarian + Pagination
// =====================================
include 'config.php';
include 'header.php';

// ================================
// Tambah Penyewa
// ================================
if(isset($_POST['tambah'])){
    $nama = $_POST['nama'];
    $telp = $_POST['telp'];
    $alamat = $_POST['alamat'];

    $conn->query("INSERT INTO penyewa(nama, telp, alamat) VALUES ('$nama','$telp','$alamat')");
}

// ================================
// Hapus Penyewa
// ================================
if(isset($_GET['hapus'])){
    $id = (int)$_GET['hapus'];
    $conn->query("DELETE FROM penyewa WHERE id=$id");
}

// ================================
// Pencarian & Pagination
// ================================
$cari = isset($_GET['cari']) ? $_GET['cari'] : '';
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$cari_sql = "";
if($cari){
    $cari_sql = "WHERE nama LIKE '%$cari%' OR telp LIKE '%$cari%' OR alamat LIKE '%$cari%'";
}

// Hitung total data
$total_result = $conn->query("SELECT COUNT(*) as total FROM penyewa $cari_sql");
$total_row = $total_result->fetch_assoc();
$total_data = $total_row['total'];
$total_page = ceil($total_data / $limit);

// Ambil data sesuai halaman
$data = $conn->query("SELECT * FROM penyewa $cari_sql ORDER BY id DESC LIMIT $limit OFFSET $offset");
?>

<h2 class="title">Data Penyewa</h2>

<!-- Form Tambah -->
<div class="card p-3 mb-4 shadow-sm">
    <h5>Tambah Penyewa</h5>
    <form method="post">
        <div class="row g-2">
            <div class="col-md-3"><input type="text" name="nama" class="form-control" placeholder="Nama Penyewa" required></div>
            <div class="col-md-3"><input type="text" name="telp" class="form-control" placeholder="No. Telepon" required></div>
            <div class="col-md-4"><input type="text" name="alamat" class="form-control" placeholder="Alamat" required></div>
            <div class="col-md-2"><button class="btn btn-primary px-3" name="tambah"><i class="bi bi-plus-circle"></i> Tambah</button></div>
        </div>
    </form>
</div>

<!-- Pencarian -->
<form method="get" class="mb-3">
    <div class="input-group">
        <input type="text" name="cari" value="<?=$cari?>" class="form-control" placeholder="Cari penyewa...">
        <button class="btn btn-secondary">Cari</button>
    </div>
</form>

<!-- Tabel Penyewa -->
<div class="card p-3 shadow-sm">
<table class="table table-bordered table-striped align-middle">
    <tr>
        <th>No</th>
        <th>Nama</th>
        <th>Telepon</th>
        <th>Alamat</th>
        <th>Aksi</th>
    </tr>
    <?php $no = $offset + 1; ?>
    <?php while($row = $data->fetch_assoc()){ ?>
    <tr>
        <td><?=$no++?></td>
        <td><?=$row['nama']?></td>
        <td><?=$row['telp']?></td>
        <td><?=$row['alamat']?></td>
        <td>
            <a href="edit_penyewa.php?id=<?=$row['id']?>" class="btn btn-warning btn-sm">Edit</a>
            <a href="penyewa.php?hapus=<?=$row['id']?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus penyewa?')">Hapus</a>
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
        echo '<li class="page-item"><a class="page-link" href="penyewa.php?page=1'.($cari?'&cari='.$cari:'').'">Awal</a></li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">Awal</span></li>';
    }

    // Tombol Sebelumnya
    if($page > 1){
        echo '<li class="page-item"><a class="page-link" href="penyewa.php?page='.($page-1).($cari?'&cari='.$cari:'').'">Sebelumnya</a></li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">Sebelumnya</span></li>';
    }

    // Halaman tengah (maks 5 halaman)
    for($i = $start_page; $i <= $end_page; $i++){
        $active = ($i == $page) ? 'active' : '';
        echo '<li class="page-item '.$active.'"><a class="page-link" href="penyewa.php?page='.$i.($cari?'&cari='.$cari:'').'">'.$i.'</a></li>';
    }

    // Tombol Berikutnya
    if($page < $total_page){
        echo '<li class="page-item"><a class="page-link" href="penyewa.php?page='.($page+1).($cari?'&cari='.$cari:'').'">Berikutnya</a></li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">Berikutnya</span></li>';
    }

    // Tombol Akhir
    if($page < $total_page){
        echo '<li class="page-item"><a class="page-link" href="penyewa.php?page='.$total_page.($cari?'&cari='.$cari:'').'">Akhir</a></li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">Akhir</span></li>';
    }
    ?>
  </ul>
</nav>

<?php include 'footer.php'; ?>
