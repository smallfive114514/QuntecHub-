<?php
class Project extends Model
{
    protected static string $table = 'projects';

    public static function withCounts(): array
    {
        $sql = "SELECT p.*,
                    (SELECT COUNT(*) FROM files f WHERE f.project_id = p.id) AS file_count,
                    (SELECT COALESCE(SUM(f.size),0) FROM files f WHERE f.project_id = p.id) AS total_size
                FROM projects p
                ORDER BY p.sort ASC, p.id ASC";
        return self::pdo()->query($sql)->fetchAll();
    }
}