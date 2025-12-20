<?php

use yii\helpers\Html;

$this->title = Yii::t('app', 'Create');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Textures'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="texture-create">
    <h1 class="h3 mb-3"><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
