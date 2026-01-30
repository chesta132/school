<?php
// Konfigurasi Kelas & Jurusan
// File ini dipake buat generate option kelas secara dinamis

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

// Generate list kelas berdasarkan jurusan
function getKelasByJurusan($jurusan, $tingkat) {
    global $jurusan_config;
    
    if (!isset($jurusan_config[$jurusan][$tingkat])) {
        return [];
    }
    
    $jumlah_kelas = $jurusan_config[$jurusan][$tingkat];
    $kelas_list = [];
    
    for ($i = 1; $i <= $jumlah_kelas; $i++) {
        $kelas_list[] = $tingkat . ' ' . $jurusan . ' ' . $i;
    }
    
    return $kelas_list;
}

// Generate semua kombinasi kelas
function getAllKelas() {
    global $jurusan_config;
    $all_kelas = [];
    
    foreach ($jurusan_config as $jurusan => $tingkat_data) {
        foreach ($tingkat_data as $tingkat => $jumlah) {
            for ($i = 1; $i <= $jumlah; $i++) {
                $all_kelas[] = [
                    'value' => $tingkat . '|' . $i . '|' . $jurusan,
                    'label' => $tingkat . ' ' . $jurusan . ' ' . $i
                ];
            }
        }
    }
    
    return $all_kelas;
}

// Get list jurusan
function getJurusanList() {
    global $jurusan_config;
    return array_keys($jurusan_config);
}

// Get list tingkat kelas
function getTingkatList() {
    return ['10', '11', '12'];
}

function buildKelas($tingkat, $jurusan, $nomor) {
    $builded = implode(' ', [htmlspecialchars($tingkat ?: ''), htmlspecialchars($jurusan ?: ''), htmlspecialchars($nomor ?: '')]);
    if (trim($builded) == '') {
        return '-';
    } else {
        return $builded;
    }
}

function jurusanAliasToLong($alias) {
    global $alias_jurusan;
    return $alias_jurusan[$alias];
}
?>