<?php

use app\assets\AppAsset;
use yii\bootstrap5\Html;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1, user-scalable=no']);
$this->registerJsFile('https://telegram.org/js/telegram-web-app.js', ['position' => \yii\web\View::POS_HEAD]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0d1117;
            --bg-secondary: #161b22;
            --bg-card: #1c2128;
            --accent-cyan: #00d4aa;
            --accent-cyan-hover: #00b894;
            --text-primary: #ffffff;
            --text-secondary: #8b949e;
            --border-color: rgba(255,255,255,0.1);
            --safe-area-top: env(safe-area-inset-top, 0px);
            --safe-area-bottom: env(safe-area-inset-bottom, 0px);
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            min-height: 100dvh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }
        body {
            padding-top: var(--safe-area-top);
            padding-bottom: var(--safe-area-bottom);
        }
        .app-container {
            max-width: 430px;
            margin: 0 auto;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        /* Screen transitions */
        .screen {
            display: none;
            flex-direction: column;
            min-height: 100vh;
            min-height: 100dvh;
            animation: fadeIn 0.3s ease;
        }
        .screen.active {
            display: flex;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<div class="app-container">
    <?= $content ?>
</div>

<script>
(function () {
  if (window.Telegram && Telegram.WebApp) {
    try {
      Telegram.WebApp.ready();
      Telegram.WebApp.expand();
      Telegram.WebApp.enableClosingConfirmation();
    } catch (e) {}
  }
})();

// Screen navigation
window.AppNav = {
    showScreen: function(screenId) {
        document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
        const screen = document.getElementById(screenId);
        if (screen) {
            screen.classList.add('active');
            window.scrollTo(0, 0);
        }
    },
    goBack: function() {
        const screens = ['onboarding-screen', 'home-screen', 'editor-screen', 'loading-screen', 'result-screen'];
        const current = document.querySelector('.screen.active');
        if (current) {
            const idx = screens.indexOf(current.id);
            if (idx > 0) {
                this.showScreen(screens[idx - 1]);
            }
        }
    }
};
</script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
