<?php

namespace app\modules\telegram\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;

class WebhookController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $update = Yii::$app->request->getRawBody();
        if ($update !== '') {
            Yii::info($update, __METHOD__);
        }

        Yii::$app->response->format = Response::FORMAT_RAW;
        return 'OK';
    }
}
