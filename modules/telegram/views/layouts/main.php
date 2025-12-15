<?php

use app\assets\AppAsset;
use yii\bootstrap5\Html;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, viewport-fit=cover']);
$this->registerJsFile('https://telegram.org/js/telegram-web-app.js', ['position' => \yii\web\View::POS_HEAD]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style>
        body { background: #0b1220; color: #e5e7eb; }
        .card { background: #0f172a; border: 1px solid rgba(255,255,255,.08); }
        .form-control, .form-select { background: #0b1220; border-color: rgba(255,255,255,.12); color: #e5e7eb; }
        .form-control:focus, .form-select:focus { box-shadow: none; border-color: rgba(99, 102, 241, .7); }
        .btn-primary { background: #4f46e5; border-color: #4f46e5; }
        .btn-primary:hover { background: #4338ca; border-color: #4338ca; }
        .muted { color: rgba(229,231,235,.75); }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<?= $content ?>

<script>
(function () {
  if (window.Telegram && Telegram.WebApp) {
    try {
      Telegram.WebApp.ready();
      Telegram.WebApp.expand();
    } catch (e) {}
  }
})();
</script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
