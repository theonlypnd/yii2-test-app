<?php

namespace app\controllers;

use Yii;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class SiteController extends Controller
{
    public function actionIndex()
    {
        $this->layout = false;
        $distPath = Yii::getAlias('@webroot/spa');
        $indexFile = $distPath . '/index.html';

        if (!file_exists($indexFile)) {
            throw new NotFoundHttpException('Vue SPA build not found.');
        }

        return Yii::$app->response->sendFile($indexFile);
    }

    public function actionAsset($path)
    {
        $dist = Yii::getAlias('@webroot/spa/assets');
        $file = $dist . '/' . $path;

        if (!is_file($file)) {
            throw new NotFoundHttpException('Asset not found.');
        }

        $mime = FileHelper::getMimeTypeByExtension($file) ?: 'application/octet-stream';
        Yii::$app->response->format = Response::FORMAT_RAW;
        return Yii::$app->response->sendFile($file, null, [
            'mimeType' => $mime,
            'inline' => true,
        ]);
    }
}
