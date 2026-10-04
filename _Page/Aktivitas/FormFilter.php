<?php
    header('Content-Type: text/html; charset=utf-8');

    require_once __DIR__ . '/../../_Config/Connection.php';
    require_once __DIR__ . '/../../_Config/GlobalFunction.php';
    require_once __DIR__ . '/../../_Config/Session.php';

    if (empty($SessionIdAkses)) {
        echo '<small class="text-danger">Sesi akses sudah berakhir. Silakan login ulang.</small>';
        exit;
    }

    $keyword_by = $_POST['keyword_by'] ?? '';

    echo '<label for="keyword">Keyword</label>';

    if ($keyword_by === 'datetime_log') {
        echo '<input type="date" name="keyword" id="keyword" class="form-control" value="'
            . date('Y-m-d') . '">';
    } elseif ($keyword_by === 'kategori_log') {
        $query = $Conn->query(
            'SELECT kategori_log, COUNT(*) AS total_log
             FROM log
             GROUP BY kategori_log
             ORDER BY kategori_log ASC'
        );

        echo '<select name="keyword" id="keyword" class="form-control">';
        echo '<option value="">Pilih Kategori</option>';

        while ($data = $query->fetch_assoc()) {
            $kategori = htmlspecialchars($data['kategori_log'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            echo '<option value="' . $kategori . '">' . $kategori
                . ' (' . (int)$data['total_log'] . ')</option>';
        }

        echo '</select>';
        $query->free();
    } else {
        // Nama, deskripsi, dan pencarian semua kolom menggunakan input teks.
        echo '<input type="text" name="keyword" id="keyword" class="form-control">';
    }
