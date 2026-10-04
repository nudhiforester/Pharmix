# Membuat credential pertama

Generator membuat satu akun pertama pada tabel `akses`. Jalankan dari terminal
di direktori proyek:

```powershell
php .\generate_credential.php
```

Pastikan:

- PHP CLI dan ekstensi `mysqli` tersedia.
- Database `pharmix` sudah diimpor dan pengaturan koneksi pada
  `_Config/Connection.php` sudah benar.
- Minimal satu entitas akses tersedia pada tabel `akses_entitas`; generator
  akan meminta pilihan level akses.
- Tabel `akses` masih kosong. Generator menolak berjalan jika akun sudah ada.

Masukkan nama, nomor kontak 6-20 digit, email, dan password 6-20 karakter
alfanumerik sesuai aturan formulir aplikasi. Password dimasukkan melalui
terminal, tidak melalui argumen command line, dan disimpan menggunakan
`password_hash()` dengan `PASSWORD_DEFAULT`, sama seperti validasi login
menggunakan `password_verify()`. Input password terlihat saat diketik, jadi
jalankan script hanya di terminal privat.

Script hanya dapat dijalankan melalui PHP CLI, bukan melalui browser.
