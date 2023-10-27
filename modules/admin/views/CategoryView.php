<?php
namespace app\modules\admin\views;

abstract class CategoryView
{
    protected static function prepareUser(array $row, string $type): array
    {
        return [
            'id' => isset($row[$type]) ? $row[$type]['id'] : $row[$type.'_id'],
            'username' => isset($row[$type]) ? $row[$type]['username'] : $row[$type.'_username'],
            'blocked' => isset($row[$type]) ? (bool) $row[$type]['blocked'] : (bool) $row[$type.'_blocked'],
            'valid_until' => isset($row[$type]) ? $row[$type]['valid_until'] : $row[$type.'_valid_until'],
        ];
    }

    protected static function prepareRow(array $row): array
    {
        return [
            'id' => $row['id'],
            'parent_id' => $row['parent_id'],
            'published' => (bool) $row['published'],
            'title' => is_array($row['description']) ? $row['description']['title'] : $row['title'],
            'short_description' => is_array($row['description']) ? $row['description']['short_description'] : $row['short_description'],
            'description' => is_array($row['description']) ? $row['description']['description'] : $row['description'],
            'created_date' => (new \DateTimeImmutable($row['created_date'], new \DateTimeZone(\Yii::$app->timeZone)))->format('c'),
            'created_by' => static::prepareUser($row, 'author'),
            'modified_date' => (new \DateTimeImmutable($row['created_date'], new \DateTimeZone(\Yii::$app->timeZone)))->format('c'),
            'modified_by' => static::prepareUser($row, 'editor'),
        ];
    }

    public static function prepareItem(array $row): array
    {
        return static::prepareRow($row);
    }

    public static function prepareItemArray(array $rows): array
    {
        $items = [];
        foreach($rows as $row) {
            $items[] = static::prepareRow($row);
        }

        return $items;
    }
}