<?php
    header('Content-Type: text/plain; charset=utf-8');
    // Apabila Periode Adalah Tahunan maka wajib ada Tahun, Apabila Periode Adalah Bulanan maka wajib ada Tahun dan Bulan, Apabila Periode Adalah Harian maka wajib ada Tanggal
    $periode = $_POST['periode_data'] ?? '';

    if (!in_array($periode, ['Semua', 'Tahunan', 'Bulanan', 'Harian'], true)) {
        http_response_code(400);
        exit('Periode data tidak valid.');
    }

    // Routing Periode Data Rekap Aktivitas Untuk Menampilkan Pada Tombol Periode Rekap Aktivitas
    if ($periode === 'Semua') {
        $label = 'Semua';
    } elseif ($periode === 'Tahunan') {
        $tahun = $_POST['tahun'] ?? '';
        if (!is_string($tahun) || !preg_match('/^[0-9]{4}$/D', $tahun) || (int)$tahun < 1000 || (int)$tahun > 9998) {
            http_response_code(400);
            exit('Tahun harus berupa 4 digit angka antara 1000 dan 9998.');
        }
        $label = $tahun;
    } elseif ($periode === 'Bulanan') {
        $tahun = $_POST['tahun'] ?? '';
        $bulan = $_POST['bulan'] ?? '';
        if (!is_string($tahun) || !preg_match('/^[0-9]{4}$/D', $tahun) || (int)$tahun < 1000 || (int)$tahun > 9998) {
            http_response_code(400);
            exit('Tahun harus berupa 4 digit angka antara 1000 dan 9998.');
        }
        if (!is_string($bulan) || !preg_match('/^(0[1-9]|1[0-2])$/D', $bulan)) {
            http_response_code(400);
            exit('Bulan harus berupa angka 01 sampai 12.');
        }
        $label = $tahun . '-' . $bulan;
    } elseif ($periode === 'Harian') {
        $tanggal = $_POST['tanggal'] ?? '';
        $awal = is_string($tanggal) ? DateTimeImmutable::createFromFormat('!Y-m-d', $tanggal) : false;
        if (!$awal || $awal->format('Y-m-d') !== $tanggal || $tanggal < '1000-01-01' || $tanggal > '9998-12-31') {
            http_response_code(400);
            exit('Tanggal tidak valid. Gunakan format YYYY-MM-DD.');
        }
        $label = $tanggal;
    }

    // Echo 
    echo $label;
?>
