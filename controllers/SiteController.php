<?php

namespace app\controllers;

use Yii;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class SiteController extends Controller
{
    public function beforeAction($action)
    {
        // Disable CSRF validation for API-style endpoints used by the SPA
        if (in_array($action->id, ['login', 'logout'])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $this->layout = false;
            $distPath = Yii::getAlias('@webroot/spa');
            $indexFile = $distPath . '/index.html';

            if (!file_exists($indexFile)) {
                throw new NotFoundHttpException('Vue SPA build not found.');
            }

            // Ensure raw HTML response despite global JSON default
            Yii::$app->response->format = Response::FORMAT_RAW;
            return Yii::$app->response->sendFile($indexFile, null, [
                'mimeType' => 'text/html',
                'inline' => true,
            ]);
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

    public function actionAuthStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $isGuest = Yii::$app->user->isGuest;
        $identity = $isGuest ? null : Yii::$app->user->identity;
        return [
            'authenticated' => !$isGuest,
            'username' => $identity ? $identity->username : null,
        ];
    }

    public function actionLogin()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new \app\models\LoginForm();
        $model->load(Yii::$app->request->post(), '');
        if ($model->login()) {
            return [
                'ok' => true,
                'username' => Yii::$app->user->identity->username,
            ];
        }
        return [
            'ok' => false,
            'errors' => $model->getErrors(),
        ];
    }

    public function actionLogout()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->user->logout();
        return ['ok' => true];
    }
}
