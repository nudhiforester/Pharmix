<?php

    if (PHP_SAPI !== 'cli') {
        http_response_code(404);
        exit("Not Found\n");
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    date_default_timezone_set('Asia/Jakarta');

    $transactionStarted = false;

    function prompt(string $label): string
    {
        fwrite(STDOUT, $label);
        $value = fgets(STDIN);

        if ($value === false) {
            throw new RuntimeException('Input tidak dapat dibaca.');
        }

        return trim($value);
    }

    try {
        require __DIR__ . DIRECTORY_SEPARATOR . '_Config' . DIRECTORY_SEPARATOR . 'Connection.php';
        $Conn->set_charset('utf8mb4');

        $lockResult = $Conn->query(
            "SELECT GET_LOCK('pharmix_generate_credential', 10) AS acquired"
        );
        $lockAcquired = (int) $lockResult->fetch_assoc()['acquired'] === 1;
        $lockResult->free();

        if (!$lockAcquired) {
            fwrite(STDERR, "Generator sedang digunakan oleh proses lain. Coba lagi nanti.\n");
            exit(1);
        }

        $result = $Conn->query('SELECT COUNT(*) AS total FROM akses');
        $accountCount = (int) $result->fetch_assoc()['total'];
        $result->free();

        if ($accountCount > 0) {
            fwrite(STDERR, "Generator hanya dapat digunakan ketika tabel akses masih kosong.\n");
            exit(1);
        }

        $roles = $Conn->query(
            'SELECT uuid_akses_entitas, akses FROM akses_entitas ORDER BY akses'
        );

        if ($roles->num_rows === 0) {
            fwrite(STDERR, "Belum ada entitas akses. Buat entitas akses terlebih dahulu.\n");
            exit(1);
        }

        $roleOptions = [];
        while ($role = $roles->fetch_assoc()) {
            $roleOptions[] = $role;
        }
        $roles->free();

        fwrite(STDOUT, "Pilih level akses:\n");
        foreach ($roleOptions as $index => $role) {
            fwrite(STDOUT, sprintf("  %d. %s\n", $index + 1, $role['akses']));
        }

        $roleNumber = filter_var(
            prompt('Nomor level akses: '),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => count($roleOptions)]]
        );

        if ($roleNumber === false) {
            fwrite(STDERR, "Pilihan level akses tidak valid.\n");
            exit(1);
        }
        $selectedRole = $roleOptions[$roleNumber - 1];

        $name = prompt('Nama: ');
        if ($name === '' || strlen($name) > 225) {
            fwrite(STDERR, "Nama wajib diisi dan maksimal 225 karakter.\n");
            exit(1);
        }

        $contact = prompt('Nomor kontak (6-20 digit): ');
        if (!preg_match('/^[0-9]{6,20}$/', $contact)) {
            fwrite(STDERR, "Nomor kontak harus terdiri dari 6-20 digit.\n");
            exit(1);
        }

        $email = prompt('Email: ');
        if (
            filter_var($email, FILTER_VALIDATE_EMAIL) === false
            || strlen($email) > 225
        ) {
            fwrite(STDERR, "Alamat email tidak valid atau melebihi 225 karakter.\n");
            exit(1);
        }

        fwrite(STDOUT, "Input password terlihat di terminal; jalankan di terminal privat.\n");
        $password = prompt('Password (6-20 karakter alfanumerik): ');
        $passwordConfirmation = prompt('Ulangi password: ');
        if (
            strlen($password) < 6
            || strlen($password) > 20
            || !preg_match('/^[a-zA-Z0-9]+$/', $password)
        ) {
            fwrite(STDERR, "Password harus terdiri dari 6-20 karakter alfanumerik.\n");
            exit(1);
        }
        if ($password !== $passwordConfirmation) {
            fwrite(STDERR, "Password dan konfirmasi tidak sama.\n");
            exit(1);
        }

        $Conn->begin_transaction();
        $transactionStarted = true;

        $result = $Conn->query('SELECT id_akses FROM akses LIMIT 1 FOR UPDATE');
        $hasAccount = $result->num_rows > 0;
        $result->free();

        if ($hasAccount) {
            $Conn->rollback();
            $transactionStarted = false;
            fwrite(STDERR, "Akun sudah dibuat oleh proses lain. Generator dibatalkan.\n");
            exit(1);
        }

        $checkEmail = $Conn->prepare('SELECT id_akses FROM akses WHERE email_akses = ? LIMIT 1');
        $checkEmail->bind_param('s', $email);
        $checkEmail->execute();
        $emailExists = $checkEmail->get_result()->num_rows > 0;
        $checkEmail->close();

        if ($emailExists) {
            $Conn->rollback();
            $transactionStarted = false;
            fwrite(STDERR, "Email tersebut sudah digunakan.\n");
            exit(1);
        }

        $now = date('Y-m-d H:i:s');
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if ($passwordHash === false) {
            throw new RuntimeException('Password tidak dapat diproses.');
        }

        $insert = $Conn->prepare(
            'INSERT INTO akses (
                uuid_akses_entitas,
                nama_akses,
                kontak_akses,
                email_akses,
                password,
                image_akses,
                akses,
                datetime_daftar,
                datetime_update
            ) VALUES (?, ?, ?, ?, ?, NULL, ?, ?, ?)'
        );
        $insert->bind_param(
            'ssssssss',
            $selectedRole['uuid_akses_entitas'],
            $name,
            $contact,
            $email,
            $passwordHash,
            $selectedRole['akses'],
            $now,
            $now
        );
        $insert->execute();
        $newAccountId = $insert->insert_id;
        $insert->close();

        $Conn->commit();
        $transactionStarted = false;
        $Conn->query("SELECT RELEASE_LOCK('pharmix_generate_credential')");
        $Conn->close();

        fwrite(STDOUT, sprintf("Credential berhasil dibuat (id_akses: %d).\n", $newAccountId));
    } catch (mysqli_sql_exception $error) {
        if (isset($Conn) && $Conn instanceof mysqli) {
            if ($transactionStarted) {
                $Conn->rollback();
            }
            $Conn->close();
        }

        fwrite(STDERR, "Kesalahan database: {$error->getMessage()}\n");
        exit(1);
    } catch (RuntimeException $error) {
        fwrite(STDERR, "{$error->getMessage()}\n");
        exit(1);
    }
