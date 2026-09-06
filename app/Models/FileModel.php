<?php
class FileModel extends Model
{
    protected static string $table = 'files';

    public static function inFolder(int $projectId, int $folderId): array
    {
        $sql = 'SELECT * FROM files WHERE project_id = ? AND folder_id = ? ORDER BY name ASC';
        return Database::run($sql, [$projectId, $folderId])->fetchAll();
    }

    public static function recent(int $limit = 10): array
    {
        $sql = 'SELECT * FROM files ORDER BY created_at DESC LIMIT '.(int) $limit;
        return self::pdo()->query($sql)->fetchAll();
    }

    public static function sameName(int $projectId, int $folderId, string $name): ?array
    {
        $sql = 'SELECT * FROM files WHERE project_id = ? AND folder_id = ? AND name = ? LIMIT 1';
        $row = Database::run($sql, [$projectId, $folderId, $name])->fetch();
        return $row ?: null;
    }

    public static function tags(int $fileId): array
    {
        $sql = 'SELECT t.* FROM tags t
                INNER JOIN file_tags ft ON ft.tag_id = t.id
                WHERE ft.file_id = ? ORDER BY t.name ASC';
        return Database::run($sql, [$fileId])->fetchAll();
    }

    public static function search(string $q): array
    {
        $like = '%'.$q.'%';
        $sql = "SELECT DISTINCT f.* FROM files f
                LEFT JOIN file_tags ft ON ft.file_id = f.id
                LEFT JOIN tags t ON t.id = ft.tag_id
                WHERE f.name LIKE ? OR f.note LIKE ? OR t.name LIKE ?
                ORDER BY f.created_at DESC";
        return Database::run($sql, [$like, $like, $like])->fetchAll();
    }

    public static function totalSize(): int
    {
        return (int) self::pdo()->query('SELECT COALESCE(SUM(size),0) FROM files')->fetchColumn();
    }

    public static function countAll(): int
    {
        return (int) self::pdo()->query('SELECT COUNT(*) FROM files')->fetchColumn();
    }
}