<?php

namespace app\models;

use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%article}}".
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $content
 * @property string $date
 * @property string $image
 * @property int $viewed
 * @property int $user_id
 * @property int $status
 * @property int $category_id
 *
 * @property ArticleTag[] $articleTags
 * @property Comment[] $comments
 */
class Article extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%article}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'description', 'content'], 'string'],
            [['title'], 'required'],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['title', 'image'], 'string', 'max' => 255],
            [['date'], 'default', 'value' => date('Y-m-d')],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'content' => 'Content',
            'date' => 'Date',
            'image' => 'Image',
            'viewed' => 'Viewed',
            'user_id' => 'User ID',
            'status' => 'Status',
            'category_id' => 'Category ID',
        ];
    }

    /**
     * Сохранение изображения.
     *
     * @param $filename
     * @return bool
     */
    public function saveImage($filename)
    {
        $this->image = $filename;
        return $this->save(false);
    }

    /**
     * Получение изображения.
     *
     * @return string
     */
    public function getImage()
    {
        return ($this->image) ? '/uploads/' . $this->image : '/no-image.png';
    }

    /**
     * Удаление изображения.
     */
    public function deleteImage()
    {
        $imageUploadModel = new ImageUpload();
        $imageUploadModel->deleteCurrentImage($this->image);

    }

    /**
     * @return bool
     */
    public function beforeDelete()
    {
        $this->deleteImage();
        return parent::beforeDelete();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    /**
     * Сохранение категории к посту.
     * @param $category_id
     * @return bool
     */
    public function saveCategory($category_id)
    {
        $category = Category::findOne($category_id);
        if ($category != null) {
            $this->link('category', $category);
            return true;
        }
    }

    /**
     * Получение тэгов.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])
            ->viaTable('article_tag', ['article_id' => 'id']);
    }

    /**
     * Получение выбранных тегов.
     *
     * @return array
     */
    public function getSelectedTags()
    {
        $selectedTags = $this->getTags()->select('id')->asArray()->all();
        return ArrayHelper::getColumn($selectedTags, 'id');
    }

    /**
     * Сохранение тэгов.
     *
     * @param $tags
     */
    public function saveTags($tags)
    {
        if (is_array($tags)) {
            $this->clearCurrentTags();
            foreach ($tags as $tag_id) {
                $tag = Tag::findOne($tag_id);
                $this->link('tags', $tag);
            }
        }
    }

    /**
     * Удаление тэгов.
     */
    public function clearCurrentTags()
    {
        ArticleTag::deleteAll(['article_id' => $this->id]);
    }

    /**
     * Получение даты.
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getDate()
    {
        return Yii::$app->formatter->asDate($this->date);
    }

    /**
     * @param int $pageSize
     * @return mixed
     */
    public static function getAll($pageSize = 3)
    {
        // build a DB query to get all articles with status = 1
        $query = Article::find();

        // get the total number of articles (but do not fetch the article data yet)
        $count = $query->count();

        // create a pagination object with the total count
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $pageSize]);

        // limit the query using the pagination and retrieve the articles
        $articles = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $data['articles'] = $articles;
        $data['pagination'] = $pagination;

        return $data;
    }

    /**
     * Получение популярных постов.
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getPopular()
    {
        return Article::find()->orderBy('viewed desc')->limit(3)->all();
    }

    /**
     * Получение последних постов.
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getRecent()
    {
        return Article::find()->orderBy('date desc')->limit(4)->all();
    }

    /**
     * Сохранение поста.
     *
     * @return bool
     */
    public function saveArticle()
    {
        $this->user_id = Yii::$app->user->id;
        return $this->save();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['article_id' => 'id']);
    }

    /**
     * Получение комментариев поста.
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getArticleComments()
    {
        return $this->getComments()->where(['status' => 1])->all();
    }

    /**
     * Получение количества просмотров.
     *
     * @return bool
     */
    public function getCountViewPost()
    {
        $session = Yii::$app->session;

        if (!isset($session['viewed'])) {
            $session->set('viewed', [$this->id]);
            $this->updateCounters(['count_viewed' => 1]);
        } else {
            if (!ArrayHelper::isIn($this->id, $session['viewed'])) {
                $array = $session['viewed'];
                array_push($array, $this->id);
                $session->remove('viewed');
                $session->set('viewed', $array);
                $this->updateCounters(['count_viewed' => 1]);
            }
        }
        return true;
    }
}
  