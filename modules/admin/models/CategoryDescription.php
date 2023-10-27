<?php
namespace app\modules\admin\models;

use yii\db\ActiveRecord;

/**
 * Class CategoryDescription
 * @package app\modules\admin\models
 *
 * @property int $id
 * @property string $title
 * @property string $short_description
 * @property string $description
 */
class CategoryDescription extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'category_description';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id'], 'default', 'value' => null],
            [['id'], 'integer'],
            [['id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => 'id'],
            [['title'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['short_description', 'description'], 'string'],
        ];
    }
}