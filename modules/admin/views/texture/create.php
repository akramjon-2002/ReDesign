<?php

use yii\helpers\Html;

$this->title = Yii::t('app', 'Create');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Textures'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="texture-create">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?> <?= Yii::t('app', 'Texture') ?></h1>
        <div>
            <?= Html::a(Yii::t('app', 'Back to List'), ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
