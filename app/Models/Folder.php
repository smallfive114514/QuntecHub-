<?php
class Folder extends Model
{
    protected static string $table = 'folders';

    public static function children(int $projectId, int $parentId = 0): array
    {
        $sql = 'SELECT * FROM folders WHERE project_id = ? AND parent_id = ? ORDER BY name ASC';
        return Database::run($sql, [$projectId, $parentId])->fetchAll();
    }

    public static function breadcrumb(int $folderId): array
    {
        $chain = [];
        $id = $folderId;
        $guard = 0;
        while ($id > 0 && $guard++ < 30) {
            $f = self::find($id);
            if (!$f) {
                break;
            }
            array_unshift($chain, $f);
            $id = (int) $f['parent_id'];
        }
        return $chain;
    }
}