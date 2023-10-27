<?php
namespace app\modules\auth\controllers;

use app\controllers\ApiAbstractController;
use app\modules\auth\models\LoginForm;
use app\modules\auth\models\Identity;
use app\modules\auth\models\UserRefreshToken;
use app\modules\user\models\User;
use OpenApi\Annotations as OA;
use yii\web\Cookie;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

/**
 *
 * @OA\Post(
 *     @OA\PathItem(path="/admin/auth/login",),
 *     path="/admin/auth/login",
 *     tags={"Auth"},
 *     operationId="/admin/auth/login",
 *     summary="Авторизация",
 *     @OA\RequestBody(
 *          required = true,
 *          request = "loginForm",
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(ref="#/components/schemas/loginForm"),
 *          ),
 *     ),
 *     @OA\Response(
 *          response = 200,
 *          description = "Вход успешен",
 *          @OA\JsonContent(
 *              @OA\Property(property="user", type="object", description="Объект пользователя", ref="#/components/schemas/User"),
 *              @OA\Property(property="token", type="string", example="very_long_token", description="Токен авторизации"),
 *         ),
 *     ),
 * ),
 *
 * @OA\Post(
 *     @OA\PathItem(path="/admin/auth/refresh-token",),
 *     path="/admin/auth/refresh-token",
 *     tags={"Auth"},
 *     operationId="/admin/auth/refresh-token",
 *     summary="Обновляет токен авторизаии",
 *     @OA\Response(
 *          response = 200,
 *          description = "Токен обновлен",
 *          @OA\JsonContent(
 *              @OA\Property(property="status", type="string", example="ok", description="ok"),
 *              @OA\Property(property="token", type="string", example="very_long_token", description="Токен авторизации"),
 *         ),
 *     ),
 * ),
 *
 * @OA\Delete(
 *     @OA\PathItem(path="/admin/auth/refresh-token",),
 *     path="/admin/auth/refresh-token",
 *     tags={"Auth"},
 *     summary="Удалят токен авторизаии. Выход из системы. Logout",
 *     @OA\Response(
 *          response = 200,
 *          description = "Токен удалён",
 *          @OA\JsonContent(
 *              @OA\Property(property="status", type="string", example="ok", description="ok"),
 *         ),
 *     ),
 * ),
 *
 * Class AuthController
 * @package app\modules\auth\controllers
 */

class AuthController extends ApiAbstractController
{
    public $modelClass = LoginForm::class;

    /**
     * @return array|\yii\web\Response
     * @throws ForbiddenHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();

        if ($model->load(\Yii::$app->request->post()) && $model->login()) {
            $user = \Yii::$app->user->identity;

            $token = $this->generateJwt($user);

            $userModel = User::findOne(['id' => $user->id]);
            $userModel->access_token = $token;
            $userModel->updateAttributes(['access_token']);

            $this->generateRefreshToken($user);

            return [
                'username' => $user->username,
                'token' => (string) $token
            ];
        }

        throw new ForbiddenHttpException();
    }

    public function actionRefreshToken()
    {
        $refreshToken = \Yii::$app->request->cookies->getValue('refresh-token', false);
        if (!$refreshToken) {
            return new UnauthorizedHttpException('refresh token не найден.');
        }

        $userRefreshToken = UserRefreshToken::findOne(['refresh_token' => $refreshToken]);

        if (\Yii::$app->request->getMethod() == 'POST') {
            if (!$userRefreshToken) {
                return new UnauthorizedHttpException('refresh token больше не существует');
            }

            $user = Identity::findIdentityByRefreshToken($userRefreshToken);
            if (!$user) {
                $userRefreshToken->delete();
                return new UnauthorizedHttpException('The user is inactive.');
            }

            $token = $this->generateJwt($user);

            $userModel = User::findOne(['id' => $user->id]);
            $userModel->access_token = $token;
            $userModel->updateAttributes(['access_token']);

            return [
                'status' => 'ok',
                'token' => (string) $token,
            ];

        } elseif (\Yii::$app->request->getMethod() == 'DELETE') {
            if ($userRefreshToken && !$userRefreshToken->delete()) {
                return new ServerErrorHttpException('Не удалось удалить refresh token.');
            }

            return ['status' => 'ok'];
        } else {
            return new UnauthorizedHttpException('Пользователь не активен.');
        }
    }

    private function generateJwt(Identity $user)
    {
        $jwt = \Yii::$app->jwt;
        $signer = $jwt->getSigner('HS256');
        $key = $jwt->getKey();

        $now   = new \DateTimeImmutable();

        $jwtParams = \Yii::$app->params['jwt'];

        $token = $jwt->getBuilder()
            ->issuedBy($jwtParams['issuer'])
            ->permittedFor($jwtParams['audience'])
            ->identifiedBy($jwtParams['id'], true)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now->modify($jwtParams['request_time']))
            ->expiresAt($now->modify($jwtParams['expire']))
            ->withClaim('uid', $user->id)
            ->getToken($signer, $key);

        return $token->toString();
    }

    /**
     * @param Identity $user
     * @param Identity|null $impersonator
     * @return UserRefreshToken
     * @throws \yii\base\Exception
     * @throws ServerErrorHttpException
     * @throws \Exception
     */
    private function generateRefreshToken(Identity $user): UserRefreshToken
    {
        $userRefreshToken = UserRefreshToken::findOne(
            [
                'user_id' => $user->id,
                'user_ip' => \Yii::$app->request->userIP,
                'user_agent' => \Yii::$app->request->userAgent
            ]
        );
        if (empty($userRefreshToken)) {
            $userRefreshToken = new UserRefreshToken(
                [
                    'user_id' => $user->id,
                    'refresh_token' => \Yii::$app->security->generateRandomString(200),
                    'user_ip' => \Yii::$app->request->userIP,
                    'user_agent' => \Yii::$app->request->userAgent
                ]
            );
            if (!$userRefreshToken->save()) {
                throw new ServerErrorHttpException(
                    'Ошибка записи refresh token: ' . implode(PHP_EOL, $userRefreshToken->getErrorSummary(true))
                );
            }
        }
        \Yii::$app->response->cookies->add(new Cookie([
            'name' => 'refresh-token',
            'value' => $userRefreshToken->refresh_token,
            'httpOnly' => true,
            'sameSite' => 'none',
            'secure' => true,
            'path' => '/admin/auth/refresh-token',
        ]));

        return $userRefreshToken;
    }
}