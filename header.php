<?php ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Nusantara Attire</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
body {
    background: #f4f2ee;
    font-family:'Poppins',sans-serif;
    font-size:14px;
    transition:0.25s ease-in-out;
}
body::before {
    content:"";
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background-image:url('indonesia.png');
    background-size:55%;
    background-position:center;
    background-repeat:no-repeat;
    opacity:0.08;
    z-index:-1;
    pointer-events:none;
    filter:grayscale(100%);
}
.dark-mode::before {
    opacity:0.12;
    filter:brightness(0.45) grayscale(100%);
}

.sidebar { width:230px; height:100vh; position:fixed; top:0; left:0;
          background:linear-gradient(180deg,#8B5E3C,#A9825A); padding:20px;
          color:white; box-shadow:3px 0 15px rgba(0,0,0,0.2); text-align:center; transition:0.3s; }
.sidebar-logo { width:160px; margin:0 auto 12px auto; display:block; }
.sidebar a { display:flex; align-items:center; gap:8px; padding:10px 12px; margin-bottom:6px;
             text-decoration:none; color:white; font-weight:500; font-size:13px;
             border-radius:8px; transition:0.25s; }
.sidebar a:hover { background:rgba(255,255,255,0.15); transform:translateX(4px); }

.content { margin-left:245px; padding:20px; }
.title { font-size:22px; font-weight:600; margin-bottom:18px; color:#4a3728; }
.card { border-radius:12px!important; border:none; transition:0.25s; }

.dark-mode { background-color:#1a1a1a!important; color:#e6e6e6!important; }
.dark-mode .content { background-color:#1a1a1a!important; }
.dark-mode .title { color:#f2f2f2!important; }
.dark-mode .card { background-color:#262626!important; border:1px solid #333!important; color:#fff!important; }
.dark-mode .sidebar { background:linear-gradient(180deg,#3d3d3d,#2b2b2b)!important; box-shadow:3px 0 15px rgba(255,255,255,0.1); }
.dark-mode .sidebar a { color:#e6e6e6!important; }
.dark-mode .sidebar a:hover { background:rgba(255,255,255,0.1); }

.dark-toggle-btn { position:fixed; right:20px; bottom:20px; z-index:9999; border-radius:50px; padding:12px 14px; }

/* =========================
   TABEL RATA TENGAH
========================= */
.table th,
.table td {
    text-align: center;
    vertical-align: middle;
}

/* =========================
   HANYA HEADER TABEL RATA TENGAH
========================= */
.table th {
    text-align: center;
    vertical-align: middle;
}
.table td {
    /* Tetap default: teks kiri, angka kanan */
    text-align: left;
    vertical-align: middle;
}

</style>

</head>
<body>

<button id="darkToggle" class="btn btn-dark dark-toggle-btn" aria-label="Toggle dark mode">
    <i class="bi bi-moon-stars"></i>
</button>

<div class="sidebar">
    <img src="Logo.png" alt="Logo" class="sidebar-logo">
    <a href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="baju.php"><i class="bi bi-bag-heart"></i> Data Baju</a>
    <a href="penyewa.php"><i class="bi bi-people"></i> Data Penyewa</a>
    <a href="sewa.php"><i class="bi bi-cart-check"></i> Transaksi Sewa</a>
    <a href="laporan.php"><i class="bi bi-file-text"></i> Laporan</a>
    <a href="login.php?logout=1" class="mt-5"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<div class="content">

<script>
(function(){
    const toggleBtn = document.getElementById("darkToggle");
    const body = document.body;
    if(localStorage.getItem("dark-mode")==="enabled"){
        body.classList.add("dark-mode");
        toggleBtn.classList.remove("btn-dark");
        toggleBtn.classList.add("btn-light");
        toggleBtn.innerHTML='<i class="bi bi-brightness-high"></i>';
    }
    toggleBtn.addEventListener("click",()=>{
        body.classList.toggle("dark-mode");
        if(body.classList.contains("dark-mode")){
            localStorage.setItem("dark-mode","enabled");
            toggleBtn.classList.remove("btn-dark");
            toggleBtn.classList.add("btn-light");
            toggleBtn.innerHTML='<i class="bi bi-brightness-high"></i>';
        } else {
            localStorage.setItem("dark-mode","disabled");
            toggleBtn.classList.remove("btn-light");
            toggleBtn.classList.add("btn-dark");
            toggleBtn.innerHTML='<i class="bi bi-moon-stars"></i>';
        }
    });
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
