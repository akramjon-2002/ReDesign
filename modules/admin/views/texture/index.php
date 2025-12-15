<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Textures';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="texture-index">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <?= Html::a('Create Texture', ['create'], ['class' => 'btn btn-primary']) ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'title',
            'type',
            'prompt_suffix',
            'created_at',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]) ?>
</div>
