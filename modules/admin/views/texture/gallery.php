<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Texture;

$this->title = 'Texture Gallery';
$this->params['breadcrumbs'][] = $this->title;

$textures = Texture::find()->orderBy(['created_at' => SORT_DESC])->all();
?>

<div class="texture-gallery">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('Add Texture', ['create'], [
                'class' => 'btn btn-success me-2',
                'title' => 'Add New Texture'
            ]) ?>
            <?= Html::a('Manage Colors', ['/admin/color/index'], [
                'class' => 'btn btn-info me-2',
                'title' => 'Manage Colors'
            ]) ?>
            <?= Html::a('Add Color', ['/admin/color/create'], [
                'class' => 'btn btn-warning',
                'title' => 'Add New Color'
            ]) ?>
        </div>
    </div>

    <?php if (empty($textures)): ?>
        <div class="alert alert-info text-center">
            <h4>No textures available</h4>
            <p>Start by adding your first texture.</p>
            <?= Html::a('Add First Texture', ['create'], [
                'class' => 'btn btn-primary btn-lg'
            ]) ?>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($textures as $texture): ?>
                <div class="col-md-3 col-sm-4 col-xs-6 mb-4">
                    <div class="card h-100 texture-card">
                        <div class="texture-image-container" style="height: 200px; overflow: hidden;">
                            <?php if ($texture->image_path && file_exists(Yii::getAlias('@webroot') . '/' . $texture->image_path)): ?>
                                <?= Html::img('/' . Html::encode($texture->image_path), [
                                    'class' => 'card-img-top',
                                    'style' => 'width: 100%; height: 100%; object-fit: cover; cursor: pointer;',
                                    'onclick' => 'window.open(this.src, "_blank")',
                                    'title' => 'Click to view full size'
                                ]) ?>
                            <?php else: ?>
                                <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 100%;">
                                    <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= Html::encode($texture->title) ?></h5>
                            
                            <?php if ($texture->prompt_suffix): ?>
                                <p class="card-text text-muted small flex-grow-1">
                                    <?= Html::encode($texture->prompt_suffix) ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="mt-auto">
                                <small class="text-muted">
                                    Created: <?= Yii::$app->formatter->asDatetime($texture->created_at) ?>
                                </small>
                            </div>
                            
                            <div class="mt-2">
                                <div class="btn-group w-100" role="group">
                                    <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $texture->id], [
                                        'class' => 'btn btn-outline-primary btn-sm',
                                        'title' => 'View'
                                    ]) ?>
                                    <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $texture->id], [
                                        'class' => 'btn btn-outline-secondary btn-sm',
                                        'title' => 'Edit'
                                    ]) ?>
                                    <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $texture->id], [
                                        'class' => 'btn btn-outline-danger btn-sm',
                                        'title' => 'Delete',
                                        'data' => [
                                            'confirm' => 'Are you sure you want to delete this texture?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="d-flex justify-content-center mt-4">
            <?= Html::a('Add Another Texture', ['create'], [
                'class' => 'btn btn-success btn-lg'
            ]) ?>
        </div>
    <?php endif; ?>
</div>

<style>
.texture-card {
    transition: transform 0.2s ease-in-out;
    border: 1px solid #e0e0e0;
}

.texture-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.texture-image-container img:hover {
    transform: scale(1.05);
    transition: transform 0.2s ease-in-out;
}
</style>