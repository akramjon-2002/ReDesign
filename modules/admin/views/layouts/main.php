<?php

use app\assets\AppAsset;
use yii\bootstrap5\Html;
use yii\bootstrap5\Breadcrumbs;
use app\widgets\Alert;

AppAsset::register($this);
$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1']);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style>
        body { background: #f8fafc; }
        .admin-shell { min-height: 100vh; display: flex; }
        .admin-sidebar { width: 260px; background: #111827; color: #fff; }
        .admin-sidebar a { color: rgba(255,255,255,.85); text-decoration: none; }
        .admin-sidebar a.active { background: rgba(255,255,255,.12); color: #fff; }
        .admin-content { flex: 1; }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<div class="admin-shell">
    <aside class="admin-sidebar p-3">
        <div class="mb-3 fw-semibold"><?= Yii::t('app', 'Admin Panel') ?></div>
        <div class="nav flex-column gap-1">
            <?php
            $route = Yii::$app->controller->route;
            $isTexture = strpos($route, 'admin/texture') === 0;
            $isColor = strpos($route, 'admin/color') === 0;
            echo Html::a(Yii::t('app', 'Textures'), ['/admin/texture/index'], ['class' => 'px-2 py-2 rounded ' . ($isTexture ? 'active' : '')]);
            echo Html::a(Yii::t('app', 'Colors'), ['/admin/color/index'], ['class' => 'px-2 py-2 rounded ' . ($isColor ? 'active' : '')]);
            ?>
        </div>
    </aside>

    <main class="admin-content">
        <div class="container-fluid py-3">
            <?php if (!empty($this->params['breadcrumbs'])): ?>
                <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
            <?php endif ?>
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </main>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
