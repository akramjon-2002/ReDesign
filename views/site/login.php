<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Admin Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .form-group input[type="text"].is-invalid,
        .form-group input[type="password"].is-invalid {
            border-color: #dc3545;
        }
        .invalid-feedback {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        .checkbox-group {
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }
        .checkbox-group input[type="checkbox"] {
            margin-right: 8px;
            cursor: pointer;
        }
        .checkbox-group label {
            margin: 0;
            cursor: pointer;
            color: #666;
            font-size: 14px;
            font-weight: normal;
        }
        .login-button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .login-button:active {
            transform: translateY(0);
        }
        .help-text {
            color: #999;
            font-size: 12px;
            margin-top: 20px;
            text-align: center;
        }
        .help-text strong {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Admin Panel</h1>
            <p>Please sign in to continue</p>
        </div>

        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

            <div class="form-group">
                <?= $form->field($model, 'username', [
                    'template' => "{label}\n{input}\n{error}"
                ])->textInput([
                    'placeholder' => 'Username',
                    'autofocus' => true,
                    'class' => 'form-control' . ($model->hasErrors('username') ? ' is-invalid' : '')
                ]) ?>
            </div>

            <div class="form-group">
                <?= $form->field($model, 'password', [
                    'template' => "{label}\n{input}\n{error}"
                ])->passwordInput([
                    'placeholder' => 'Password',
                    'class' => 'form-control' . ($model->hasErrors('password') ? ' is-invalid' : '')
                ]) ?>
            </div>

            <div class="checkbox-group">
                <?= $form->field($model, 'rememberMe', [
                    'template' => "{input} {label}"
                ])->checkbox() ?>
            </div>

            <?= Html::submitButton('Sign In', ['class' => 'login-button']) ?>

        <?php ActiveForm::end(); ?>

        <div class="help-text">
            <strong>Demo Credentials:</strong><br>
            Username: <strong>admin</strong><br>
            Password: <strong>admin123</strong>
        </div>
    </div>
</body>
</html>
