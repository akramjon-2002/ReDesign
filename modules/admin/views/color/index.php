<?php

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = Yii::t('app', 'Colors');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="color-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'pager' => [
            'class' => 'yii\bootstrap5\LinkPager',
            'options' => ['class' => 'pagination justify-content-center'],
            'linkOptions' => ['class' => 'page-link'],
            'activePageCssClass' => 'active',
            'disabledPageCssClass' => 'disabled',
            'prevPageLabel' => '‹',
            'nextPageLabel' => '›',
            'firstPageLabel' => '«',
            'lastPageLabel' => '»',
        ],
        'columns' => [
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
