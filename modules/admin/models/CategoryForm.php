<?php
namespace app\modules\admin\models;

use OpenApi\Annotations as OA;
use yii\base\Model;

/**
 *
 * @OA\Schema(
 *     schema="categoryForm",
 *     @OA\Property(property="id", type="integer", example="2", description="ID"),
 *     @OA\Property(property="parent_id", type="integer|null", example="4", description="ID родителя"),
 *     @OA\Property(property="published", type="boolean", example="true", description="Флаг публикации"),
 *     @OA\Property(property="title", type="string", example="Заголовок", description="Заголовок категории"),
 *     @OA\Property(property="short_description", type="string", example="Краткое описание", description="Краткое описание категории. Выводится в списке (под)категорий"),
 *     @OA\Property(property="description", type="string", example="Описание", description="Полное описание категории. Выводится в карточке категории"),
 * ),
 *
 * Форма для сохранения категорий каталога
 * 
 * Class ItemToCategoryRelation
 * @package app\modules\admin\models
 *
 * @property int $id
 * @property int $parent_id
 * @property bool $published
 * @property string $title
 * @property string $short_description
 * @property string $description
 */
class CategoryForm extends Model
{
    public int $id;
    public int|null $parent_id = null;
    public bool $published = true;
    public string $title;
    public string $description;
    public string $short_description;


    /**
     * {@inheritdoc}
     */
    public function formName(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['parent_id'], 'default', 'value' => null],
            [['parent_id'], 'integer'],
            [['parent_id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => 'id'],
            [['published'], 'boolean'],
            [['published'], 'default', 'value' => true],
            [['title'], 'string', 'max' => 255],
            [['short_description', 'description'], 'string'],
        ];
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    public function save(): bool
    {
        if ($this->validate()) {
            return \Yii::$app->getDb()->transaction(
                function () {
                    if (!empty($this->id)) {
                        $category = Category::findOne(['id' => $this->id]);
                        if (empty($category)) {
                            throw new \Exception('Категория не найдена', 404);
                        }
                        $categoryDescription = CategoryDescription::findOne(['id' => $this->id]);
                    } else {
                        $category = new Category();
                        $categoryDescription = new CategoryDescription();
                    }
                    $category->published = (int) $this->published;
                    $category->parent_id = $this->parent_id;

                    if (!$category->save()) {
                        $this->addErrors($category->getErrors());
                        throw new \Exception('Ошибка сохранения категории', 417);
                    }

                    if (empty($this->id)) {
                        $categoryDescription->id = $category->id;
                        $this->id = $category->id;
                    }

                    $categoryDescription->title = $this->title;
                    $categoryDescription->short_description = $this->short_description;
                    $categoryDescription->description = $this->description;

                    if (!$categoryDescription->save()) {
                        $this->addErrors($categoryDescription->getErrors());
                        throw new \Exception('Ошибка сохранения описания категории', 417);
                    }
                }
            ) ?? true;
        }

        return false;
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    public function delete(): bool
    {
        if ($item = Items::findOne(['id' => $this->id])) {
            if (Category::findOne(['parent_id' => $this->id])) {
                throw new \Exception('У категории есть подкатегории', 405);
            }

            if (ItemToCategoryRelation::findOne(['category_id' => $this->id])) {
                throw new \Exception('В категории есть элементы', 405);
            }

            return \Yii::$app->getDb()->transaction(
                function () {
                    CategoryDescription::deleteAll(['item_id' => $this->id]);
                    Category::deleteAll(['item_id' => $this->id]);
                }
            );
        }
        throw new \Exception('Элемент каталога не найден', 404);
    }
}