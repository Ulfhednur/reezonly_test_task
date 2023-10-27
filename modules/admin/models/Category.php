<?php
namespace app\modules\admin\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use app\modules\user\models\User;

/**
 * Class Category
 * @package app\modules\admin\models
 *
 * @property int $id
 * @property bool $published
 * @property int $price
 * @property int $created_by
 * @property string $created_date
 * @property int $modified_by
 * @property string $modified_date
 *
 * @property-read CategoryDescription $description
 * @property-read User $author
 * @property-read User $editor
 * @property-read Category $parent
 * @property-read Category[] $children
 */
class Category extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'parent_id', 'created_by', 'modified_by'], 'integer'],
            [['id'], 'default', 'value' => null],
            [['parent_id'], 'default', 'value' => null],
            [['parent_id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => 'id'],
            [['published'], 'default', 'value' => Items::PUBLISHED],
            [['published'], 'in', 'range' => [Items::PUBLISHED, Items::UNPUBLISHED]],
            [['created_date', 'modified_date'], 'date', 'format' => 'php:Y-m-d H:i:s'],
            [['created_by', 'modified_by'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeValidate(): bool
    {
        $this->modified_date = (new \DateTimeImmutable('now', new \DateTimeZone(\Yii::$app->timeZone)))->format('Y-m-d H:i:s');
        $this->modified_by = \Yii::$app->user->identity->id;
        if (empty($this->id)) {
            $this->created_date = $this->modified_date;
            $this->created_by = $this->modified_by;
        }
        return parent::beforeValidate();
    }

    /**
     * @return ActiveQuery
     */
    public function getDescription(): ActiveQuery
    {
        return $this->hasOne(CategoryDescription::class, ['id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAuthor(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * @return ActiveQuery
     */
    public function getEditor(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'modified_by']);
    }

    /**
     * @return ActiveQuery
     */
    public function getParent(): ActiveQuery
    {
        return $this->hasOne(self::class, ['parent_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getChildren(): ActiveQuery
    {
        return $this->hasMany(self::class, ['id' => 'parent_id']);
    }
}