<?php
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Cell\DataType;
    use PhpOffice\PhpSpreadsheet\Shared\Date;
    use PhpOffice\PhpSpreadsheet\Style\Alignment;

    // Tampung output agar pesan dari file include tidak merusak file Excel.
    ob_start();
    require __DIR__ . '/../../vendor/autoload.php';
    include __DIR__ . '/../../_Config/Connection.php';
    include __DIR__ . '/../../_Config/GlobalFunction.php';
    include __DIR__ . '/../../_Config/Session.php';

    // Validasi sesi dan ID supplier dari form export.
    if (empty($SessionIdAkses)) {
        ob_end_clean();
        http_response_code(401);
        exit('Sesi Akses Sudah Berakhir, Silahkan Login Ulang!');
    }
    $id_supplier = filter_var($_POST['id_supplier'] ?? '', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    if ($id_supplier === false) {
        ob_end_clean();
        http_response_code(400);
        exit('ID Supplier Tidak Boleh Kosong Atau Tidak Valid!');
    }

    try {
        // Ambil seluruh rincian sesuai jumlah data pada FormExportTransaksi.
        // LEFT JOIN menjaga rincian tetap tampil ketika barang tidak tersedia.
        $sql = "
            SELECT
                r.id_transaksi_jual_beli, t.kategori, t.tanggal, t.status,
                b.kode_barang, r.harga, r.qty, r.satuan,
                r.harga * r.qty AS subtotal_awal, r.ppn, r.diskon, r.subtotal
            FROM transaksi_jual_beli_rincian r
            INNER JOIN transaksi_jual_beli t
                ON t.id_transaksi_jual_beli = r.id_transaksi_jual_beli
            LEFT JOIN barang b ON b.id_barang = r.id_barang
            WHERE t.id_supplier = ?
            ORDER BY t.tanggal ASC, r.id_transaksi_jual_beli_rincian ASC
        ";
        $stmt = mysqli_prepare($Conn, $sql);
        if (!$stmt) {
            throw new RuntimeException('Gagal menyiapkan data export transaksi.');
        }
        mysqli_stmt_bind_param($stmt, 'i', $id_supplier);
        if (!mysqli_stmt_execute($stmt)) {
            throw new RuntimeException('Gagal membaca data export transaksi.');
        }
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) === 0) {
            mysqli_stmt_close($stmt);
            ob_end_clean();
            exit('Tidak Ada Data Transaksi Supplier Yang Bisa Di Export.');
        }

        // Buat worksheet dengan susunan kolom sesuai permintaan export.
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Transaksi Supplier');
        $headers = [
            'A1' => 'No',
            'B1' => 'Referensi',
            'C1' => 'Kategori',
            'D1' => 'Tanggal',
            'E1' => 'Kode',
            'F1' => 'Harga',
            'G1' => 'QTY',
            'H1' => 'Satuan',
            'I1' => 'Subtotal',
            'J1' => 'PPN',
            'K1' => 'Diskon',
            'L1' => 'Jumlah',
            'M1' => 'Status'
        ];
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Isi satu baris Excel untuk setiap rincian transaksi.
        $no  = 1;
        $row = 2;
        while ($data = mysqli_fetch_assoc($result)) {
            $sheet->setCellValue('A'.$row, $no);

            // Teks eksplisit mempertahankan nol awal kode dan mencegah formula dari data.
            $textColumns = [
                'B' => $data['id_transaksi_jual_beli'],
                'C' => $data['kategori'],
                'E' => $data['kode_barang'] ?? '',
                'H' => $data['satuan'],
                'M' => $data['status']
            ];
            foreach ($textColumns as $column => $value) {
                $sheet->setCellValueExplicit($column.$row, (string)$value, DataType::TYPE_STRING);
            }

            // Simpan tanggal sebagai nilai Excel agar dapat diurutkan dan difilter.
            $tanggal = Date::PHPToExcel(new DateTime($data['tanggal']));
            $sheet->setCellValue('D'.$row, $tanggal);

            // Subtotal adalah harga x qty; Jumlah adalah nilai akhir rincian tersimpan.
            // Nominal dan qty disimpan sebagai angka agar dapat dihitung di Excel.
            $numberColumns = [
                'F' => $data['harga'],
                'G' => $data['qty'],
                'I' => $data['subtotal_awal'],
                'J' => $data['ppn'],
                'K' => $data['diskon'],
                'L' => $data['subtotal']
            ];
            foreach ($numberColumns as $column => $value) {
                $sheet->setCellValue($column.$row, (float)($value ?? 0));
            }
            $row++;
            $no++;
        }
        mysqli_stmt_close($stmt);

        // Rapikan header, format angka/tanggal, dan lebar kolom.
        $lastRow = $row - 1;
        $sheet->getStyle('A1:M1')->getFont()->setBold(true);
        $sheet->getStyle('A1:M1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D2:D'.$lastRow)->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm:ss');
        $sheet->getStyle('F2:G'.$lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('I2:L'.$lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:M'.$lastRow);
        foreach (range('A', 'M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Selesaikan file sementara sebelum mengirim header download.
        // File sementara otomatis dihapus setelah resource ditutup.
        $file = tmpfile();
        if ($file === false) {
            throw new RuntimeException('Gagal membuat file sementara export.');
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save(stream_get_meta_data($file)['uri']);
        rewind($file);

        $filename = 'Transaksi-Supplier-'.$id_supplier.'-'.date('Ymd-His').'.xlsx';
        ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: no-store, max-age=0');
        fpassthru($file);
        fclose($file);
        $spreadsheet->disconnectWorksheets();
        exit;
    } catch (Exception $e) {
        // Detail kesalahan hanya dicatat di log server.
        if (isset($file) && is_resource($file)) {
            fclose($file);
        }
        ob_end_clean();
        error_log('Export transaksi supplier: ' . $e->getMessage());
        http_response_code(500);
        exit('Gagal Membuat File Excel Transaksi Supplier. Silahkan Coba Lagi.');
    }
