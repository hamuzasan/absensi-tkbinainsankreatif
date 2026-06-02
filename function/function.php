<?php
// Fungsi konversi tanggal ke format Bahasa Indonesia
date_default_timezone_set('Asia/Jakarta');
function formatTanggalIndonesia($tanggal) {
    $hari = [
        'Sunday'    => 'Minggu',
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu'
    ];

    $bulan = [
        1  => 'Januari', 2 => 'Februari', 3 => 'Maret',     4 => 'April',
        5  => 'Mei',     6 => 'Juni',     7 => 'Juli',      8 => 'Agustus',
        9  => 'September',10 => 'Oktober',11 => 'November',12 => 'Desember'
    ];

    $timestamp = strtotime($tanggal);
    $hariNama = $hari[date('l', $timestamp)];
    $tgl = date('j', $timestamp);
    $bulanNama = $bulan[(int)date('m', $timestamp)];
    $tahun = date('Y', $timestamp);

    return "$hariNama, $tgl $bulanNama $tahun";
}

