<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Textures', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="texture-view">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <div class="d-flex gap-2">
            <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-outline-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'title',
            'type',
            'prompt_suffix',
            [
                'attribute' => 'image_path',
                'format' => 'raw',
                'value' => $model->image_path ? Html::img('/' . $model->image_path, ['style' => 'max-width: 320px; border-radius: 8px;']) : null,
            ],
            'created_at',
        ],
    ]) ?>
</div>
