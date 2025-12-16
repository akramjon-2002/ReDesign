<?php

namespace app\modules\telegram\controllers;

use app\modules\telegram\actions\UploadAction;
use app\models\Texture;
use app\models\Color;
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
        $colors = Color::find()->orderBy(['sort_order' => SORT_ASC])->all();

        return $this->render('index', [
            'textures' => $textures,
            'colors' => $colors,
        ]);
    }
}
