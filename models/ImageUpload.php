<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;


class ImageUpload extends Model
{

    public $image;

    public function rules()
    {
        return [
            [['image'], 'required'],
            [['image'], 'file', 'extensions' => 'jpg,png']
        ];
    }

    /**
     * Загрузка изображения.
     *
     * @param UploadedFile $file
     * @param $currentImage
     * @return string
     */
    public function uploadFile(UploadedFile $file, $currentImage)
    {
        $this->image = $file;
        if ($this->validate()) {
            $this->deleteCurrentImage($currentImage);
            return $this->saveImage();
        }
    }

    /**
     * Получение папки.
     *
     * @return string
     */
    private function getFolder()
    {
        return Yii::getAlias('@web') . 'uploads/';
    }

    /**
     * Удаление изображения.
     *
     * @param $currentImage
     */
    public function deleteCurrentImage($currentImage)
    {
        if ($this->fileExists($currentImage)) {
            unlink($this->getFolder() . $currentImage);
        }
    }

    /**
     * Генерация названия изображения.
     *
     * @return string
     */
    private function generateFilename()
    {
        return strtolower(md5(uniqid($this->image->baseName)) . '.' . $this->image->extension);
    }

    /**
     * Проверка на существование.
     *
     * @param $currentImage
     * @return bool
     */
    public function fileExists($currentImage)
    {
        if (!empty($currentImage) && $currentImage != null) {
            return file_exists($this->getFolder() . $currentImage);
        }
    }

    /**
     * Сохранение изображения.
     *
     * @return string
     */
    public function saveImage()
    {
        $filename = $this->generateFilename();
        $this->image->saveAs($this->getFolder() . $filename);
        return $filename;
    }
}

