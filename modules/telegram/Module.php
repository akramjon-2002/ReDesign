<?php

namespace app\modules\telegram;

/**
 * telegram module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\telegram\controllers';

    public $layout = 'main';

    public $layoutPath = '@app/modules/telegram/views/layouts';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
    }
}
