<?php
namespace app\modules\admin\controllers;

use app\modules\admin\models\ItemForm;
use app\modules\admin\models\Items;
use app\modules\admin\models\search\ItemsSearch;
use app\modules\admin\views\ItemsView;
use OpenApi\Annotations as OA;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use yii\db\Query;
use yii\web\NotFoundHttpException;

/**
 * @OA\Schema(
 *     schema="ItemCategory",
 *     @OA\Property(property="id", type="integer", example="2", description="ID"),
 *     @OA\Property(property="title", type="string", example="Заголовок", description="Заголовок объекта"),
 * ),
 *
 * @OA\Schema(
 *     schema="Item",
 *     @OA\Property(property="id", type="integer", example="2", description="ID"),
 *     @OA\Property(property="published", type="boolean", example="true", description="Флаг публикации"),
 *     @OA\Property(property="price", type="integer", example="100500", description="Цена в копейках"),
 *     @OA\Property(property="categories", type="array", description="Список категорий", @OA\Items(ref="#/components/schemas/ItemCategory")),
 *     @OA\Property(property="title", type="string", example="Заголовок", description="Заголовок объекта"),
 *     @OA\Property(property="short_description", type="string", example="Краткое описание", description="Краткое описание объекта. В каталоге выводится в списке"),
 *     @OA\Property(property="description", type="string", example="Описание", description="Полное описание объекта. В каталоге выводится в карточке объекта"),
 *     @OA\Property(property="created_date", type="string", example="2023-10-12Т12:56:22+03:00", description="Дата создания объекта."),
 *     @OA\Property(property="created_by", type="object",  description="Создатель объекта.", ref="#/components/schemas/Author"),
 *     @OA\Property(property="modified_date", type="string", example="2023-10-12Т12:56:22+03:00", description="Дата изменения объекта."),
 *     @OA\Property(property="modified_by", type="object",  description="Редактор объекта.", ref="#/components/schemas/Author"),
 * ),
 *
 * @OA\Get(
 *     @OA\PathItem(path="admin/item"),
 *     path="/admin/item",
 *     tags={"Admin"},
 *     operationId="/admin/item",
 *     summary="Список объектов",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="page", in="query", example="1", description="Номер страницы"),
 *     @OA\Parameter(name="per-page", in="query", example="1", description="Записей на странице"),
 *     @OA\Parameter(name="category_id", in="query", example="1", description="ID категории"),
 *     @OA\Parameter(name="title", in="query", example="Заголовок объекта", description="Поиск по названию"),
 *     @OA\Parameter(name="sort", in="query", description="сортировка", example="-title"),
 *     @OA\Response(
 *          response = 200,
 *          description = "Список объектов",
 *          @OA\JsonContent(
 *              @OA\Property(property="items", type="array", description="Список объектов", @OA\Items(ref="#/components/schemas/Item")),
 *              @OA\Property(property="_meta", type="object", description="Метаданные", ref="#/components/schemas/meta"),
 *         ),
 *     ),
 *     @OA\Response(
 *          response = 403,
 *          description = "Доступ запрещён",
 *          @OA\JsonContent(ref="#/components/schemas/HttpException"),
 *     ),
 * ),
 *
 * @OA\Get(
 *     @OA\PathItem(path="admin/item/{id}"),
 *     path="/admin/item/{id}",
 *     tags={"Admin"},
 *     operationId="/admin/item/{id}",
 *     summary="Объект",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id", in="path", example="7", description="id объекта"),
 *     @OA\Response(
 *          response = 200,
 *          description = "Объект",
 *          @OA\JsonContent(ref="#/components/schemas/Item"),
 *     ),
 *     @OA\Response(
 *          response = 403,
 *          description = "Доступ запрещён",
 *          @OA\JsonContent(ref="#/components/schemas/HttpException"),
 *     ),
 *     @OA\Response(
 *          response = 404,
 *          description = "Категория не найдена",
 *          @OA\JsonContent(ref="#/components/schemas/HttpException"),
 *     ),
 * ),
 *
 * @OA\Post(
 *     @OA\PathItem(path="admin/item"),
 *     path="/admin/item",
 *     tags={"Admin"},
 *     operationId="/admin/item/create",
 *     summary="Создание объекта",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *          required = true,
 *          request = "createItem",
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(ref="#/components/schemas/itemForm"),
 *          ),
 *     ),
 *     @OA\Response(
 *          response = 200,
 *          description = "Объект",
 *          @OA\JsonContent(ref="#/components/schemas/Item"),
 *     ),
 *     @OA\Response(
 *          response = 400,
 *          description = "Некорректно заполнены поля",
 *          @OA\JsonContent(ref="#/components/schemas/HttpException"),
 *     ),
 *     @OA\Response(
 *          response = 403,
 *          description = "Доступ запрещён",
 *          @OA\JsonContent(ref="#/components/schemas/HttpException"),
 *     ),
 * ),
 *
 * @OA\Put(
 *     @OA\PathItem(path="admin/item/{id}"),
 *     path="/admin/item/{id}",
 *     tags={"Admin"},
 *     operationId="/admin/item/update",
 *     summary="Редактирование объекта",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *          required = true,
 *          request = "updateItem",
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(ref="#/components/schemas/itemForm"),
 *          ),
 *     ),
 *     @OA\Response(
 *          response = 200,
 *          description = "Категория",
 *          @OA\JsonContent(ref="#/components/schemas/Item"),
 *     ),
 *     @OA\Response(
 *          response = 400,
 *          description = "Некорректно заполнены поля",
 *          @OA\JsonContent(ref="#/components/schemas/HttpException"),
 *     ),
 *     @OA\Response(
 *          response = 403,
 *          description = "Доступ запрещён",
 *          @OA\JsonContent(ref="#/components/schemas/HttpException"),
 *     ),
 * ),
 *
 * @OA\Delete(
 *     @OA\PathItem(path="admin/item/{id}"),
 *     path="/admin/item/{id}",
 *     tags={"Admin"},
 *     operationId="/admin/item/delete",
 *     summary="Удаление объекта",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id", in="path", example="7", description="id объекта"),
 *     @OA\Response(
 *          response = 200,
 *          description = "Объект удален",
 *          @OA\JsonContent(ref="#/components/schemas/ok"),
 *     ),
 *     @OA\Response(
 *          response = 403,
 *          description = "Доступ запрещён",
 *          @OA\JsonContent(ref="#/components/schemas/HttpException"),
 *     ),
 *     @OA\Response(
 *          response = 404,
 *          description = "Категория не найдена",
 *          @OA\JsonContent(ref="#/components/schemas/HttpException"),
 *     ),
 * ),
 * 
 * Class ItemsController
 * @package app\modules\admin\controllers
 */
class ItemsController extends AdminAbstractController
{
    public $modelClass = Items::class;
    public $modelFormClass = ItemForm::class;

    /**
     * @return array[]|object|object[]|ActiveDataProvider[]|\yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function actionIndex()
    {
        $input = self::getListInput();
        $dataFilter = ItemsSearch::instance();
        $dataFilter->load($input);

        /** @var Items $class */
        $class = \Yii::$container->get($this->modelClass);

        $filter = $dataFilter->build();

        $query = (new Query())
            ->select(['i.*', 'd.title', 'd.short_description', 'd.description', 'a.id AS author_id', 'a.username AS author_username', 'a.blocked AS author_blocked', 'a.valid_until AS author_valid_until', 'a.id AS editor_id', 'a.username AS editor_username', 'a.blocked AS editor_blocked', 'a.valid_until AS editor_valid_until'])
            ->from($class::tableName().' AS i')
            ->leftJoin('item_description AS d', 'i.id = d.id')
            ->leftJoin('users AS a', 'a.id = i.created_by')
            ->leftJoin('users AS e', 'e.id = i.modified_by');

        if(!empty($filter)) {
            if($dataFilter->needJoin) {
                $query->leftJoin('item_to_category_relation AS ic', 'i.id = ic.item_id');
            }
            $query->where($filter);
        }


        $items = \Yii::createObject([
                                        'class' => SqlDataProvider::class,
                                        'sql' => $query->createCommand()->rawSql,
                                        'pagination' => ['params' => $input],
                                        'sort' => ['params' => $input],
                                    ]);
        return [
            'items' => ItemsView::prepareItemArray($items->getModels()),
            '_meta' => [
                'totalCount' => $items->getTotalCount(),
                'pageCount' => ceil($items->getTotalCount() / $input['per-page']),
                'currentPage' => (int) $input['page'],
                'perPage' => (int) $input['per-page']
            ]
        ];
    }

    /**
     * @param $id
     * @return array|NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function actionView($id): array|NotFoundHttpException
    {
        /** @var Items $class */
        $class = \Yii::$container->get($this->modelClass);
        $item = $class::find()
            ->joinWith(['description', 'author','editor'])
            ->andWhere(['items.id' => (int) $id])
            ->asArray()
            ->one();
        if ($item) {
            return ItemsView::prepareItem($item);
        }
        return new NotFoundHttpException('Объект каталога не найдена', 404);
    }
}