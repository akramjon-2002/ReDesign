<?php

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Colors';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="color-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Color', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'title',
            [
                'attribute' => 'hex',
                'format' => 'raw',
                'value' => function ($model) {
                    return '<span style="display:inline-block;width:24px;height:24px;background:' . Html::encode($model->hex) . ';border:1px solid #ccc;border-radius:4px;vertical-align:middle;margin-right:8px;"></span>' . Html::encode($model->hex);
                },
            ],
            'sort_order',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
