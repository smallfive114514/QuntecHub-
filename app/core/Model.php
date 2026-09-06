<?php
abstract class Model
{
    protected static string $table;
    protected static string $pk = 'id';

    public static function table(): string
    {
        return static::$table;
    }

    public static function pdo(): PDO
    {
        return Database::pdo();
    }

    public static function find($id): ?array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM '.static::table().' WHERE '.static::$pk.' = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function all(string $order = ''): array
    {
        $sql = 'SELECT * FROM '.static::table();
        if ($order) {
            $sql .= ' ORDER BY '.$order;
        }
        return self::pdo()->query($sql)->fetchAll();
    }

    public static function create(array $data): int
    {
        $cols = array_keys($data);
        $sql = 'INSERT INTO '.static::table().' ('.implode(',', $cols).') VALUES (:'.implode(',:', $cols).')';
        Database::run($sql, $data);
        return (int) self::pdo()->lastInsertId();
    }

    public static function update($id, array $data): int
    {
        $set = [];
        foreach (array_keys($data) as $c) {
            $set[] = $c.' = :'.$c;
        }
        $sql = 'UPDATE '.static::table().' SET '.implode(',', $set).' WHERE '.static::$pk.' = :__id';
        $data['__id'] = $id;
        $stmt = Database::run($sql, $data);
        return $stmt->rowCount();
    }

    public static function delete($id): int
    {
        $stmt = self::pdo()->prepare('DELETE FROM '.static::table().' WHERE '.static::$pk.' = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}
