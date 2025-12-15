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
}
