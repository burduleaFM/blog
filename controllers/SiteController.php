<?php

namespace app\controllers;

use app\models\Article;
use app\models\Category;
use app\models\CommentForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;


class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $data = Article::getAll();
        $popular = Article::getPopular();
        $recent = Article::getRecent();
        $categories = Category::getAll();

        return $this->render('index', [
            'articles' => $data['articles'],
            'pagination' => $data['pagination'],
            'popular' => $popular,
            'recent' => $recent,
            'categories' => $categories,
        ]);
    }

    /**
     * Просмотр статьи.
     *
     * @param $id
     * @return string
     */
    public function actionView($id)
    {
        $article = Article::findOne($id);
        $popular = Article::getPopular();
        $recent = Article::getRecent();
        $categories = Category::getAll();
        $tags = $article->tags;
        $comments = $article->getArticleComments();
        $commentForm = new CommentForm();

        return $this->render('view', [
            'article' => $article,
            'popular' => $popular,
            'recent' => $recent,
            'categories' => $categories,
            'tags' => $tags,
            'comments' => $comments,
            'commentForm' => $commentForm,
        ]);
    }

    /**
     * Категория.
     *
     * @param $id
     * @return string
     */
    public function actionCategory($id)
    {
        $data = Category::getArticlesByCategory($id);
        $popular = Article::getPopular();
        $recent = Article::getRecent();
        $categories = Category::getAll();

        return $this->render('category', [
            'articles' => $data['articles'],
            'pagination' => $data['pagination'],
            'popular' => $popular,
            'recent' => $recent,
            'categories' => $categories

        ]);
    }

    /**
     * Отправка комментария.
     *
     * @param $id
     * @return \yii\web\Response
     */
    public function actionComment($id)
    {
        $model = new CommentForm();
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            if ($model->saveComment($id)) {
                Yii::$app->getSession()->setFlash('comment', 'Ваш комментарий будет добавлен после прохождения модерации. Спасибо!');

                return $this->redirect(['site/view', 'id' => $id]);
            }
        }
    }
}