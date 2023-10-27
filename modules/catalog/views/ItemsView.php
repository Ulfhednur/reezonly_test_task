<?php
namespace app\modules\catalog\views;

use app\modules\admin\models\Items;

abstract class ItemsView
{
    public static function prepareItem(Items|array $item): array
    {
        if (!is_array($item)) {
            $item = $item->description->toArray();
        }
        $result = [
            'id' => $item['id'],
            'price' => number_format(round($item['price'] / 100, 2), 2, ',', ' '),
            'title' => isset($item['description']) ? $item['description']['title'] : $item['title'],
        ];
        if(isset($item['description'])) {
            $result['description'] = $item['description']['description'];
        } else {
            $result['short_description'] = $item['short_description'];
        }
        return $result;
    }

    public static function prepareItemArray(array $rows): array
    {
        $items = [];
        foreach($rows as $row) {
            $items[] = static::prepareItem($row);
        }

        return $items;
    }
}