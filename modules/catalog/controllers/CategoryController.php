<?php
namespace app\modules\catalog\controllers;

use app\controllers\ApiAbstractController;
use app\modules\admin\models\Category;
use app\modules\admin\models\Items;
use app\modules\catalog\models\search\CategorySearch;
use OpenApi\Annotations as OA;
use yii\data\SqlDataProvider;
use yii\db\Query;
use yii\web\NotFoundHttpException;

/**
 *
 * @OA\Schema(
 *     schema="CatalogCategoryList",
 *     @OA\Property(property="id", type="integer", example="2", description="ID"),
 *     @OA\Property(property="parent_id", type="integer|null", example="4", description="ID родителя"),
 *     @OA\Property(property="title", type="string", example="Заголовок", description="Заголовок категории"),
 *     @OA\Property(property="short_description", type="string", example="Краткое описание", description="Краткое описание категории. В каталоге выводится в списке (под)категорий"),
 * ),
 *
 * @OA\Schema(
 *     schema="CatalogCategory",
 *     @OA\Property(property="id", type="integer", example="2", description="ID"),
 *     @OA\Property(property="parent_id", type="integer|null", example="4", description="ID родителя"),
 *     @OA\Property(property="title", type="string", example="Заголовок", description="Заголовок категории"),
 *     @OA\Property(property="description", type="string", example="Описание", description="Полное описание категории. В каталоге выводится в карточке категории"),
 *     @OA\Property(property="child_categories", type="array", description="Список категорий", @OA\Items(ref="#/components/schemas/CatalogCategoryList")),
 * ),
 *
 * @OA\Get(
 *     @OA\PathItem(path="catalog/category"),
 *     path="/catalog/category",
 *     tags={"Catalog"},
 *     operationId="/catalog/category",
 *     summary="Список корневых категорий",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="page", in="query", example="1", description="Номер страницы"),
 *     @OA\Parameter(name="per-page", in="query", example="1", description="Записей на странице"),
 *     @OA\Parameter(name="title", in="query", example="Какой-то заголовок", description="Поиск по названию"),
 *     @OA\Response(
 *          response = 200,
 *          description = "Список категорий",
 *          @OA\JsonContent(
 *              @OA\Property(property="title", type="string", description="Заголовок каталога"),
 *              @OA\Property(property="description", type="string", description="Описание каталога"),
 *              @OA\Property(property="items", type="array", description="Список категорий", @OA\Items(ref="#/components/schemas/CatalogCategoryList")),
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
 *     @OA\PathItem(path="catalog/category/{id}"),
 *     path="/catalog/category/{id}",
 *     tags={"Catalog"},
 *     operationId="/catalog/category/{id}",
 *     summary="Категория",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id", in="path", example="7", description="id категории"),
 *     @OA\Response(
 *          response = 200,
 *          description = "Категория",
 *          @OA\JsonContent(ref="#/components/schemas/CatalogCategory"),
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
 * Class CategoryController
 * @package app\modules\catalog\controllers
 */
class CategoryController extends ApiAbstractController
{
    public $modelClass = Category::class;

    public function actionIndex()
    {
        $input = self::getListInput();
        $dataFilter = CategorySearch::instance();

        $dataFilter->load($input);

        /** @var Category $class */
        $class = \Yii::$container->get($this->modelClass);

        $items = \Yii::createObject([
                                        'class' => SqlDataProvider::class,
                                        'sql' => (new Query())
                                            ->select(['c.id', 'c.parent_id', 'd.title', 'd.short_description'])
                                            ->from($class::tableName().' AS c')
                                            ->leftJoin('category_description AS d', 'c.id = d.id')
                                            ->where($dataFilter->build())
                                            ->createCommand()
                                            ->rawSql,
                                        'pagination' => ['params' => $input],
                                        'sort' => ['params' => $input],
                                    ]);
        return [
            'title' => \Yii::$app->params['catalog_root_title'],
            'description' => \Yii::$app->params['catalog_root_description'],
            'items' => $items->getModels(),
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
        /** @var Category $class */
        $class = \Yii::$container->get($this->modelClass);
        $item = $class::find()
            ->joinWith('description')
            ->andWhere([
                'category.id' => (int) $id,
                'category.published' => Items::PUBLISHED
            ])
            ->one();

        $input = self::getListInput();
        $input['parent_id'] = $item->id;

        \Yii::$app->getRequest()->setQueryParams($input);
        $children = $this->actionIndex();
        if ($item) {
            return [
                'id' => $item->id,
                'parent_id' => $item->parent_id,
                'title' => $item->description->title,
                'description' => $item->description->description,
                'child_categories' => $children['items']
            ];
        }
        return new NotFoundHttpException('Категория не найдена', 404);
    }
}
