<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\LoginForm;
use app\models\SignupForm;


class AuthController extends Controller
{
    /**
     * @return string|\yii\web\Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            if ($model->signup()) {
                return $this->redirect(['auth/login']);
            }
        }
        return $this->render('signup', ['model' => $model]);
    }

}