<?php

namespace app\modules\user\controllers;

use OpenApi\Annotations as OA;
use app\modules\admin\controllers\AdminAbstractController;
use app\modules\user\models\User;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * @OA\Get(
 *     @OA\PathItem(path="/admin/user"),
 *     path="/admin/user",
 *     tags={"Admin"},
 *     operationId="/admin/user/index/",
 *     summary="Список пользователей",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="page", in="query", example="1", description="Номер страницы"),
 *     @OA\Parameter(name="per-page", in="query", example="1", description="Записей на странице"),
 *     @OA\Response(
 *          response = 200,
 *          description = "Список пользователей",
 *          @OA\JsonContent(
 *              @OA\Property(property="items", type="array", description="Список пользователей", @OA\Items(ref="#/components/schemas/UserResponce")),
 *              @OA\Property(property="_meta", type="object", description="Метаданные", ref="#/components/schemas/meta"),
 *         ),
 *     ),
 * ),
 *
 * @OA\Post(
 *     @OA\PathItem(path="/admin/user"),
 *     path="/admin/user",
 *     tags={"Admin"},
 *     operationId="/admin/user/create",
 *     summary="Создание пользователя",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *          required = true,
 *          request = "createUser",
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(ref="#/components/schemas/User"),
 *          ),
 *     ),
 *     @OA\Response(
 *          response = 200,
 *          description = "Пользователь создан",
 *          @OA\JsonContent(ref="#/components/schemas/UserResponce"),
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
 *     @OA\PathItem(path="/admin/user/{id}",),
 *     path="/admin/user/{id}",
 *     tags={"Admin"},
 *     operationId="/admin/user/update/{id}",
 *     summary="Изменение пользователя",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id", in="path", example="7", description="id пользователя"),
 *     @OA\RequestBody(
 *          required = true,
 *          request = "createUser",
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(ref="#/components/schemas/User"),
 *          ),
 *     ),
 *     @OA\Response(
 *          response = 200,
 *          description = "Пользователь изменен",
 *          @OA\JsonContent(ref="#/components/schemas/UserResponce"),
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
 *     @OA\Response(
 *          response = 404,
 *          description = "Пользователь не найден",
 *          @OA\JsonContent(ref="#/components/schemas/HttpException"),
 *     ),
 * ),
 *
 * @OA\GET(
 *     @OA\PathItem(path="/admin/user/{id}",),
 *     path="/admin/user/{id}",
 *     tags={"Admin"},
 *     operationId="/admin/user/view/{id}",
 *     summary="Просмотр пользователя",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id", in="path", example="7", description="id пользователя"),
 *     @OA\Response(
 *          response = 200,
 *          description = "Пользователь",
 *          @OA\JsonContent(ref="#/components/schemas/UserResponce"),
 *     ),
 *     @OA\Response(
 *          response = 403,
 *          description = "Доступ запрещён",
 *          @OA\JsonContent(ref="#/components/schemas/HttpException"),
 *     ),
 *     @OA\Response(
 *          response = 404,
 *          description = "Пользователь не найден",
 *          @OA\JsonContent(ref="#/components/schemas/HttpException"),
 *     ),
 * ),
 *
 * @OA\Delete(
 *     @OA\PathItem(path="/admin/user/{id}"),
 *     path="/admin/user/{id}",
 *     tags={"Admin"},
 *     operationId="/admin/user/delete/{id}",
 *     summary="Удаление пользователя",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id", in="path", example="7", description="id пользователя"),
 *     @OA\Response(
 *          response = 200,
 *          description = "Пользователь удален",
 *          @OA\JsonContent(ref="#/components/schemas/ok"),
 *     ),
 *     @OA\Response(
 *          response = 403,
 *          description = "Доступ запрещён",
 *          @OA\JsonContent(ref="#/components/schemas/HttpException"),
 *     ),
 *     @OA\Response(
 *          response = 404,
 *          description = "Пользователь не найден",
 *          @OA\JsonContent(ref="#/components/schemas/HttpException"),
 *     ),
 * ),
 *
 * Class UserController
 * @package app\controllers
 */
class UserController extends AdminAbstractController
{
    public $modelClass = User::class;
    public $modelFormClass = User::class;

    /**
     * @throws \yii\di\NotInstantiableException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        $input = self::getListInput();
        $input['fields'] = 'id,username,blocked,valid_until';
        \Yii::$app->getRequest()->setQueryParams($input);

        /** @var User $class */
        $class = \Yii::$container->get($this->modelClass);

        /** @var ActiveDataProvider $items */
        $items = \Yii::createObject([
                                      'class' => ActiveDataProvider::class,
                                      'query' => $class::find(),
                                      'pagination' => ['params' => $input],
                                      'sort' => [
                                          'params' => $input,
                                          'defaultOrder' => [
                                              'id' => SORT_DESC
                                          ]
                                      ],
                                  ]);

        return [
            'items' => $items,
            '_meta' => [
                'totalCount' => $items->getTotalCount(),
                'pageCount' => ceil($items->getTotalCount() / $input['per-page']),
                'currentPage' => (int) $input['page'],
                'perPage' => (int) $input['per-page']
            ],
        ];
    }

    public function actionView($id)
    {
        /** @var User $model */
        $model = \Yii::$container->get($this->modelClass)::findOne($id);
        if (!$model->id) {
            throw new NotFoundHttpException('Пользователь не найден: ' . $id);
        }
        $user = $model->toArray();
        unset($user['password_hash'],$user['auth_key'],$user['access_token']);
        return $user;
    }

}