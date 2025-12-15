<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

?>

<div class="texture-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->dropDownList([
        'texture' => 'texture',
        'paint' => 'paint',
        'architecture_style' => 'architecture_style',
    ], ['prompt' => 'Select type']) ?>

    <?= $form->field($model, 'prompt_suffix')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'image_path')->fileInput(['accept' => 'image/*']) ?>

    <?php if (!$model->isNewRecord && $model->image_path): ?>
        <div class="mb-3">
            <?= Html::img('/' . $model->image_path, ['style' => 'max-width: 240px; border-radius: 8px;']) ?>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Save', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
