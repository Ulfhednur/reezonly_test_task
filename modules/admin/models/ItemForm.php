<?php
namespace app\modules\admin\models;

use OpenApi\Annotations as OA;
use yii\base\Model;

/**
 *
 * @OA\Schema(
 *     schema="itemForm",
 *     @OA\Property(property="id", type="integer", example="2", description="ID"),
 *     @OA\Property(property="published", type="boolean", example="true", description="Флаг публикации"),
 *     @OA\Property(property="price", type="integer", example="100500", description="Цена в копейках"),
 *     @OA\Property(property="title", type="string", example="Заголовок", description="Заголовок объекта"),
 *     @OA\Property(property="short_description", type="string", example="Краткое описание", description="Краткое описание объекта. Выводится в списке (под)категорий"),
 *     @OA\Property(property="description", type="string", example="Описание", description="Полное описание объекта. Выводится в карточке объекта"),
 *     @OA\Property(property="category_relation", type="array", description="Список id категорий в которых объект присутствует", @OA\Items(type="integer")),
 * ),
 * 
 * Форма для сохранения элемента каталога
 *
 * Class ItemToCategoryRelation
 * @package app\modules\admin\models
 *
 * @property int $id
 * @property bool $published
 * @property int $price
 * @property string $title
 * @property string $short_description
 * @property string $description
 * @property array $category_relation
 */
class ItemForm extends Model
{
    public ?int $id = null;
    public bool $published = true;
    public int $price;
    public string $title;
    public string $description;
    public string $short_description;
    public array $category_relation = [];

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
            [['price'], 'required'],
            [['price'], 'integer'],
            [['published'], 'boolean'],
            [['published'], 'default', 'value' => true],
            [['title'], 'string', 'max' => 255],
            [['short_description', 'description'], 'string'],
            [['category_relation'], 'each', 'rule' => ['in', 'range' => Category::find()->select('id')->column()]],
        ];
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    public function save(): bool
    {
        if ($this->validate()) {
            return (\Yii::$app->getDb()->transaction(
                function () {
                    if (!empty($this->id)) {
                        $item = Items::findOne(['id' => $this->id]);
                        if (empty($item)) {
                            throw new \Exception('Элемент каталога не найден', 404);
                        }
                        $itemDescription = ItemsDescription::findOne(['id' => $this->id]);
                    } else {
                        $item = new Items();
                        $itemDescription = new ItemsDescription();
                    }
                    $item->price = $this->price;
                    $item->published = (int) $this->published;
                    if (!$item->save()) {
                        $this->addErrors($item->getErrors());
                        throw new \Exception('Ошибка сохранения элемента каталога', 417);
                    }

                    if (empty($this->id)) {
                        $itemDescription->id = $item->id;
                        $this->id = $item->id;
                    }

                    $itemDescription->title = $this->title;
                    $itemDescription->short_description = $this->short_description;
                    $itemDescription->description = $this->description;

                    if (!$itemDescription->save()) {
                        $this->addErrors($itemDescription->getErrors());
                        throw new \Exception('Ошибка сохранения описания элемента каталога', 417);
                    }

                    ItemToCategoryRelation::deleteAll(['item_id' => $item->id]);

                    if (!empty($this->category_relation)) {
                        foreach ($this->category_relation as $category_id) {
                            $relation = new ItemToCategoryRelation([
                                                                       'item_id' => $item->id,
                                                                       'category_id' => $category_id,
                                                                   ]);
                            if (!$relation->save()){
                                $this->addErrors($relation->getErrors());
                                throw new \Exception('Ошибка добавления элемента каталога в категорию', 417);
                            }
                        }
                    }
                }
            )) ?? true;
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
            return \Yii::$app->getDb()->transaction(
                function () {
                    ItemToCategoryRelation::deleteAll(['item_id' => $this->id]);
                    ItemsDescription::deleteAll(['item_id' => $this->id]);
                    Items::deleteAll(['item_id' => $this->id]);
                }
            );
        }
        throw new \Exception('Элемент каталога не найден', 404);
    }
}