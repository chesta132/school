<?php
require_once 'config/kelas_config.php';

// Helper: ambil inisial dari nama
function getInitials(string $nama): string {
    $parts = explode(' ', trim($nama));
    $init = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $init .= strtoupper($p[0] ?? '');
    }
    return $init ?: '?';
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