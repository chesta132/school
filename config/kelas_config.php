<?php
// Konfigurasi Kelas & Jurusan

// Data jurusan dan jumlah kelas per tingkat
$jurusan_config = [
    'TKJ' => [
        '10' => 4,  // Kelas 10 TKJ ada 4 kelas
        '11' => 3,  // Kelas 11 TKJ ada 3 kelas
        '12' => 3   // Kelas 12 TKJ ada 3 kelas
    ],
    'AKL' => [
        '10' => 2,
        '11' => 2,
        '12' => 2
    ],
    'Lakes' => [
        '10' => 2,
        '11' => 2,
        '12' => 2
    ],
    'Perhotelan' => [
        '10' => 1,
        '11' => 1,
        '12' => 1
    ]
];

$alias_jurusan = [
    'TKJ' => 'Teknik Komputer dan Jaringan',
    'AKL' => 'Akuntansi dan Keuangan Lembaga',
    'Lakes' => 'Layanan Kesehatan',
    'Perhotelan' => 'Perhotelan'
];
?>