<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\web\Controller;
use app\models\Comment;


class CommentController extends Controller
{
    /**
     * Главная с комментариями.
     *
     * @return string
     */
    public function actionIndex()
    {
        $comments = Comment::find()->orderBy('id desc')->all();
        return $this->render('index', ['comments' => $comments]);
    }

    /**
     * Удаление комментария.
     *
     * @param $id
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $comment = Comment::findOne($id);

        if ($comment->delete()) {
            return $this->redirect(['comment/index']);
        }
    }

    /**
     * Подтверждение комментария.
     *
     * @param $id
     * @return \yii\web\Response
     */
    public function actionAllow($id)
    {
        $comment = Comment::findOne($id);
        if ($comment->allow()) {
            return $this->redirect(['index']);
        }
    }

    /**
     * Отклонение комментария.
     *
     * @param $id
     * @return \yii\web\Response
     */
    public function actionDisallow($id)
    {
        $comment = Comment::findOne($id);
        if ($comment->disallow()) {
            return $this->redirect(['index']);
        }
    }

}