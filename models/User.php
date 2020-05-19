<?php

namespace app\models;

use yii\web\IdentityInterface;
use Yii;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property int $isAdmin
 * @property string $photo
 *
 * @property Comment[] $comments
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    /**
     * Админ.
     */
    const ADMIN_VALUE = 1;
    /**
     * @var mixed|string|null
     */
    private $auth_key;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['isAdmin'], 'integer'],
            [['name', 'email', 'password', 'photo'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'isAdmin' => 'Is Admin',
            'photo' => 'Photo',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['user_id' => 'id']);
    }

    /**
     * Generates "remember me" authentication key
     * @throws \yii\base\Exception
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }


    /**
     * @param int|string $id
     * @return User|IdentityInterface|null
     */
    public static function findIdentity($id)
    {
        return User::findOne($id);
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $authKey
     * @return bool|void
     */
    public function validateAuthKey($authKey)
    {
        // TODO: Implement validateAuthKey() method.
    }

    /**
     * @param mixed $token
     * @param null $type
     * @return void|IdentityInterface
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        // TODO: Implement findIdentityByAccessToken() method.
    }

    /**
     * Поиск по email.
     *
     * @param $email
     * @return array|\yii\db\ActiveRecord|null
     */
    public static function findByEmail($email)
    {
        return User::find()->where(['email' => $email])->one();
    }

    /**
     * Валидация пароля.
     *
     * @param $password
     * @return bool
     */
    public function validatePassword($password)
    {
        return ($this->password == $password) ? true : false;

    }

    /**
     * Создание.
     *
     * @return bool
     */
    public function create()
    {
        return $this->save(false);
    }

    /**
     * Получение изображения.
     *
     * @return string
     */
    public function getImage()
    {
        return $this->photo;
    }

    /**
     * Создание дефолтного админа.
     *
     * @return bool
     */
    public function createDefaultAdmin()
    {
        $admin = new self();
        $admin->name = 'admin';
        $admin->email = 'admin@example.com';
        $admin->password = '123456';
        $admin->isAdmin = self::ADMIN_VALUE;

        return $admin->save();
    }

}
