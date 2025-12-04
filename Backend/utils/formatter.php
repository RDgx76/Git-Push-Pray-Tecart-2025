<?php
function formatRupiah($number) {
    return "Rp " . number_format($number, 0, ',', '.');
}

function formatDate($date) {
    return date("d M Y", strtotime($date));
}
