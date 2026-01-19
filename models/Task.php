<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class Task extends ActiveRecord
{
    public const MAX_IMAGE_WIDTH = 320;
    public const MAX_IMAGE_HEIGHT = 240;
    /**
     * @var UploadedFile|null
     */
    public $imageFile;

    public static function tableName()
    {
        return '{{%task}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules()
    {
        return [
            [['username', 'email', 'text'], 'required'],
            [['username', 'email'], 'string', 'max' => 255],
            ['email', 'email'],
            ['text', 'string', 'max' => 1000],
            ['is_done', 'boolean'],
            [['created_at', 'updated_at'], 'integer'],

            // Image file upload handling
            ['imageFile', 'file', 'extensions' => ['jpg', 'jpeg', 'png', 'gif'], 'skipOnEmpty' => true, 'checkExtensionByMimeType' => true],
        ];
    }

    public function fields()
    {
        // Expose fields for API responses
        return [
            'id', 'username', 'email', 'text', 'image', 'is_done', 'created_at', 'updated_at'
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'email' => 'Email',
            'text' => 'Text',
            'image' => 'Image Path',
            'is_done' => 'Is Done',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            // Assign UploadedFile instance if present
            $this->imageFile = UploadedFile::getInstanceByName('image');
            return true;
        }
        return false;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Handle image upload & resizing when a new file is provided
        if ($this->imageFile instanceof UploadedFile) {
            $saved = $this->processAndSaveImage($this->imageFile);
            if (!$saved) {
                $this->addError('imageFile', 'Failed to process image.');
                return false;
            }
        }

        return true;
    }

    /**
     * Process upload: validate dimensions and proportionally downscale to max 320x240.
     * Save to web/uploads and set `image` relative URL.
     */
    protected function processAndSaveImage(UploadedFile $file): bool
    {
        $uploadDir = Yii::getAlias('@webroot/uploads');
        if (!is_dir($uploadDir)) {
            FileHelper::createDirectory($uploadDir, 0775, true);
        }

        $ext = strtolower($file->getExtension());
        $basename = date('YmdHis') . '_' . Yii::$app->security->generateRandomString(8);
        $targetPath = $uploadDir . '/' . $basename . '.' . $ext;

        // Move to temp path first
        $tmpPath = $uploadDir . '/tmp_' . $basename . '.' . $ext;
        if (!$file->saveAs($tmpPath, false)) {
            return false;
        }

        // Read dimensions
        [$width, $height] = getimagesize($tmpPath) ?: [0, 0];
        if ($width === 0 || $height === 0) {
            @unlink($tmpPath);
            return false;
        }

        $maxW = self::MAX_IMAGE_WIDTH;
        $maxH = self::MAX_IMAGE_HEIGHT;
        $newW = $width;
        $newH = $height;

        // Calculate scale if exceeds limits
        if ($width > $maxW || $height > $maxH) {
            $scale = min($maxW / $width, $maxH / $height);
            $newW = (int) floor($width * $scale);
            $newH = (int) floor($height * $scale);
        }

        // Create image resources using GD
        $src = $this->createImageResource($tmpPath, $ext);
        if (!$src) {
            @unlink($tmpPath);
            return false;
        }

        $dst = imagecreatetruecolor($newW, $newH);
        // Preserve transparency for PNG/GIF
        if (in_array($ext, ['png', 'gif'], true)) {
            imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }

        if (!imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $width, $height)) {
            imagedestroy($src);
            imagedestroy($dst);
            @unlink($tmpPath);
            return false;
        }

        $saved = $this->saveImageResource($dst, $targetPath, $ext);
        imagedestroy($src);
        imagedestroy($dst);
        @unlink($tmpPath);

        if (!$saved) {
            return false;
        }

        // Save relative web path
        $this->image = '/uploads/' . basename($targetPath);
        return true;
    }

    protected function createImageResource(string $path, string $ext)
    {
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                return imagecreatefromjpeg($path);
            case 'png':
                return imagecreatefrompng($path);
            case 'gif':
                return imagecreatefromgif($path);
            default:
                return null;
        }
    }

    protected function saveImageResource($resource, string $path, string $ext): bool
    {
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                return imagejpeg($resource, $path, 85);
            case 'png':
                return imagepng($resource, $path, 6);
            case 'gif':
                return imagegif($resource, $path);
            default:
                return false;
        }
    }
}
