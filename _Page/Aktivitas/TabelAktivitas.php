<?php

    header('Content-Type: application/json; charset=utf-8');

    // =========================================================
    // KONEKSI DAN SESSION
    // =========================================================
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // =========================================================
    // FUNGSI RESPONSE ERROR
    // =========================================================
    function responseError(
        string $message,
        int $page = 1,
        int $total_page = 1,
        int $total_data = 0
    ): void {

        echo json_encode([
            "status"     => "error",
            "html"       => '
                <tr>
                    <td colspan="5" class="text-center text-danger">
                        <small>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</small>
                    </td>
                </tr>
            ',
            "page"       => $page,
            "total_page" => $total_page,
            "total_data" => $total_data
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // =========================================================
    // VALIDASI SESSION
    // =========================================================
    if (empty($SessionIdAkses)) {

        responseError(
            "Sesi akses sudah berakhir. Silakan login ulang.",
            1,
            1,
            0
        );
    }

    // =========================================================
    // AMBIL PARAMETER
    // =========================================================
    $page       = $_POST['page'] ?? 1;
    $batas      = $_POST['batas'] ?? 10;
    $OrderBy    = $_POST['OrderBy'] ?? 'id_log';
    $ShortBy    = $_POST['ShortBy'] ?? 'DESC';
    $keyword_by = $_POST['keyword_by'] ?? '';
    $keyword    = trim($_POST['keyword'] ?? '');

    // =========================================================
    // VALIDASI PAGE
    // =========================================================
    $page = filter_var($page, FILTER_VALIDATE_INT);

    if ($page === false || $page < 1) {
        $page = 1;
    }

    // =========================================================
    // VALIDASI BATAS
    // =========================================================
    $batas = filter_var($batas, FILTER_VALIDATE_INT);

    if ($batas === false || $batas < 1) {
        $batas = 10;
    }

    // Batasi maksimal data per halaman
    if ($batas > 500) {
        $batas = 500;
    }

    // =========================================================
    // VALIDASI ORDER BY
    // =========================================================
    $allowedOrder = [
        'id_log',
        'id_akses',
        'datetime_log',
        'kategori_log',
        'deskripsi_log',
        'nama_akses'
    ];

    if (!in_array($OrderBy, $allowedOrder, true)) {
        $OrderBy = 'id_log';
    }

    // =========================================================
    // VALIDASI SORT
    // =========================================================
    $ShortBy = strtoupper($ShortBy);

    if (!in_array($ShortBy, ['ASC', 'DESC'], true)) {
        $ShortBy = 'ASC';
    }

    // =========================================================
    // ORDER SQL
    // =========================================================
    // Karena $OrderBy sudah divalidasi menggunakan whitelist,
    // aman untuk dimasukkan langsung ke query.
    $OrderBySql = ($OrderBy === 'nama_akses' ? "a." : "l.") . $OrderBy;

    // =========================================================
    // VALIDASI FILTER
    // =========================================================
    $allowedKeywordBy = [
        'id_akses',
        'nama_akses',
        'datetime_log',
        'kategori_log',
        'deskripsi_log'
    ];

    if (!empty($keyword_by) && !in_array($keyword_by, $allowedKeywordBy, true)) {
        $keyword_by = '';
    }

    // =========================================================
    // BUILD WHERE
    // =========================================================
    $where      = "";
    $bindTypes  = "";
    $bindValues = [];

    if ($keyword !== '') {

        $keywordLike = "%" . $keyword . "%";

        // ---------------------------------------------
        // Pencarian berdasarkan kolom tertentu
        // ---------------------------------------------
        if ($keyword_by !== '') {

            $keywordColumn = ($keyword_by === 'nama_akses' ? "a." : "l.") . $keyword_by; $where = " WHERE $keywordColumn LIKE ? ";

            $bindTypes   = "s";
            $bindValues[] = $keywordLike;

        } else {

            // ---------------------------------------------
            // Pencarian semua kolom
            // ---------------------------------------------
            $where = "
                WHERE (
                    l.id_akses LIKE ?
                    OR a.nama_akses LIKE ?
                    OR l.datetime_log LIKE ?
                    OR l.kategori_log LIKE ?
                    OR l.deskripsi_log LIKE ?
                )
            ";

            $bindTypes = "sssss";

            $bindValues[] = $keywordLike;
            $bindValues[] = $keywordLike;
            $bindValues[] = $keywordLike;
            $bindValues[] = $keywordLike;
            $bindValues[] = $keywordLike;
        }
    }

    // =========================================================
    // QUERY TOTAL DATA
    // =========================================================
    $sql_count = "
        SELECT COUNT(*) AS total
        FROM log AS l LEFT JOIN akses AS a ON a.id_akses = l.id_akses
        $where
    ";

    $stmt_count = $Conn->prepare($sql_count);

    if (!$stmt_count) {

        responseError(
            "Gagal mempersiapkan query jumlah data.",
            $page,
            1,
            0
        );
    }

    // =========================================================
    // BIND COUNT PARAMETER
    // =========================================================
    if (!empty($bindValues)) {

        $stmt_count->bind_param(
            $bindTypes,
            ...$bindValues
        );
    }

    // =========================================================
    // EXECUTE COUNT
    // =========================================================
    if (!$stmt_count->execute()) {

        $stmt_count->close();

        responseError(
            "Gagal menghitung jumlah data.",
            $page,
            1,
            0
        );
    }

    // =========================================================
    // AMBIL TOTAL
    // =========================================================
    $result_count = $stmt_count->get_result();

    if (!$result_count) {

        $stmt_count->close();

        responseError(
            "Gagal membaca jumlah data.",
            $page,
            1,
            0
        );
    }

    $data_count = $result_count->fetch_assoc();

    $total_data = (int)($data_count['total'] ?? 0);

    $stmt_count->close();

    // =========================================================
    // TOTAL HALAMAN
    // =========================================================
    $total_page = ($total_data > 0)
        ? (int)ceil($total_data / $batas)
        : 1;

    // =========================================================
    // VALIDASI PAGE TERHADAP TOTAL PAGE
    // =========================================================
    if ($page > $total_page) {
        $page = $total_page;
    }

    // =========================================================
    // POSISI DATA
    // =========================================================
    $posisi = ($page - 1) * $batas;

    // =========================================================
    // QUERY DATA
    // =========================================================
    $sql = "
        SELECT l.id_log, l.id_akses, l.datetime_log, l.kategori_log,
            l.deskripsi_log, a.nama_akses
        FROM log AS l
        LEFT JOIN akses AS a ON a.id_akses = l.id_akses
        $where

        ORDER BY $OrderBySql $ShortBy, l.id_log $ShortBy

        LIMIT ?, ?
    ";

    // =========================================================
    // PREPARE
    // =========================================================
    $stmt = $Conn->prepare($sql);

    if (!$stmt) {

        responseError(
            "Gagal mempersiapkan query data.",
            $page,
            $total_page,
            $total_data
        );
    }

    // =========================================================
    // BIND PARAMETER DATA
    // =========================================================
    $bindTypesData  = $bindTypes . "ii";
    $bindValuesData = $bindValues;

    $bindValuesData[] = $posisi;
    $bindValuesData[] = $batas;

    $stmt->bind_param(
        $bindTypesData,
        ...$bindValuesData
    );

    // =========================================================
    // EXECUTE
    // =========================================================
    if (!$stmt->execute()) {

        $stmt->close();

        responseError(
            "Terjadi kesalahan saat mengambil data.",
            $page,
            $total_page,
            $total_data
        );
    }

    // =========================================================
    // GET RESULT
    // =========================================================
    $query = $stmt->get_result();

    if (!$query) {

        $stmt->close();

        responseError(
            "Gagal membaca hasil data.",
            $page,
            $total_page,
            $total_data
        );
    }

    // =========================================================
    // BUILD HTML
    // =========================================================
    $html = '';

    $no = $posisi + 1;

    // =========================================================
    // JIKA DATA KOSONG
    // =========================================================
    if ($query->num_rows === 0) {

        $html .= '
            <tr>
                <td colspan="5" class="text-center text-muted">
                    <small>Tidak ada data yang ditampilkan.</small>
                </td>
            </tr>
        ';

    } else {

        // =====================================================
        // LOOP DATA
        // =====================================================
        while ($data = $query->fetch_assoc()) {

            $nama = $data['nama_akses'] ?? ('User #' . $data['id_akses']);
            $datetime = $data['datetime_log'] ?? '';
            // format datetime menjadi format yang lebih mudah dibaca
            if ($datetime !== '') {
                $datetime = date('d/m/Y H:i:s', strtotime($datetime));
            }
            $html .= '<tr><td class="table-number"><small class="text-muted">' . $no . '</small></td>';
            foreach ([$nama, $data['kategori_log'], $data['deskripsi_log'], $datetime] as $index => $value) {
                $html .= ($index === 0 ? '<td class="table-title">' : '<td>') . '<small class="text-muted">'
                    . htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    . '</small></td>';
            }
            $html .= '</tr>';
            $no++;
        }
    }

    // =========================================================
    // CLOSE STATEMENT
    // =========================================================
    $stmt->close();

    // =========================================================
    // RESPONSE JSON
    // =========================================================
    echo json_encode([
        "status"     => "success",
        "html"       => $html,
        "page"       => $page,
        "total_page" => $total_page,
        "total_data" => $total_data
    ], JSON_UNESCAPED_UNICODE);

    exit;
?>
