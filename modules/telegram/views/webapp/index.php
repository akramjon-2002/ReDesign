<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Interior Remodel';
?>

<div class="container py-4">
    <h3 class="mb-3">Upload room photo</h3>

    <form method="post" enctype="multipart/form-data" action="<?= Html::encode(Url::to(['/telegram/webapp/upload'])) ?>">
        <div class="mb-3">
            <label class="form-label">Telegram user id</label>
            <input type="text" name="user_id" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Texture</label>
            <select name="texture_id" class="form-select">
                <option value="">-- select --</option>
                <?php foreach ($textures as $texture): ?>
                    <option value="<?= (int)$texture->id ?>"><?= Html::encode($texture->title) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Photo</label>
            <input type="file" name="photo" class="form-control" accept="image/*" required>
        </div>

        <button type="submit" class="btn btn-primary">Send</button>
    </form>
</div>
