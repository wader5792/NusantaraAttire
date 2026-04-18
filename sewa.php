<?php
// =====================================
// sewa.php – Transaksi Sewa (Mode C, Dropdown + "Tambah" daftar baju + Qty)
// =====================================

ob_start();
include 'config.php';

// ----------------------
// Helper: aman_cast_ids_qty
// Format database: "id:qty,id:qty"
// ----------------------
function aman_cast_ids_qty($str){
    $arr = [];
    $parts = array_filter(array_map('trim', explode(',', $str)), fn($v)=>$v!=='' );
    foreach($parts as $p){
        $sub = explode(':',$p);
        $id = (int)($sub[0] ?? 0);
        $qty = max(1,(int)($sub[1] ?? 1));
        if($id>0 && $qty>0) $arr[$id] = $qty;
    }
    return $arr;
}

// ==========================
// LOGIKA POST / GET SEBELUM OUTPUT
// ==========================

// ========== TAMBAH TRANSAKSI MODE C ==========
if(isset($_POST['tambah'])){
    $id_penyewa = (int)($_POST['id_penyewa'] ?? 0);
    $id_baju_list_raw = $_POST['id_baju_list'] ?? '';
    $tgl_sewa   = $_POST['tgl_sewa'] ?? '';
    $tgl_kembali= $_POST['tgl_kembali'] ?? '';
    $bayar      = (int)($_POST['bayar'] ?? 0);
    $keterangan = trim($conn->real_escape_string($_POST['keterangan'] ?? ''));

    $id_baju_arr = aman_cast_ids_qty($id_baju_list_raw);
    if($id_penyewa<=0 || empty($id_baju_arr) || !$tgl_sewa || !$tgl_kembali){
        echo "<script>alert('Form belum lengkap atau daftar baju kosong.'); window.location='sewa.php';</script>"; exit;
    }

    $total_harga = 0;
    $stok_error = false;
    $baju_names = [];
    foreach($id_baju_arr as $id_baju=>$qty){
        $q = $conn->query("SELECT nama,harga,stok,jenis,ukuran FROM baju WHERE id=$id_baju");
        $b = $q ? $q->fetch_assoc() : null;
        if(!$b || $b['stok']<$qty){
            $stok_error = true;
            break;
        }
        $total_harga += (int)$b['harga'] * $qty;
        $baju_names[] = $conn->real_escape_string($b['nama']." (".$b['jenis'].", ".$b['ukuran'].") x $qty");
    }
    if($stok_error){
        echo "<script>alert('Salah satu baju tidak cukup stok!'); window.location='sewa.php';</script>"; exit;
    }

    $status_bayar = ($bayar >= $total_harga) ? "lunas" : "belum";
    $kekurangan = ($status_bayar=="belum") ? $total_harga - $bayar : 0;

    $id_baju_list = implode(',', array_map(fn($id,$q)=>"$id:$q", array_keys($id_baju_arr), $id_baju_arr));
    $nama_baju_list = implode(', ', $baju_names);

    $sql = "
        INSERT INTO sewa(id_penyewa, id_baju_list, nama_baju_list, tgl_sewa, tgl_kembali, total_harga, bayar, kekurangan, status_bayar, status_sewa, keterangan)
        VALUES ('$id_penyewa', '".$conn->real_escape_string($id_baju_list)."', '".$conn->real_escape_string($nama_baju_list)."', '$tgl_sewa', '$tgl_kembali', '$total_harga', '$bayar', '$kekurangan', '$status_bayar', 'dipinjam', '$keterangan')
    ";
    $conn->query($sql);

    // kurangi stok
    foreach($id_baju_arr as $id_baju=>$qty){
        $conn->query("UPDATE baju SET stok = stok - $qty WHERE id=$id_baju");
    }

    header("Location: sewa.php"); exit;
}

// ========== PENGEMBALIAN ==========
if(isset($_GET['kembali'])){
    $id = (int)$_GET['kembali'];
    $sewa_data = $conn->query("SELECT id_baju_list FROM sewa WHERE id=$id")->fetch_assoc();
    if($sewa_data && trim($sewa_data['id_baju_list'])!==''){
        $id_baju_arr = aman_cast_ids_qty($sewa_data['id_baju_list']);
        foreach($id_baju_arr as $id_baju=>$qty){
            $conn->query("UPDATE baju SET stok = stok + $qty WHERE id=$id_baju");
        }
        $conn->query("UPDATE sewa SET status_sewa='kembali' WHERE id=$id");
    }
    header("Location: sewa.php"); exit;
}

// ========== PELUNASAN ==========
if(isset($_POST['pelunasan'])){
    $id = (int)($_POST['id_sewa'] ?? 0);
    $bayar_tambahan = (int)($_POST['bayar_tambahan'] ?? 0);
    $s = $conn->query("SELECT bayar,total_harga FROM sewa WHERE id=$id")->fetch_assoc();
    if(!$s){ echo "<script>alert('Transaksi tidak ditemukan'); window.location='sewa.php';</script>"; exit; }
    $bayar_baru = (int)$s['bayar'] + $bayar_tambahan;
    $kekurangan = max(0, (int)$s['total_harga'] - $bayar_baru);
    $status_bayar = ($kekurangan==0)?'lunas':'belum';
    $conn->query("UPDATE sewa SET bayar='$bayar_baru', kekurangan='$kekurangan', status_bayar='$status_bayar' WHERE id=$id");
    header("Location: sewa.php"); exit;
}

// ========== HAPUS ==========
if(isset($_GET['hapus'])){
    $id = (int)$_GET['hapus'];
    $sewa_data = $conn->query("SELECT id_baju_list,status_sewa FROM sewa WHERE id=$id")->fetch_assoc();
    if($sewa_data){
        $id_baju_arr = aman_cast_ids_qty($sewa_data['id_baju_list']);
        if($sewa_data['status_sewa']=='dipinjam'){
            foreach($id_baju_arr as $id_baju=>$qty){
                $conn->query("UPDATE baju SET stok = stok + $qty WHERE id=$id_baju");
            }
        }
        $conn->query("DELETE FROM sewa WHERE id=$id");
    }
    header("Location: sewa.php"); exit;
}

// ==========================
// PENCARIAN & PAGINATION
// ==========================
$cari = $conn->real_escape_string($_GET['cari'] ?? '');
$limit = 20;
$page = max(1,(int)($_GET['page']??1));
$offset = ($page-1)*$limit;
$cari_sql = $cari ? "WHERE p.nama LIKE '%$cari%' OR s.nama_baju_list LIKE '%$cari%' OR s.keterangan LIKE '%$cari%'" : '';

$total_result = $conn->query("SELECT COUNT(*) as total FROM sewa s JOIN penyewa p ON s.id_penyewa=p.id $cari_sql");
$total_data = $total_result->fetch_assoc()['total'];
$total_page = ceil($total_data/$limit);

$sewa = $conn->query("
    SELECT s.*, p.nama AS penyewa, p.alamat AS alamat_penyewa
    FROM sewa s
    JOIN penyewa p ON s.id_penyewa=p.id
    $cari_sql
    ORDER BY s.id DESC
    LIMIT $limit OFFSET $offset
");

$penyewa_result = $conn->query("SELECT * FROM penyewa ORDER BY nama ASC");
$baju_result = $conn->query("SELECT * FROM baju ORDER BY nama ASC");

// ==========================
// FORMAT TANGGAL
// ==========================
function formatTanggal($tgl){
    if(!$tgl) return "-";

    $hariIndo = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $bulanIndo = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

    $timestamp = strtotime($tgl);
    $hari = $hariIndo[(int)date('w', $timestamp)];
    $tanggal = date('d', $timestamp);
    $bulan = $bulanIndo[(int)date('n', $timestamp)];
    $tahun = date('Y', $timestamp);

    return "$hari, $tanggal $bulan $tahun";
}

// ==========================
// HEADER
// ==========================
include 'header.php';
?>

<h2 class="title">Transaksi Sewa</h2>

<!-- FORM TAMBAH -->
<div class="card p-3 mb-4 shadow-sm">
<h5>Tambah Transaksi</h5>
<form method="post" id="formSewa">
<div class="row g-2">

<div class="col-md-4">
<label>Nama</label>
<select name="id_penyewa" class="form-control" required>
<option value="">Pilih Penyewa</option>
<?php while($p=$penyewa_result->fetch_assoc()){ ?>
<option value="<?=$p['id']?>"><?=htmlspecialchars($p['nama'])?></option>
<?php } ?>
</select>
</div>

<div class="col-md-7">
<label>Baju</label>
<div class="input-group">
<select id="pilihBaju" class="form-control">
<option value="">Pilih Baju</option>
<?php
$baju_result->data_seek(0);
while($b=$baju_result->fetch_assoc()){
    echo '<option data-nama="'.htmlspecialchars($b['nama'].' ('.$b['jenis'].', '.$b['ukuran'].')').'" 
                data-harga="'.(int)$b['harga'].'" 
                data-stok="'.(int)$b['stok'].'" 
                value="'.$b['id'].'">'
        .htmlspecialchars($b['nama'].' ('.$b['jenis'].', '.$b['ukuran'].') - Rp '.number_format($b['harga']).' | Stok: '.$b['stok'])
        .'</option>';
}
?>
</select>
<input type="number" id="qtyBaju" min="1" value="1" class="form-control" style="max-width:80px;">
<button type="button" id="btnTambahBaju" class="btn btn-primary px-3">
    <i class="bi bi-plus-circle"></i> Tambah
</button>

</div>
<small class="text-muted">Klik Tambah untuk menambahkan ke daftar baju.</small>
<ul id="listBaju" class="list-group mt-2"></ul>
<input type="hidden" name="id_baju_list" id="id_baju_list" value="">
</div>

<div class="col-md-3">
<label>Tanggal Sewa</label>
<input type="date" name="tgl_sewa" class="form-control" required>
</div>

<div class="col-md-3">
<label>Tanggal Kembali</label>
<input type="date" name="tgl_kembali" class="form-control" required>
</div>

<div class="col-md-3">
<label>Bayar (Rp)</label>
<input type="number" name="bayar" class="form-control" required value="0">
</div>

<div class="col-md-3">
<label>Keterangan</label>
<input type="text" name="keterangan" class="form-control">
</div>

<div class="col-md-12 mt-2">
<button type="submit" name="tambah" class="btn btn-primary px-4 mt-2">
    <i class="bi bi-arrow-repeat"></i> Proses
</button>
</div>

</div>
</form>
</div>

<script>
const pilihBaju = document.getElementById('pilihBaju');
const btnTambahBaju = document.getElementById('btnTambahBaju');
const listBaju = document.getElementById('listBaju');
const idBajuListInput = document.getElementById('id_baju_list');
const qtyBajuInput = document.getElementById('qtyBaju');

let selected = [];

function updateHidden(){
    idBajuListInput.value = selected.map(i => i.id + ':' + i.qty).join(',');
}

function renderList(){
    listBaju.innerHTML = '';
    let totalQty = 0;
    let totalHarga = 0;
    selected.forEach((it, idx)=>{
        totalQty += it.qty;
        totalHarga += it.qty * it.harga;
        const li = document.createElement('li');
        li.className='list-group-item d-flex justify-content-between align-items-center';
        li.innerHTML=`<div>${it.nama} x ${it.qty} <br><small>Rp ${Number(it.harga*it.qty).toLocaleString()}</small></div>
                      <div><button class="btn btn-sm btn-danger me-1" data-idx="${idx}">Hapus</button></div>`;
        listBaju.appendChild(li);
    });
    if(selected.length>0){
        const liTotal=document.createElement('li');
        liTotal.className='list-group-item d-flex justify-content-between align-items-center bg-light fw-bold';
        liTotal.innerHTML=`<div>Total Baju: ${totalQty}</div><div>Total Harga: Rp ${Number(totalHarga).toLocaleString()}</div>`;
        listBaju.appendChild(liTotal);
    }
    updateHidden();
}

btnTambahBaju.addEventListener('click', function(){
    const opt = pilihBaju.selectedOptions[0];
    if(!opt || !opt.value) return alert('Pilih baju terlebih dahulu.');
    const id = parseInt(opt.value);
    const nama = opt.getAttribute('data-nama') || opt.text;
    const harga = parseInt(opt.getAttribute('data-harga') || '0');
    const stok = parseInt(opt.getAttribute('data-stok') || '0');
    let qty = parseInt(qtyBajuInput.value) || 1;
    
    // Validasi stok
    const existing = selected.find(x => x.id===id);
    if(existing){
        if(existing.qty + qty > stok) return alert(`Stok maksimal ${stok}.`);
        existing.qty += qty;
    } else {
        if(qty > stok) qty = stok;
        selected.push({id,nama,harga,stok,qty});
    }
    renderList();
});

listBaju.addEventListener('click', function(e){
    if(e.target && e.target.matches('button')){
        const idx = parseInt(e.target.getAttribute('data-idx'));
        if(!isNaN(idx)){ selected.splice(idx,1); renderList(); }
    }
});

document.getElementById('formSewa').addEventListener('submit', function(e){
    if(selected.length===0){ e.preventDefault(); alert('Silakan tambahkan minimal 1 baju.'); return false; }
    updateHidden();
    return true;
});
</script>


<!-- PENCARIAN -->
<form method="get" class="mb-3">
<div class="input-group">
<input type="text" name="cari" value="<?=$cari?>" class="form-control" placeholder="Cari transaksi...">
<button class="btn btn-secondary">Cari</button>
</div>
</form>

<!-- TABEL DATA -->
<div class="card p-3 shadow-sm">
    <table class="table table-bordered table-striped align-middle">
        <thead>
            <tr>
                <th>No</th>
                <th>Penyewa</th>
                <th>Alamat</th>
                <th>Daftar Baju</th>
                <th>Tgl Sewa</th>
                <th>Tgl Kembali</th>
                <th>Total Harga</th>
                <th>Bayar</th>
                <th>Kekurangan</th>
                <th>Status</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = $total_data - $offset;
            while($s = $sewa->fetch_assoc()):
                $id_baju_arr = aman_cast_ids_qty($s['id_baju_list'] ?? '');
                $total_qty = 0;
            ?>
            <tr>
                <td><?=$no--?></td>
                <td><?=htmlspecialchars($s['penyewa'])?></td>
                <td><?=htmlspecialchars($s['alamat_penyewa'])?></td>
                <td>
                    <?php
                    if(!empty($id_baju_arr)){
                        $id_list_sql = implode(',', array_map('intval', array_keys($id_baju_arr)));
                        $q = $conn->query("SELECT id, nama, jenis, ukuran FROM baju WHERE id IN ($id_list_sql)");
                        $mapNama = [];
                        while($bb = $q->fetch_assoc()) {
                            $mapNama[$bb['id']] = $bb['nama']." (".$bb['jenis'].", ".$bb['ukuran'].")";
                        }

                        foreach($id_baju_arr as $id_baju => $qty){
                            $total_qty += $qty;
                            $label = $mapNama[$id_baju] ?? "ID:$id_baju";
                            echo "- ".htmlspecialchars($label)." x $qty<br>";
                        }
                        echo "<strong>Total Baju: $total_qty</strong>";
                    } else {
                        echo "-";
                    }
                    ?>
                </td>
                <td><?=formatTanggal($s['tgl_sewa'])?></td>
                <td><?=formatTanggal($s['tgl_kembali'])?></td>
                <td>Rp <?=number_format($s['total_harga'])?></td>
                <td>Rp <?=number_format($s['bayar'])?></td>
                <td>Rp <?=number_format($s['kekurangan'])?></td>
                <td>
                    <?php 
                    if($s['status_sewa'] == 'kembali') {
                        echo '<span class="badge bg-primary">Selesai</span>';
                    } elseif($s['status_bayar'] == 'lunas') {
                        echo '<span class="badge bg-success">Lunas</span>';
                    } else {
                        echo '<span class="badge bg-danger">Belum Lunas</span>'; 
                    }
                    ?>
                </td>
                <td><?=htmlspecialchars($s['keterangan'] ?: '-')?></td>
                <td>
                    <?php if($s['status_sewa'] != 'kembali'): ?>
                        <a href="sewa.php?kembali=<?=$s['id']?>" class="btn btn-success btn-sm mb-1" 
                           onclick="return confirm('Konfirmasi pengembalian semua baju?')">Kembalikan</a>
                    <?php endif; ?>

                    <a href="sewa.php?hapus=<?=$s['id']?>" class="btn btn-danger btn-sm mb-1" 
                       onclick="return confirm('Hapus transaksi ini? Semua baju akan direstock.')">Hapus</a>

                    <?php if($s['status_bayar'] == 'belum'): ?>
                        <form method="post" class="mt-1">
                            <input type="hidden" name="id_sewa" value="<?=$s['id']?>">
                            <input type="number" name="bayar_tambahan" min="1" placeholder="Bayar tambahan" 
                                   class="form-control form-control-sm mb-1" required>
                            <button type="submit" name="pelunasan" class="btn btn-warning btn-sm w-100">Lunasi</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>


<!-- PAGINATION -->
<nav><ul class="pagination justify-content-center mt-3">
<?php
$start = max(1,$page-2); $end = min($total_page,$page+2);
if($page>1) echo '<li class="page-item"><a class="page-link" href="?page=1'.($cari?('&cari='.$cari):'').'">Awal</a></li>';
else echo '<li class="page-item disabled"><span class="page-link">Awal</span></li>';
if($page>1) echo '<li class="page-item"><a class="page-link" href="?page='.($page-1).($cari?('&cari='.$cari):'').'">Sebelumnya</a></li>';
else echo '<li class="page-item disabled"><span class="page-link">Sebelumnya</span></li>';
for($i=$start;$i<=$end;$i++){
    $active=($i==$page)?'active':'';
    echo '<li class="page-item '.$active.'"><a class="page-link" href="?page='.$i.($cari?('&cari='.$cari):'').'">'.$i.'</a></li>';
}
if($page<$total_page) echo '<li class="page-item"><a class="page-link" href="?page='.($page+1).($cari?('&cari='.$cari):'').'">Berikutnya</a></li>';
else echo '<li class="page-item disabled"><span class="page-link">Berikutnya</span></li>';
if($page<$total_page) echo '<li class="page-item"><a class="page-link" href="?page='.$total_page.($cari?('&cari='.$cari):'').'">Akhir</a></li>';
else echo '<li class="page-item disabled"><span class="page-link">Akhir</span></li>';
?>
</ul></nav>

<?php include 'footer.php'; ?>
