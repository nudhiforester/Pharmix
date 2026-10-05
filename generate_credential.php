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

    function loadFeatureReference(string $path): array
    {
        if (!is_readable($path)) {
            throw new RuntimeException('page_code_access.json tidak ditemukan atau tidak dapat dibaca.');
        }
        $json = file_get_contents($path);
        if ($json === false) {
            throw new RuntimeException('page_code_access.json tidak dapat dibaca.');
        }
        try {
            $categories = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $error) {
            throw new RuntimeException('JSON referensi fitur tidak valid: ' . $error->getMessage());
        }
        if (!is_array($categories) || $categories === [] || array_keys($categories) !== range(0, count($categories) - 1)) {
            throw new RuntimeException('Referensi fitur harus berupa daftar kategori yang tidak kosong.');
        }

        $features = [];
        $codes = [];
        foreach ($categories as $category) {
            if (!is_array($category) || !isset($category['page_category'], $category['page_member'])
                || !is_string($category['page_category']) || trim($category['page_category']) === ''
                || mb_strlen($category['page_category'], 'UTF-8') > 50
                || !is_array($category['page_member']) || $category['page_member'] === []
                || array_keys($category['page_member']) !== range(0, count($category['page_member']) - 1)) {
                throw new RuntimeException('Kategori atau daftar page_member tidak valid.');
            }
            foreach ($category['page_member'] as $member) {
                foreach (['page_code' => 32, 'page_name' => 100, 'page_description' => null, 'page_url' => null] as $field => $limit) {
                    if (!is_array($member) || !isset($member[$field]) || !is_string($member[$field])
                        || trim($member[$field]) === ''
                        || ($limit !== null && mb_strlen($member[$field], 'UTF-8') > $limit)) {
                        throw new RuntimeException('Atribut fitur tidak valid: ' . $field);
                    }
                }
                if ($member['page_code'] !== trim($member['page_code'])
                    || !preg_match('/^[a-zA-Z0-9]+$/D', $member['page_code'])) {
                    throw new RuntimeException('page_code harus berupa kode alfanumerik tanpa spasi.');
                }
                // Kolom kode menggunakan collation yang tidak membedakan huruf besar/kecil.
                $codeKey = strtolower($member['page_code']);
                if (isset($codes[$codeKey])) {
                    throw new RuntimeException('page_code duplikat: ' . $member['page_code']);
                }
                $codes[$codeKey] = true;
                $features[] = [
                    'kode' => $member['page_code'],
                    'nama' => $member['page_name'],
                    'kategori' => $category['page_category'],
                    'keterangan' => $member['page_description'],
                ];
            }
        }
        return $features;
    }

    try {
        // Validasi seluruh referensi sebelum koneksi database maupun pembuatan akun.
        $features = loadFeatureReference(__DIR__ . DIRECTORY_SEPARATOR . 'page_code_access.json');
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

        $roleOptions = [];
        while ($role = $roles->fetch_assoc()) {
            $roleOptions[] = $role;
        }
        $roles->free();

        $createInitialRole = count($roleOptions) === 0;
        if ($createInitialRole) {
            $selectedRole = [
                'uuid_akses_entitas' => bin2hex(random_bytes(16)),
                'akses' => 'Administrator',
            ];
            fwrite(STDOUT, "Entitas akses Administrator akan dibuat untuk akun pertama.\n");
        } else {
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
        }

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

        // Entitas awal disimpan dalam transaksi yang sama dengan akun dan fitur.
        if ($createInitialRole) {
            $roleCheck = $Conn->query('SELECT uuid_akses_entitas FROM akses_entitas LIMIT 1 FOR UPDATE');
            $hasRole = $roleCheck->num_rows > 0;
            $roleCheck->free();
            if ($hasRole) {
                throw new RuntimeException('Entitas akses sudah dibuat oleh proses lain. Jalankan ulang generator.');
            }
            $roleDescription = 'Administrator awal dengan akses ke seluruh fitur aplikasi.';
            $insertRole = $Conn->prepare(
                'INSERT INTO akses_entitas (uuid_akses_entitas, akses, keterangan) VALUES (?, ?, ?)'
            );
            $insertRole->bind_param('sss', $selectedRole['uuid_akses_entitas'], $selectedRole['akses'], $roleDescription);
            $insertRole->execute();
            $insertRole->close();
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

        $findFeature = $Conn->prepare('SELECT id_akses_fitur FROM akses_fitur WHERE kode = ? FOR UPDATE');
        $insertFeature = $Conn->prepare(
            'INSERT INTO akses_fitur (kode, nama, kategori, keterangan) VALUES (?, ?, ?, ?)'
        );
        $updateFeature = $Conn->prepare(
            'UPDATE akses_fitur SET nama = ?, kategori = ?, keterangan = ? WHERE id_akses_fitur = ?'
        );
        foreach ($features as $feature) {
            $findFeature->bind_param('s', $feature['kode']);
            $findFeature->execute();
            $existing = $findFeature->get_result();
            if ($existing->num_rows > 1) {
                throw new RuntimeException('Kode fitur duplikat pada database: ' . $feature['kode']);
            }
            if ($existing->num_rows === 1) {
                $featureId = (int) $existing->fetch_assoc()['id_akses_fitur'];
                $updateFeature->bind_param('sssi', $feature['nama'], $feature['kategori'], $feature['keterangan'], $featureId);
                $updateFeature->execute();
            } else {
                $insertFeature->bind_param('ssss', $feature['kode'], $feature['nama'], $feature['kategori'], $feature['keterangan']);
                $insertFeature->execute();
            }
            $existing->free();
        }
        $findFeature->close();
        $insertFeature->close();
        $updateFeature->close();

        // Inisiasi seluruh pasangan entitas-fitur tanpa menggandakan relasi yang sudah ada.
        $Conn->query(
            'INSERT INTO akses_referensi (uuid_akses_entitas, id_akses_fitur)
             SELECT e.uuid_akses_entitas, f.id_akses_fitur
             FROM akses_entitas AS e CROSS JOIN akses_fitur AS f
             WHERE NOT EXISTS (
                 SELECT 1 FROM akses_referensi AS r
                 WHERE r.uuid_akses_entitas = e.uuid_akses_entitas
                   AND r.id_akses_fitur = f.id_akses_fitur
             )'
        );
        $referenceCount = $Conn->affected_rows;
        $Conn->query(
            'INSERT INTO akses_ijin (id_akses, id_akses_fitur, kode, nama, kategori)
             SELECT a.id_akses, f.id_akses_fitur, f.kode, f.nama, f.kategori
             FROM akses AS a CROSS JOIN akses_fitur AS f
             WHERE NOT EXISTS (
                 SELECT 1 FROM akses_ijin AS i
                 WHERE i.id_akses = a.id_akses AND i.id_akses_fitur = f.id_akses_fitur
             )'
        );
        $permissionCount = $Conn->affected_rows;

        $Conn->commit();
        $transactionStarted = false;
        $Conn->query("SELECT RELEASE_LOCK('pharmix_generate_credential')");
        $Conn->close();

        fwrite(STDOUT, sprintf("Credential berhasil dibuat (id_akses: %d).\n", $newAccountId));
        fwrite(STDOUT, sprintf("Inisiasi selesai: %d fitur JSON, %d referensi baru, %d izin baru.\n", count($features), $referenceCount, $permissionCount));
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
        if (isset($Conn) && $Conn instanceof mysqli) {
            if ($transactionStarted) {
                $Conn->rollback();
            }
            $Conn->close();
        }
        fwrite(STDERR, "{$error->getMessage()}\n");
        exit(1);
    }
