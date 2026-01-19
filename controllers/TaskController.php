<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;
use app\models\Task;
use app\models\Admin;

class TaskController extends ActiveController
{
    public $modelClass = Task::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Enforce JSON response already set globally.
        // Use session-based auth (Yii::$app->user) via AccessControl for admin-only actions

        // Access rules: guests can index, view, create; admin can update/delete
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['index', 'view', 'create', 'update', 'delete', 'preview'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'preview'],
                    'roles' => ['?', '@'],
                ],
                [
                    'allow' => true,
                    'actions' => ['update', 'delete'],
                    'roles' => ['@'],
                ],
            ],
        ];

        // Verbs
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index' => ['GET', 'OPTIONS'],
                'view' => ['GET', 'OPTIONS'],
                'create' => ['POST', 'OPTIONS'],
                'update' => ['PUT', 'PATCH', 'OPTIONS'],
                'delete' => ['DELETE', 'OPTIONS'],
                'preview' => ['POST', 'OPTIONS'],
            ],
        ];

        return $behaviors;
    }

    // Custom index for pagination and sorting
    public function actions()
    {
        $actions = parent::actions();

        // Override data provider
        $actions['index']['prepareDataProvider'] = function ($action) {
            $request = Yii::$app->request;
            $query = Task::find();

            $sort = [
                'attributes' => ['username', 'email', 'is_done'],
                'defaultOrder' => ['id' => SORT_DESC],
            ];

            $provider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 3,
                ],
                'sort' => $sort,
            ]);

            // Apply sorting from query params (e.g., ?sort=username or ?sort=-email)
            $provider->sort->params = $request->get();
            return $provider;
        };

        // We'll handle create/update to support file upload and enforce admin-only edits of certain fields
        unset($actions['create'], $actions['update']);

        return $actions;
    }

    public function actionCreate()
    {
        $model = new Task();
        // Load from form-data or JSON
        $data = Yii::$app->request->post();
        if (empty($data)) {
            $data = Yii::$app->request->getBodyParams();
        }
        $model->load($data, '');

        // Also capture file via UploadedFile
        $model->imageFile = UploadedFile::getInstanceByName('image');

        if (!$model->validate()) {
            return $model;
        }

        if (!$model->save(false)) {
            throw new BadRequestHttpException('Failed to save task');
        }

        return $model;
    }

    public function actionUpdate($id)
    {
        $model = Task::findOne($id);
        if (!$model) {
            throw new BadRequestHttpException('Task not found');
        }

        $data = Yii::$app->request->getBodyParams();
        // Admin is allowed to edit text and is_done only.
        $allowed = ['text', 'is_done'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        $model->load($updateData, '');

        // Image edit is not allowed via update by spec; but in case image is sent, ignore.
        $model->imageFile = null;

        if (!$model->validate(['text', 'is_done'])) {
            return $model;
        }

        if (!$model->save(false)) {
            throw new BadRequestHttpException('Failed to update task');
        }

        return $model;
    }

    // Preview text without saving (AJAX)
    public function actionPreview()
    {
        $text = Yii::$app->request->post('text');
        if ($text === null) {
            throw new BadRequestHttpException('No text provided');
        }
        // Basic sanitization and formatting (escape + nl2br)
        $safe = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safe = nl2br($safe);

        return [
            'preview' => $safe,
        ];
    }
}
