<?php
namespace app\modules\admin\models;

use yii\db\ActiveRecord;

/**
 * Class ItemToCategoryRelation
 * @package app\modules\admin\models
 *
 * @property int $category_id
 * @property int $item_id
 */
class ItemToCategoryRelation extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'item_to_category_relation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['category_id', 'item_id'], 'integer'],
            [['category_id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => 'id'],
            [['item_id'], 'exist', 'targetClass' => Items::class, 'targetAttribute' => 'id'],
            [['category_id', 'item_id'], 'unique', 'targetAttribute' => ['category_id', 'item_id']],
        ];
    }

    public function getCategory()
    {
        return $this->hasOne(CategoryDescription::class, ['id' => 'category_id']);
    }
}