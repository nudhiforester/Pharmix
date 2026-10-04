<?php
    // Routing Form Filter Periode Data
    header('Content-Type: text/html; charset=utf-8');
    date_default_timezone_set('Asia/Jakarta');

    $periode_data = $_POST['periode_data'] ?? 'Semua';

    if (!in_array($periode_data, ['Tahunan', 'Bulanan', 'Harian'], true)) {
        exit;
    }

    if ($periode_data === 'Tahunan' || $periode_data === 'Bulanan') {
?>
<div class="row mb-3">
    <div class="col-md-12">
        <label for="tahun">Tahun</label>
        <input type="text" name="tahun" id="tahun" class="form-control"
            inputmode="numeric" pattern="[0-9]{4}" maxlength="4"
            title="Masukkan tahun dalam 4 digit angka" value="<?= date('Y') ?>"
            oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
    </div>
</div>
<?php
    }

    if ($periode_data === 'Bulanan') {
        $daftar_bulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
?>
<div class="row mb-3">
    <div class="col-md-12">
        <label for="bulan">Bulan</label>
        <select name="bulan" id="bulan" class="form-control" required>
            <?php foreach ($daftar_bulan as $angka => $nama) { ?>
                <option value="<?= $angka ?>"<?= (string)$angka === date('m') ? ' selected' : '' ?>><?= $nama ?></option>
            <?php } ?>
        </select>
    </div>
</div>
<?php
    }

    if ($periode_data === 'Harian') {
?>
<div class="row mb-3">
    <div class="col-md-12">
        <label for="tanggal">Tanggal</label>
        <input type="date" name="tanggal" id="tanggal" class="form-control"
            value="<?= date('Y-m-d') ?>" required>
    </div>
</div>
<?php } ?>
