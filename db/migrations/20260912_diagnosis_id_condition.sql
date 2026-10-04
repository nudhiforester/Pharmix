-- Jalankan pada instalasi lama; aman jika kolom sudah tersedia.
SET @condition_column_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'diagnosis' AND COLUMN_NAME = 'id_condition'
);
SET @condition_migration_sql = IF(
    @condition_column_exists = 0,
    'ALTER TABLE diagnosis ADD COLUMN id_condition varchar(255) DEFAULT NULL COMMENT ''ID resource Condition dari SATUSEHAT'' AFTER diagnosis_code',
    'SELECT ''Kolom diagnosis.id_condition sudah tersedia'' AS message'
);
PREPARE condition_migration FROM @condition_migration_sql;
EXECUTE condition_migration;
DEALLOCATE PREPARE condition_migration;
