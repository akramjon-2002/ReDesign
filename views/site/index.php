<?php

/** @var yii\web\View $this */

$this->title = 'Interior Design';
?>
<style>
    .hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 100px 0;
        text-align: center;
        margin-bottom: 50px;
    }
    .hero h1 {
        font-size: 48px;
        margin-bottom: 20px;
        font-weight: bold;
    }
    .hero p {
        font-size: 18px;
        margin-bottom: 30px;
    }
    .btn-group {
        display: flex;
        gap: 15px;
        justify-content: center;
    }
    .btn {
        padding: 12px 30px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-primary {
        background: white;
        color: #667eea;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }
    .btn-secondary {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 2px solid white;
    }
    .btn-secondary:hover {
        background: white;
        color: #667eea;
    }
    .features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin: 60px 0;
    }
    .feature-card {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        text-align: center;
    }
    .feature-card h3 {
        color: #667eea;
        margin-bottom: 15px;
        font-size: 24px;
    }
    .feature-card p {
        color: #666;
        line-height: 1.6;
    }
    .icon {
        font-size: 48px;
        margin-bottom: 15px;
    }
</style>

<div class="hero">
    <div class="container">
        <h1>Interior Design System</h1>
        <p>Управляйте текстурами и цветами для вашего интерьера</p>
        <div class="btn-group">
            <?php if (Yii::$app->user->isGuest): ?>
                <a href="<?= \yii\helpers\Url::to(['/site/login']) ?>" class="btn btn-primary">Войти в систему</a>
            <?php else: ?>
                <a href="<?= \yii\helpers\Url::to(['/admin/texture/index']) ?>" class="btn btn-primary">Перейти в админку</a>
                <a href="<?= \yii\helpers\Url::to(['/site/logout']) ?>" class="btn btn-secondary" onclick="document.getElementById('logout-form').submit(); return false;">Выйти</a>
                <form id="logout-form" method="post" action="<?= \yii\helpers\Url::to(['/site/logout']) ?>" style="display: none;">
                    <?= \yii\helpers\Html::csrfMetaTags() ?>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container">
    <div class="features">
        <div class="feature-card">
            <div class="icon">🎨</div>
            <h3>Текстуры</h3>
            <p>Управляйте всеми текстурами для интерьера в одном месте. Добавляйте новые, редактируйте и удаляйте существующие.</p>
        </div>
        <div class="feature-card">
            <div class="icon">🌈</div>
            <h3>Цвета</h3>
            <p>Подберите идеальный цвет для вашего дизайна. Большой выбор предопределённых цветов или создайте свой.</p>
        </div>
        <div class="feature-card">
            <div class="icon">⚙️</div>
            <h3>Управление</h3>
            <p>Простой и удобный интерфейс администратора для управления всеми элементами системы.</p>
        </div>
    </div>
</div>
