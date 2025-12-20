<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

?>

<div class="color-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <div class="mb-3">
        <label class="form-label"><?= Yii::t('app', 'Color Preview') ?></label>
        <div class="d-flex align-items-center gap-2">
            <input type="color" id="colorPickerPreview" value="<?= Html::encode($model->hex ?: '#FFFFFF') ?>" style="width:50px;height:38px;padding:0;border:none;cursor:pointer;">
            <?= $form->field($model, 'hex', ['options' => ['class' => 'mb-0', 'style' => 'flex:1;max-width:150px;']])->textInput(['maxlength' => 7, 'placeholder' => '#FFFFFF'])->label(false) ?>
        </div>
    </div>

    <?= $form->field($model, 'sort_order')->textInput(['type' => 'number']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var picker = document.getElementById('colorPickerPreview');
    var hexInput = document.querySelector('input[name="Color[hex]"]');
    
    if (picker && hexInput) {
        picker.addEventListener('input', function() {
            hexInput.value = picker.value.toUpperCase();
        });
        hexInput.addEventListener('input', function() {
            if (/^#[0-9A-Fa-f]{6}$/.test(hexInput.value)) {
                picker.value = hexInput.value;
            }
        });
    }
});
</script>
