<?php
namespace app\modules\admin\views;

use app\modules\admin\models\ItemToCategoryRelation;

abstract class ItemsView extends CategoryView
{
    protected static function prepareRow(array $row): array
    {
        return [
            'id' => $row['id'],
            'published' => (bool) $row['published'],
            'price' => $row['price'],
            'categories' => [],
            'title' => is_array($row['description']) ? $row['description']['title'] : $row['title'],
            'short_description' => is_array($row['description']) ? $row['description']['short_description'] : $row['short_description'],
            'description' => is_array($row['description']) ? $row['description']['description'] : $row['description'],
            'created_date' => (new \DateTimeImmutable($row['created_date'], new \DateTimeZone(\Yii::$app->timeZone)))->format('c'),
            'created_by' => static::prepareUser($row, 'author'),
            'modified_date' => (new \DateTimeImmutable($row['created_date'], new \DateTimeZone(\Yii::$app->timeZone)))->format('c'),
            'modified_by' => static::prepareUser($row, 'editor'),
        ];
    }

    protected static function addCategories($items)
    {
        $categories = ItemToCategoryRelation::find()
            ->joinWith('category')
            ->where(['IN', 'item_id', array_keys($items)]);

        foreach($categories->each() as $category) {
            $items[$category->item_id]['categories'][] = [
                'id' => $category->category_id,
                'title' => $category->category->title,
            ];
        }

        return array_values($items);
    }

    public static function prepareItem(array $row): array
    {
        $item = static::prepareRow($row);
        return self::addCategories([$row['id'] => $item])[0];
    }

    public static function prepareItemArray(array $rows): array
    {
        $items = [];
        foreach($rows as $row) {
            $items[$row['id']] = static::prepareRow($row);
        }

        return self::addCategories($items);
    }
}