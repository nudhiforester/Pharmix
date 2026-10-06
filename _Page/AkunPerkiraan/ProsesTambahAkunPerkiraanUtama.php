<?php
    header('Content-Type: application/json; charset=utf-8');

    // Response sesuai dengan handler AJAX di AkunPerkiraan.js
    function Response($status, $message){
        echo json_encode([
            'status' => $status,
            'message' => $message
        ]);
        exit;
    }

    //Koneksi
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    require_once __DIR__ . '/KodeAkunBerikutnya.php';
    date_default_timezone_set('Asia/Jakarta');

    // Waktu Sekarang
    $now = date('Y-m-d H:i:d');
    
    //Validasi Variebl yang ditangkap
    //Cek Akses
    if(empty($SessionIdAkses)){
        Response('error', 'Sesi Akses Sudah Berakhir. Silahkan Login Ulang!');
    }else{
        if(empty($_POST['kode']) || !is_scalar($_POST['kode'])){
            Response('error', 'Kode Perkiraan Tidak Boleh Kosong.');
        }else{
            if(empty($_POST['nama'])){
                Response('error', 'Nama Perkiraan Tidak Boleh Kosong.');
            }else{
                if(empty($_POST['saldo_normal'])){
                    Response('error', 'Saldo Normal Akun Perkiraan Tidak Boleh Kosong.');
                }else{
                    $kode=$_POST['kode'];
                    $nama=$_POST['nama'];
                    $saldo_normal=$_POST['saldo_normal'];
                    //Bersihkan Variabel
                    $kode=validateAndSanitizeInput($kode);
                    $nama=validateAndSanitizeInput($nama);
                    $saldo_normal=validateAndSanitizeInput($saldo_normal);
                    // Validasi kode angka dan urutan otomatis pada sisi server.
                    if (!preg_match('/^[0-9]+$/D', $kode)) {
                        Response('error', 'Kode akun hanya boleh berisi angka.');
                    }
                    try {
                        $kodeBerikutnya = GetKodeAkunBerikutnya($Conn);
                    } catch (Throwable $e) {
                        Response('error', 'Terjadi kesalahan saat menyiapkan kode akun.');
                    }
                    if ($kode !== $kodeBerikutnya) {
                        Response('error', 'Urutan kode sudah berubah. Tutup dan buka kembali form tambah akun.');
                    }
                    //Validasi Kode Sama/Duplikat
                    $ValidasiKodeSama=GetDetailData($Conn,'akun_perkiraan','kode',$kode,'kode');
                    //Apabila akun belum ada, atau duplikat
                    if(!empty($ValidasiKodeSama)){
                        Response('error', 'Kode yang anda gunakan sudah ada, silahkan gunakan kode lain.');
                    }else{
                        //Lakukan Input data baru ke akun_perkiraan
                        $InputDataPerkiraan="INSERT INTO akun_perkiraan (
                            kode,
                            nama,
                            level,
                            saldo_normal,
                            kd1
                        ) VALUES (
                            '$kode',
                            '$nama',
                            '1',
                            '$saldo_normal',
                            '$kode'
                        )";
                        $HasilInputDataPerkiraan=mysqli_query($Conn, $InputDataPerkiraan);
                        if($HasilInputDataPerkiraan){
                            $kategori_log="Akun Perkiraan";
                            $deskripsi_log="Tambah Akun Perkiraan";
                            $InputLog=addLog($Conn,$SessionIdAkses,$now,$kategori_log,$deskripsi_log);
                            if($InputLog=="Success"){
                                Response('success', 'Data Akun Perkiraan Berhasil Ditambahkan.');
                            }else{
                                Response('error', 'Terjadi kesalahan pada saat menyimpan log.');
                            }
                            
                        }else{
                            Response('error', 'Terjadi kesalahan pada saat menyimpan data.');
                        }
                    }
                }
            }
        }
    }
?>

