<?php
    // Koneksi dan validasi sesi mengikuti endpoint supplier lainnya.
    include __DIR__ . '/../../_Config/Connection.php';
    include __DIR__ . '/../../_Config/GlobalFunction.php';
    include __DIR__ . '/../../_Config/Session.php';
    header('Content-Type: application/json; charset=utf-8');

    // Amankan teks database sebelum ditampilkan sebagai HTML.
    $escape = static function ($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    };

    // Respons JSON dipakai oleh ShowRiwayatTransaksi untuk isi tabel dan pagination.
    $respond = static function ($status, $html, $page = 1, $JmlHalaman = 0, $jml_data = 0) {
        echo json_encode([
            'status'     => $status,
            'html'       => $html,
            'page'       => $page,
            'total_page' => $JmlHalaman,
            'total_data' => $jml_data
        ], JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    };

    // Baris pesan memakai colspan yang sama dengan sembilan kolom tabel.
    $emptyRow = static function ($message) use ($escape) {
        return '
            <tr class="table-empty">
                <td colspan="9" class="text-center text-danger">
                    <h1 class="bi bi-exclamation-triangle" aria-hidden="true"></h1>
                    <small>'.$escape($message).'</small>
                </td>
            </tr>
        ';
    };

    // Terima nilai tunggal saja agar parameter berbentuk array tidak menimbulkan error.
    $input = static function ($name, $default = '') {
        return isset($_POST[$name]) && is_scalar($_POST[$name]) ? trim((string)$_POST[$name]) : $default;
    };

    // Validasi sesi akses dan ID supplier sebelum menjalankan query.
    if (empty($SessionIdAkses)) {
        $respond('error', $emptyRow('Sesi akses sudah berakhir. Silakan login ulang.'));
    }
    $id_supplier = $input('id_supplier');
    if ($id_supplier === '') {
        $respond('error', $emptyRow('ID Supplier Tidak Boleh Kosong'));
    }

    // Nama kolom hanya berasal dari daftar yang sesuai ModalFilterTransaksi.
    $fields = [
        'id_transaksi_jual_beli' => 'r.id_transaksi_jual_beli',
        'kategori' => 't.kategori',
        'tanggal' => 't.tanggal',
        'nama_barang' => 'r.nama_barang',
        'status' => 't.status'
    ];

    // Tangkap parameter filter dengan nilai default dan batas yang valid.
    $OrderBy    = $fields[$input('OrderBy')] ?? 'r.id_transaksi_jual_beli_rincian';
    $ShortBy    = $input('ShortBy', 'DESC') === 'ASC' ? 'ASC' : 'DESC';
    $batas      = max(1, min(500, (int)$input('batas', '10')));
    $page       = max(1, (int)$input('page', '1'));
    $keyword    = $input('keyword');
    $keyword_by = $input('keyword_by');

    // Gunakan JOIN yang sama untuk menghitung dan mengambil rincian transaksi.
    // Kondisi supplier selalu berlaku, termasuk saat pencarian di semua kolom.
    $whereClause = ' FROM transaksi_jual_beli_rincian r
        INNER JOIN transaksi_jual_beli t ON t.id_transaksi_jual_beli = r.id_transaksi_jual_beli
        WHERE t.id_supplier = ?';
    $params = [$id_supplier];
    $types  = 's';
    if ($keyword !== '') {
        $searchFields = isset($fields[$keyword_by]) ? [$fields[$keyword_by]] : array_values($fields);
        $conditions = [];
        foreach ($searchFields as $field) {
            $conditions[] = "$field LIKE ?";
            $params[] = '%' . $keyword . '%';
            $types .= 's';
        }
        $whereClause .= ' AND (' . implode(' OR ', $conditions) . ')';
    }

    try {
        // Hitung jumlah rincian yang sesuai filter (satu baris per barang).
        $stmtCount = mysqli_prepare($Conn, 'SELECT COUNT(*) AS total' . $whereClause);
        if (!$stmtCount) {
            throw new RuntimeException('Gagal menyiapkan jumlah transaksi.');
        }
        mysqli_stmt_bind_param($stmtCount, $types, ...$params);
        if (!mysqli_stmt_execute($stmtCount)) {
            throw new RuntimeException('Gagal menghitung transaksi.');
        }
        $resultCount = mysqli_stmt_get_result($stmtCount);
        $rowCount    = mysqli_fetch_assoc($resultCount);
        $jml_data    = (int)$rowCount['total'];
        mysqli_stmt_close($stmtCount);

        // Batasi halaman agar tetap valid setelah filter atau data berubah.
        $JmlHalaman = (int)ceil($jml_data / $batas);
        $page       = min($page, max(1, $JmlHalaman));
        if ($jml_data === 0) {
            $respond('success', $emptyRow('Tidak Ada Data Transaksi Yang Ditemukan'), $page);
        }
        $posisi = ($page - 1) * $batas;

        // Ambil data halaman aktif. Urutan ID rincian menjaga pagination tetap konsisten.
        $dataQuery = 'SELECT r.id_transaksi_jual_beli, t.tanggal, r.nama_barang,
            r.harga, r.qty, r.ppn, r.diskon, r.subtotal, t.status' . $whereClause
            . " ORDER BY $OrderBy $ShortBy, r.id_transaksi_jual_beli_rincian $ShortBy LIMIT ?, ?";
        $stmtData = mysqli_prepare($Conn, $dataQuery);
        if (!$stmtData) {
            throw new RuntimeException('Gagal menyiapkan data transaksi.');
        }
        $limitParams = array_merge($params, [$posisi, $batas]);
        $limitTypes = $types . 'ii';
        mysqli_stmt_bind_param($stmtData, $limitTypes, ...$limitParams);
        if (!mysqli_stmt_execute($stmtData)) {
            throw new RuntimeException('Gagal membaca transaksi.');
        }
        $resultData = mysqli_stmt_get_result($stmtData);

        // Generate HTML untuk tbody riwayat transaksi supplier.
        $html = '';
        while ($data = mysqli_fetch_assoc($resultData)) {
            $timestamp = strtotime($data['tanggal'] ?? '');
            $tanggal = $timestamp === false ? '-' : date('d/m/Y', $timestamp);

            // Format nominal dan qty dengan pemisah Indonesia; desimal nol disembunyikan.
            $angka = [];
            foreach (['harga', 'qty', 'ppn', 'diskon', 'subtotal'] as $field) {
                $value = number_format((float)($data[$field] ?? 0), 2, ',', '.');
                $angka[$field] = rtrim(rtrim($value, '0'), ',');
            }

            // Warna badge mengikuti status transaksi induk.
            $badgeClass = [
                'Lunas' => 'bg-success',
                'Utang' => 'bg-warning text-dark',
                'Piutang' => 'bg-info text-dark'
            ][$data['status']] ?? 'bg-secondary';
            $referensi   = $escape($data['id_transaksi_jual_beli']);
            $nama_barang = $escape($data['nama_barang']);
            $status      = $escape($data['status']);

            // Susunan kolom harus sama dengan header TableRiwayatTransaksi.
            // Jumlah memakai subtotal tersimpan pada rincian transaksi.
            $html .= '
                <tr>
                    <td class="table-title" data-label="Referensi">
                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalDetailTransaksi" data-id="'.$referensi.'">
                            <small>'.$referensi.'</small>
                        </a>
                    </td>
                    <td data-label="Tgl"><small>'.$tanggal.'</small></td>
                    <td data-label="Uraian"><small>'.$nama_barang.'</small></td>
                    <td data-label="Harga" class="text-end"><small>'.$angka['harga'].'</small></td>
                    <td data-label="QTY" class="text-end"><small>'.$angka['qty'].'</small></td>
                    <td data-label="PPN" class="text-end"><small>'.$angka['ppn'].'</small></td>
                    <td data-label="Diskon" class="text-end"><small>'.$angka['diskon'].'</small></td>
                    <td data-label="Jumlah" class="text-end"><small>'.$angka['subtotal'].'</small></td>
                    <td data-label="Status"><span class="badge '.$badgeClass.'">'.$status.'</span></td>
                </tr>
            ';
        }
        mysqli_stmt_close($stmtData);

        // Kirim isi tabel beserta informasi pagination ke Supplier.js.
        $respond('success', $html, $page, $JmlHalaman, $jml_data);
    } catch (Exception $e) {
        // Catat detail di log server; kirim pesan umum ke pengguna.
        error_log('Riwayat transaksi supplier: ' . $e->getMessage());
        $respond('error', $emptyRow('Gagal memuat riwayat transaksi supplier.'));
    }
