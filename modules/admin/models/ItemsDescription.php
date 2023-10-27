<?php
namespace app\modules\admin\models;

use yii\db\ActiveRecord;

/**
 * Class ItemsDescription
 * @package app\modules\admin\models
 *
 * @property int $id
 * @property string $title
 * @property string $short_description
 * @property string $description
 */
class ItemsDescription extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'item_description';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id'], 'default', 'value' => null],
            [['title'], 'required'],
            [['id'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['short_description', 'description'], 'string'],
            [['id'], 'exist', 'targetClass' => Items::class, 'targetAttribute' => 'id'],
        ];
    }
}