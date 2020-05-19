<?php

namespace app\commands;

use app\models\User;
use Yii;

use yii\helpers\Console;

/**
 * Служебные действия приложения.
 * @package console\controllers
 */
class ServiceController extends \yii\console\Controller
{
    /**
     * Генерация админа по умолчанию.
     * @param $password
     * @return int
     * @throws \yii\base\Exception
     */
    public function actionDefaultAdmin($password)
    {
        if (!isset(Yii::$app->params['adminEmail'])) {
            Console::output('Отсутствует email администратора в настройках');
            return self::EXIT_CODE_ERROR;
        }

        if (User::findByEmail(['email' => Yii::$app->params['adminEmail']])) {
            Console::output('Пользователь уже создан');
            return self::EXIT_CODE_ERROR;
        }

        $user = new User([
            'email' => Yii::$app->params['adminEmail'],
            'password' => $password,
            'isAdmin' => 1,
            'name' => Yii::$app->params['adminEmail']
        ]);
        $user->generateAuthKey();

        if ($user->save(false)) {
            Console::output('Пользователь успешно создан');
            return self::EXIT_CODE_NORMAL;
        } else {
            Console::output('Не удалось создать пользователя');
            return self::EXIT_CODE_ERROR;
        }
    }

}
