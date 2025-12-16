<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

?>

<div class="texture-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->dropDownList([
        'texture' => 'texture',
    ], ['prompt' => 'Select type']) ?>

    <?= $form->field($model, 'prompt_suffix')->textInput(['maxlength' => true, 'placeholder' => 'Optional: e.g. "matte finish", "glossy"']) ?>

    <?= $form->field($model, 'image_path')->fileInput(['accept' => 'image/*', 'required' => $model->isNewRecord]) ?>
    <p class="text-muted small">Texture image is required. Gemini will use this image as reference.</p>

    <?php if (!$model->isNewRecord && $model->image_path): ?>
        <div class="mb-3">
            <label class="form-label">Current Image:</label><br>
            <?= Html::img('/' . $model->image_path, ['style' => 'max-width: 240px; border-radius: 8px;']) ?>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Save', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
