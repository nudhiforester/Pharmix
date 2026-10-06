<?php
// Endpoint untuk menyegarkan kode setiap kali modal tambah induk dibuka.
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../_Config/Connection.php';
require_once __DIR__ . '/../../_Config/GlobalFunction.php';
require_once __DIR__ . '/../../_Config/Session.php';
require_once __DIR__ . '/KodeAkunBerikutnya.php';
if (empty($SessionIdAkses)) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi akses sudah berakhir. Silahkan login ulang.']);
    exit;
}
try {
    echo json_encode(['status' => 'success', 'kode' => GetKodeAkunBerikutnya($Conn)]);
} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan saat menyiapkan kode akun.']);
}
