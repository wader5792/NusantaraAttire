<?php
// ================================
// footer.php – Minimalist Sticky Footer (FIXED)
// ================================
?>
    </div> <!-- end content -->

   <footer style="
    position:fixed;
    bottom:0;
    left:0;
    width:100%;
    padding:10px 0;
    background:#ffffff;
    border-top:1px solid #e5e5e5;
    text-align:center;
    font-family:'Poppins', sans-serif;
    font-size:13px;
    color:#555;
    z-index: 99999; /* FOOTER PALING DEPAN */
    ">
        © 2025 <b>by wader5792</b>
    </footer>

    <style>
        .pagination {
            position: relative;
            z-index: 1; /* pagination di BELAKANG footer */
        }

        .content {
            padding-bottom: 20px; /* kecil saja, agar tidak terlalu naik */
        }

        .dark-toggle-btn {
            z-index: 999999 !important; /* lebih tinggi dari footer */
        }
    </style>

</body>
</html>
