<?php

namespace app\modules\admin;

use Yii;

/**
 * admin module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\admin\controllers';

    public $layout = 'main';

    public $layoutPath = '@app/modules/admin/views/layouts';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (Yii::$app->user->isGuest) {
            Yii::$app->user->loginRequired();
            return false;
        }

        if (!Yii::$app->user->identity->isAdmin()) {
            throw new \yii\web\ForbiddenHttpException('You are not allowed to access this page.');
        }

        return true;
    }
}
