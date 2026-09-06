<?php
class Version extends Model
{
    protected static string $table = 'file_versions';

    public static function of(int $fileId): array
    {
        $sql = 'SELECT * FROM file_versions WHERE file_id = ? ORDER BY version_no DESC';
        return Database::run($sql, [$fileId])->fetchAll();
    }

    public static function nextNo(int $fileId): int
    {
        $sql = 'SELECT COALESCE(MAX(version_no),0)+1 FROM file_versions WHERE file_id = ?';
        return (int) Database::run($sql, [$fileId])->fetchColumn();
    }
}