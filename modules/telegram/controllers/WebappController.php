<?php

namespace app\modules\telegram\controllers;

use app\modules\telegram\actions\UploadAction;
use app\models\Texture;
use yii\web\Controller;

class WebappController extends Controller
{
    public function actions()
    {
        return [
            'upload' => [
                'class' => UploadAction::class,
            ],
        ];
    }

    public function actionIndex()
    {
        $textures = Texture::find()->orderBy(['created_at' => SORT_DESC])->all();

        return $this->render('index', [
            'textures' => $textures,
        ]);
    }
}
