<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Textures');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="texture-index">
    <h1 class="h3 mb-3"><?= Html::encode($this->title) ?></h1>

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
            [
                'attribute' => 'image_path',
                'label' => Yii::t('app', 'Image'),
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->image_path && file_exists(Yii::getAlias('@webroot') . '/' . $model->image_path)) {
                        return Html::img('/' . Html::encode($model->image_path), [
                            'style' => 'width: 80px; height: 60px; object-fit: cover; border-radius: 4px;'
                        ]);
                    } else {
                        return '<span style="color: #ccc; font-style: italic;">' . Yii::t('app', 'No Image') . '</span>';
                    }
                },
                'headerOptions' => ['style' => 'width: 100px;'],
            ],
            'title',
            'created_at',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]) ?>
</div>
