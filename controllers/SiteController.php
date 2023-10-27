<?php

namespace app\controllers;

use OpenApi\Annotations as OA;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * @OA\Swagger(
 *     basePath="/",
 *     produces={"application/json"},
 *     consumes={"application/json"},
 *     @OA\Info(version="1.0", title="Reezonly Test Task API"),
 *     @OA\Server(url="http://reezonly.local"),
 *     @OA\Tag(name="Auth", description="Авторизация"),
 *     @OA\Tag(name="Admin", description="Администрирование"),
 *     @OA\Tag(name="Catalog", description="Каталог"),
 *     @OA\SecurityScheme(
 *       securityScheme="bearerAuth",
 *       type="http",
 *       scheme="bearer",
 *       bearerFormat="JWT",
 *     ),
 * ),
 * @OA\Schema(
 *     schema="meta",
 *     @OA\Property(property="totalCount", type="integer", example="1", description="всего записей"),
 *     @OA\Property(property="pageCount", type="integer", example="1", description="всего страниц"),
 *     @OA\Property(property="currentPage", type="integer", example="1", description="текущая страница"),
 *     @OA\Property(property="perPage", type="integer", example="1", description="записей на странице"),
 * ),
 * @OA\Schema(
 *     schema="ok",
 *     @OA\Property(property="status", type="string", example="ok"),
 * ),
 * @OA\Schema(
 *     schema="HttpException",
 *     @OA\Property(property="name", type="string", example="Forbidden", description="Тип ошибки"),
 *     @OA\Property(property="message", type="string", example="Forbidden", description="Текст ошибки"),
 *     @OA\Property(property="code", type="integer", example="0", description="код ошибки"),
 *     @OA\Property(property="status", type="integer", example="403", description="HTTP-код ошибки"),
 *     @OA\Property(property="type", type="string", example="yii\\web\\ForbiddenHttpException", description="Отладочная информация"),
 * ),
 *
 * @OA\Schema(
 *     schema="Author",
 *     @OA\Property(property="id", type="integer", example="2", description="ID"),
 *     @OA\Property(property="username", type="string", example="Someuser", description="Имя пользователя"),
 *     @OA\Property(property="blocked", type="boolean", example="true", description="Флаг блокировки"),
 *     @OA\Property(property="valid_until", type="string|null", example="2023-10-12Т12:56:22+03:00", description="Дата автоматической блокировки."),
 * ),
 */

class SiteController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => \light\swagger\SwaggerAction::class,
                'restUrl' => Url::to(['site/json-schema']),
                'configurations' => [
                    'docExpansion' => 'none',
                ]
            ],
            'json-schema' => [
                'class' => \light\swagger\SwaggerApiAction::class,
                'scanDir' => [
                    Yii::getAlias('@app/controllers'),
                    Yii::getAlias('@app/modules/admin/controllers'),
                    Yii::getAlias('@app/modules/admin/models'),
                    Yii::getAlias('@app/modules/auth/controllers'),
                    Yii::getAlias('@app/modules/auth/models'),
                    Yii::getAlias('@app/modules/catalog/controllers'),
                    Yii::getAlias('@app/modules/catalog/models'),
                    Yii::getAlias('@app/modules/user/controllers'),
                    Yii::getAlias('@app/modules/user/models'),
                ],
                'api_key' => 'balbalbal',
            ]
        ];
    }

    public function actionError()
    {

        if (($exception = Yii::$app->getErrorHandler()->exception) === null) {
            $exception = new NotFoundHttpException(Yii::t('yii', 'Page not found.'), 404);
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['code' => $exception->getCode(), 'message' => $exception->getMessage()];
    }
}
