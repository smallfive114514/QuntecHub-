<?php
class Tag extends Model
{
    protected static string $table = 'tags';

    public static function allUsed(): array
    {
        $sql = 'SELECT t.*, COUNT(ft.file_id) AS cnt
                FROM tags t
                LEFT JOIN file_tags ft ON ft.tag_id = t.id
                GROUP BY t.id
                ORDER BY cnt DESC, t.name ASC';
        return self::pdo()->query($sql)->fetchAll();
    }

    public static function firstOrCreate(string $name): int
    {
        $name = trim($name);
        $stmt = self::pdo()->prepare('SELECT id FROM tags WHERE name = ? LIMIT 1');
        $stmt->execute([$name]);
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int) $id;
        }
        self::create(['name' => $name]);
        return (int) self::pdo()->lastInsertId();
    }

    public static function attach(int $fileId, int $tagId): void
    {
        $stmt = self::pdo()->prepare('INSERT IGNORE INTO file_tags (file_id, tag_id) VALUES (?, ?)');
        $stmt->execute([$fileId, $tagId]);
    }

    public static function detach(int $fileId, int $tagId): void
    {
        $stmt = self::pdo()->prepare('DELETE FROM file_tags WHERE file_id = ? AND tag_id = ?');
        $stmt->execute([$fileId, $tagId]);
    }

    public static function sync(int $fileId, array $names): void
    {
        self::pdo()->prepare('DELETE FROM file_tags WHERE file_id = ?')->execute([$fileId]);
        foreach ($names as $n) {
            $n = trim($n);
            if ($n === '') {
                continue;
            }
            self::attach($fileId, self::firstOrCreate($n));
        }
    }
}