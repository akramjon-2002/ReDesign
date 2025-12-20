<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Textures');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="texture-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a(Yii::t('app', 'Add Texture'), ['create'], [
                'class' => 'btn btn-success',
                'title' => Yii::t('app', 'Add New Texture')
            ]) ?>
        </div>
    </div>

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
