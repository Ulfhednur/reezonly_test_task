<?php
namespace app\modules\admin\controllers;

use app\controllers\ApiAbstractController;

abstract class AdminAbstractController extends ApiAbstractController
{
    /**
     * Включаем аутентификацию по JWT
     * @return array
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => \kaabar\jwt\JwtHttpBearerAuth::class,
            'except' => [
                'options'
            ],
        ];

        return $behaviors;
    }

    /**
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    public function actionCreate(): mixed
    {
        $model = new $this->modelFormClass();
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');

        try {
            $saved = $model->save();
        } catch (\Exception $e) {
            throw $e;
        }

        if ($saved) {
            return $this->actionView($model->id);
        }

        return $model;
    }

    /**
     * @param $id
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($id): mixed
    {
        $input = \Yii::$app->getRequest()->getBodyParams();
        $input = array_merge(['id' => $id], $input);
        \Yii::$app->getRequest()->setBodyParams($input);

        return $this->actionCreate();
    }

    /**
     * @param $id
     * @return string[]
     * @throws \Throwable
     */
    public function actionDelete($id): array
    {
        $model = new $this->modelFormClass();
        $model->id = $id;
        try {
            $model->delete();
        } catch (\Exception $e) {
            throw $e;
        }

        return ['status' => 'ok'];
    }
}