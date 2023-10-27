<?php
namespace app\modules\admin\controllers;

use app\modules\admin\models\Category;
use app\modules\admin\models\CategoryForm;
use app\modules\admin\models\search\CategorySearch;
use app\modules\admin\views\CategoryView;
use OpenApi\Annotations as OA;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use yii\db\Query;
use yii\web\NotFoundHttpException;

/**
 * @OA\Schema(
 *     schema="Category",
 *     @OA\Property(property="id", type="integer", example="2", description="ID"),
 *     @OA\Property(property="parent_id", type="integer|null", example="4", description="ID родителя"),
 *     @OA\Property(property="published", type="boolean", example="true", description="Флаг публикации"),
 *     @OA\Property(property="title", type="string", example="Заголовок", description="Заголовок категории"),
 *     @OA\Property(property="short_description", type="string", example="Краткое описание", description="Краткое описание категории. В каталоге выводится в списке (под)категорий"),
 *     @OA\Property(property="description", type="string", example="Описание", description="Полное описание категории. В каталоге выводится в карточке категории"),
 *     @OA\Property(property="created_date", type="string", example="2023-10-12Т12:56:22+03:00", description="Дата создания категории."),
 *     @OA\Property(property="created_by", type="object",  description="Создатель категории.", ref="#/components/schemas/Author"),
 *     @OA\Property(property="modified_date", type="string", example="2023-10-12Т12:56:22+03:00", description="Дата изменения категории."),
 *     @OA\Property(property="modified_by", type="object",  description="Редактор категории.", ref="#/components/schemas/Author"),
 * ),
 *
 * @OA\Get(
 *     @OA\PathItem(path="admin/category"),
 *     path="/admin/category",
 *     tags={"Admin"},
 *     operationId="/admin/category",
 *     summary="Список категорий",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="page", in="query", example="1", description="Номер страницы"),
 *     @OA\Parameter(name="per-page", in="query", example="1", description="Записей на странице"),
 *     @OA\Parameter(name="parent_id", in="query", example="1", description="Родительская категория"),
 *     @OA\Parameter(name="title", in="query", example="Заголовок категории", description="Поиск по названию"),
 *     @OA\Parameter(name="sort", in="query", description="сортировка", example="-title"),
 *     @OA\Response(
 *          response = 200,
 *          description = "Список категорий",
 *          @OA\JsonContent(
 *              @OA\Property(property="items", type="array", description="Список категорий", @OA\Items(ref="#/components/schemas/Category")),
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
 *     @OA\PathItem(path="admin/category/{id}"),
 *     path="/admin/category/{id}",
 *     tags={"Admin"},
 *     operationId="/admin/category/{id}",
 *     summary="Категория",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id", in="path", example="7", description="id категории"),
 *     @OA\Response(
 *          response = 200,
 *          description = "Категория",
 *          @OA\JsonContent(ref="#/components/schemas/Category"),
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
 *     @OA\PathItem(path="admin/category"),
 *     path="/admin/category",
 *     tags={"Admin"},
 *     operationId="/admin/category/create",
 *     summary="Создание категории",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *          required = true,
 *          request = "createСategory",
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(ref="#/components/schemas/categoryForm"),
 *          ),
 *     ),
 *     @OA\Response(
 *          response = 200,
 *          description = "Категория",
 *          @OA\JsonContent(ref="#/components/schemas/Category"),
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
 *     @OA\PathItem(path="admin/category/{id}"),
 *     path="/admin/category/{id}",
 *     tags={"Admin"},
 *     operationId="/admin/category/update",
 *     summary="Редактирование категории",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *          required = true,
 *          request = "updateСategory",
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(ref="#/components/schemas/categoryForm"),
 *          ),
 *     ),
 *     @OA\Response(
 *          response = 200,
 *          description = "Категория",
 *          @OA\JsonContent(ref="#/components/schemas/Category"),
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
 *     @OA\PathItem(path="admin/category/{id}"),
 *     path="/admin/category/{id}",
 *     tags={"Admin"},
 *     operationId="/admin/category/delete",
 *     summary="Удаление категории",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id", in="path", example="7", description="id категории"),
 *     @OA\Response(
 *          response = 200,
 *          description = "Категория удалена",
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
 * Class CategoryController
 * @package app\modules\admin\controllers
 */
class CategoryController extends AdminAbstractController
{
    public $modelClass = Category::class;
    public $modelFormClass = CategoryForm::class;

    /**
     * @return array[]|object|object[]|ActiveDataProvider[]|\yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
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
                                            ->select(['c.*', 'd.title', 'd.short_description', 'd.description', 'a.id AS author_id', 'a.username AS author_username', 'a.blocked AS author_blocked', 'a.valid_until AS author_valid_until', 'a.id AS editor_id', 'a.username AS editor_username', 'a.blocked AS editor_blocked', 'a.valid_until AS editor_valid_until'])
                                            ->from($class::tableName().' AS c')
                                            ->leftJoin('category_description AS d', 'c.id = d.id')
                                            ->leftJoin('users AS a', 'a.id = c.created_by')
                                            ->leftJoin('users AS e', 'e.id = c.modified_by')
                                            ->where($dataFilter->build())
                                            ->createCommand()
                                            ->rawSql,
                                        'pagination' => ['params' => $input],
                                        'sort' => ['params' => $input],
                                    ]);
        return [
            'items' => CategoryView::prepareItemArray($items->getModels()),
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
        /** @var Category $class */
        $class = \Yii::$container->get($this->modelClass);
        $item = $class::find()
            ->joinWith(['description', 'author','editor'])
            ->andWhere(['category.id' => (int) $id])
            ->asArray()
            ->one();
        if ($item) {
            return CategoryView::prepareItem($item);
        }
        return new NotFoundHttpException('Категория не найдена', 404);
    }
}