<?php
namespace app\modules\catalog\controllers;

use app\controllers\ApiAbstractController;
use app\modules\admin\models\Items;
use app\modules\catalog\models\search\ItemsSearch;
use app\modules\catalog\views\ItemsView;
use OpenApi\Annotations as OA;
use yii\data\SqlDataProvider;
use yii\db\Query;
use yii\web\NotFoundHttpException;

/**
 *
 * @OA\Schema(
 *     schema="CatalogItemList",
 *     @OA\Property(property="id", type="integer", example="2", description="ID"),
 *     @OA\Property(property="price", type="string", example="1 005,00", description="Цена в рублях"),
 *     @OA\Property(property="title", type="string", example="Заголовок", description="Заголовок объекта"),
 *     @OA\Property(property="short_description", type="string", example="Краткое описание", description="Краткое описание объекта. В каталоге выводится в списке"),
 * ),
 * @OA\Schema(
 *     schema="CatalogItem",
 *     @OA\Property(property="id", type="integer", example="2", description="ID"),
 *     @OA\Property(property="price", type="string", example="1 005,00", description="Цена в рублях"),
 *     @OA\Property(property="title", type="string", example="Заголовок", description="Заголовок объекта"),
 *     @OA\Property(property="description", type="string", example="Описание", description="Полное описание объекта. В каталоге выводится в карточке объекта"),
 * ),
 *
 *
 * @OA\Get(
 *     @OA\PathItem(path="catalog/item"),
 *     path="/catalog/item",
 *     tags={"Catalog"},
 *     operationId="/catalog/item",
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
 *              @OA\Property(property="items", type="array", description="Список объектов", @OA\Items(ref="#/components/schemas/CatalogItemList")),
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
 *     @OA\PathItem(path="catalog/item/{id}"),
 *     path="/catalog/item/{id}",
 *     tags={"Catalog"},
 *     operationId="/catalog/item/{id}",
 *     summary="Объект",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id", in="path", example="7", description="id объекта"),
 *     @OA\Response(
 *          response = 200,
 *          description = "Объект",
 *          @OA\JsonContent(ref="#/components/schemas/CatalogItem"),
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
 * @package app\modules\catalog\controllers
 */
class ItemsController extends ApiAbstractController
{
    public $modelClass = Items::class;

    public function actionIndex($id)
    {
        $input = self::getListInput();
        $dataFilter = ItemsSearch::instance();

        $dataFilter->load($input);

        /** @var Items $class */
        $class = \Yii::$container->get($this->modelClass);

        $items = \Yii::createObject([
                                        'class' => SqlDataProvider::class,
                                        'sql' => (new Query())
                                            ->select(['i.id', 'i.price', 'd.title', 'd.short_description'])
                                            ->from($class::tableName().' AS i')
                                            ->leftJoin('item_description AS d', 'i.id = d.id')
                                            ->innerJoin('item_to_category_relation AS ic', 'i.id = ic.item_id')
                                            ->where($dataFilter->build())
                                            ->andWhere(['ic.category_id' => $id])
                                            ->createCommand()
                                            ->rawSql,
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

    public function actionView($id)
    {
        /** @var Items $class */
        $class = \Yii::$container->get($this->modelClass);
        $item = $class::find()
            ->joinWith('description')
            ->andWhere([
                'items.id' => (int) $id,
                'items.published' => Items::PUBLISHED
            ])
            ->one();

        if ($item) {
            return ItemsView::prepareItem($item);
        }
        return new NotFoundHttpException('Объект каталога не найден не найдена', 404);
    }
}
