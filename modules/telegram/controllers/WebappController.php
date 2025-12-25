<?php

namespace app\modules\telegram\controllers;

use app\modules\telegram\actions\UploadAction;
use app\models\Texture;
use app\models\Color;
use app\models\Request;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class WebappController extends Controller
{
    public $enableCsrfValidation = true;

    public function actions()
    {
        return [
            'upload' => [
                'class' => UploadAction::class,
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (in_array($action->id, ['status', 'history'])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $textures = Texture::find()->orderBy(['created_at' => SORT_DESC])->all();
        $colors = Color::find()->orderBy(['sort_order' => SORT_ASC])->all();

        return $this->render('index', [
            'textures' => $textures,
            'colors' => $colors,
        ]);
    }

    public function actionStatus($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Request::findOne((int)$id);
        if ($request === null) {
            return ['ok' => false, 'message' => 'Request not found'];
        }

        $result = [
            'ok' => true,
            'id' => $request->id,
            'status' => $request->status,
        ];

        if ($request->status === Request::STATUS_COMPLETED && !empty($request->output_image_path)) {
            $result['output_url'] = Yii::$app->request->baseUrl . '/' . $request->output_image_path;
        }

        if ($request->status === Request::STATUS_FAILED) {
            $result['message'] = 'Processing failed';
        }

        return $result;
    }

    public function actionHistory($user_id, $page = 1, $per_page = 10)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $page = max(1, (int)$page);
        $per_page = min(50, max(1, (int)$per_page));
        $offset = ($page - 1) * $per_page;

        $requests = Request::find()
            ->where(['user_id' => $user_id])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($per_page)
            ->offset($offset)
            ->all();

        $items = [];
        foreach ($requests as $req) {
            $item = [
                'id' => $req->id,
                'status' => $req->status,
                'created_at' => $req->created_at,
                'texture_title' => $req->texture ? $req->texture->title : null,
                'texture_preview' => $req->texture && $req->texture->image_path ? Yii::$app->request->baseUrl . '/' . $req->texture->image_path : null,
                'color_hex' => $req->color_hex,
            ];
            if (!empty($req->input_image_path)) {
                $item['input_url'] = Yii::$app->request->baseUrl . '/' . $req->input_image_path;
            }
            if ($req->status === Request::STATUS_COMPLETED && !empty($req->output_image_path)) {
                $item['output_url'] = Yii::$app->request->baseUrl . '/' . $req->output_image_path;
            }
            $items[] = $item;
        }

        return [
            'ok' => true, 
            'items' => $items,
            'page' => $page,
            'per_page' => $per_page
        ];
    }
}
