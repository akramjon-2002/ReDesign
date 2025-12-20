<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Interior Remodel');
?>

<div class="webapp-container">
    <div class="webapp-header">
        <h1><?= Yii::t('app', 'Remodel your room') ?></h1>
        <p><?= Yii::t('app', 'Upload photo, select texture and/or color') ?></p>
    </div>

    <form id="uploadForm" method="post" enctype="multipart/form-data" action="<?= Html::encode(Url::to(['/telegram/webapp/upload'])) ?>">
        <input type="hidden" name="_csrf" value="<?= Html::encode(Yii::$app->request->csrfToken) ?>">
        <input id="userId" type="hidden" name="user_id" value="">

        <div class="form-group">
            <label class="form-label"><?= Yii::t('app', 'Texture') ?> <span class="optional">(<?= Yii::t('app', 'optional') ?>)</span></label>
            <select id="textureId" name="texture_id" class="form-select">
                <option value="">-- <?= Yii::t('app', 'no texture') ?> --</option>
                <?php foreach ($textures as $texture): ?>
                    <option value="<?= (int)$texture->id ?>" data-preview="<?= Html::encode($texture->image_path ? Yii::$app->request->baseUrl . '/' . $texture->image_path : '') ?>">
                        <?= Html::encode($texture->title) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="texture-preview" id="texturePreview">
                <img id="texturePreviewImg" src="" alt="preview">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label"><?= Yii::t('app', 'Color') ?> <span class="optional">(<?= Yii::t('app', 'optional') ?>)</span></label>
            <?php if (!empty($colors)): ?>
            <div class="color-palette">
                <?php foreach ($colors as $color): ?>
                    <div class="color-swatch" data-color="<?= Html::encode($color->hex) ?>" title="<?= Html::encode($color->title) ?>"
                         style="background: <?= Html::encode($color->hex) ?>;"></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="color-input-row">
                <input type="color" id="colorPicker" value="#FFFFFF" class="color-picker">
                <input type="text" id="colorHex" name="color" class="color-hex-input" placeholder="#FFFFFF">
                <button type="button" id="clearColor" class="btn-clear"><?= Yii::t('app', 'Clear') ?></button>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label"><?= Yii::t('app', 'Photo') ?></label>
            <input id="photo" type="file" name="photo" class="form-file" accept="image/*" required>
            <div class="photo-preview" id="photoPreview">
                <img id="photoPreviewImg" src="" alt="photo preview">
            </div>
        </div>

        <button id="submitBtn" type="submit" class="btn-submit"><?= Yii::t('app', 'Generate') ?></button>
        <div id="status" class="status-text"></div>
    </form>
</div>

<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { 
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
    background: var(--tg-theme-bg-color, #1a1a2e); 
    color: var(--tg-theme-text-color, #eee);
    min-height: 100vh;
}
.webapp-container { 
    max-width: 420px; 
    margin: 0 auto; 
    padding: 20px 16px; 
}
.webapp-header { margin-bottom: 24px; }
.webapp-header h1 { 
    font-size: 22px; 
    font-weight: 600; 
    margin-bottom: 6px;
    color: var(--tg-theme-text-color, #fff);
}
.webapp-header p { 
    font-size: 14px; 
    opacity: 0.7; 
}
.form-group { margin-bottom: 20px; }
.form-label { 
    display: block; 
    font-size: 14px; 
    font-weight: 500; 
    margin-bottom: 8px; 
}
.optional { 
    font-weight: 400; 
    opacity: 0.6; 
    font-size: 12px; 
}
.form-select, .form-file, .color-hex-input {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid var(--tg-theme-hint-color, #444);
    border-radius: 10px;
    background: var(--tg-theme-secondary-bg-color, #16213e);
    color: var(--tg-theme-text-color, #fff);
    font-size: 15px;
    outline: none;
    transition: border-color 0.2s;
}
.form-select:focus, .form-file:focus, .color-hex-input:focus {
    border-color: var(--tg-theme-button-color, #0088cc);
}
.texture-preview {
    display: none;
    margin-top: 12px;
    border-radius: 10px;
    overflow: hidden;
    background: var(--tg-theme-secondary-bg-color, #16213e);
    padding: 8px;
}
.texture-preview img {
    width: 100%;
    max-height: 120px;
    object-fit: cover;
    border-radius: 8px;
}
.photo-preview {
    display: none;
    margin-top: 12px;
    border-radius: 10px;
    overflow: hidden;
    background: var(--tg-theme-secondary-bg-color, #16213e);
    padding: 8px;
}
.photo-preview img {
    width: 100%;
    max-height: 200px;
    object-fit: contain;
    border-radius: 8px;
}
.color-palette {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
}
.color-swatch {
    width: 36px;
    height: 36px;
    border: 2px solid transparent;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.15s, border-color 0.15s;
}
.color-swatch:hover {
    transform: scale(1.1);
}
.color-swatch.selected {
    border-color: var(--tg-theme-button-color, #0088cc);
}
.color-input-row {
    display: flex;
    gap: 10px;
    align-items: center;
}
.color-picker {
    width: 44px;
    height: 44px;
    padding: 0;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    background: transparent;
}
.color-hex-input {
    flex: 1;
    max-width: 120px;
}
.btn-clear {
    padding: 10px 16px;
    border: 1px solid var(--tg-theme-hint-color, #444);
    border-radius: 8px;
    background: transparent;
    color: var(--tg-theme-text-color, #fff);
    font-size: 14px;
    cursor: pointer;
    transition: background 0.2s;
}
.btn-clear:hover {
    background: rgba(255,255,255,0.1);
}
.btn-submit {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 10px;
    background: var(--tg-theme-button-color, #0088cc);
    color: var(--tg-theme-button-text-color, #fff);
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.1s;
}
.btn-submit:hover { opacity: 0.9; }
.btn-submit:active { transform: scale(0.98); }
.btn-submit:disabled { 
    opacity: 0.5; 
    cursor: not-allowed; 
}
.status-text {
    margin-top: 12px;
    font-size: 13px;
    text-align: center;
    opacity: 0.7;
    min-height: 20px;
}
</style>

<script>
// Переводы
var translations = {
  waitingTelegram: '<?= Html::encode(Yii::t('app', 'Waiting for Telegram...')) ?>',
  openViaTelegram: '<?= Html::encode(Yii::t('app', 'Open via Telegram bot')) ?>',
  errorTelegramNotFound: '<?= Html::encode(Yii::t('app', 'Error: Telegram user not found')) ?>',
  photoTooLarge: '<?= Html::encode(Yii::t('app', 'Photo is too large (max 5MB)')) ?>',
  uploading: '<?= Html::encode(Yii::t('app', 'Uploading...')) ?>',
  error: '<?= Html::encode(Yii::t('app', 'Error')) ?>',
  requestQueued: '<?= Html::encode(Yii::t('app', 'Request queued')) ?>'
};

document.addEventListener('DOMContentLoaded', function() {
  var userIdInput = document.getElementById('userId');
  var submitBtn = document.getElementById('submitBtn');
  var photoInput = document.getElementById('photo');
  var maxPhotoBytes = 5 * 1024 * 1024;
  var statusEl = document.getElementById('status');

  function setStatus(text) {
    if (statusEl) statusEl.textContent = text || '';
  }

  function getTelegramUserId() {
    try {
      if (window.Telegram && Telegram.WebApp && Telegram.WebApp.initDataUnsafe && Telegram.WebApp.initDataUnsafe.user) {
        return Telegram.WebApp.initDataUnsafe.user.id;
      }
      if (window.Telegram && Telegram.WebApp && Telegram.WebApp.initData) {
        var params = new URLSearchParams(Telegram.WebApp.initData);
        var userStr = params.get('user');
        if (userStr) {
          var userObj = JSON.parse(userStr);
          if (userObj && userObj.id) return userObj.id;
        }
      }
      var urlParams = new URLSearchParams(window.location.search);
      var urlUserId = urlParams.get('user_id');
      if (urlUserId) return urlUserId;
    } catch (e) {}
    return null;
  }

  function initApp() {
    if (submitBtn) submitBtn.disabled = true;
    setStatus(translations.waitingTelegram);
    
    var attempts = 80;
    var timer = setInterval(function() {
      var id = getTelegramUserId();
      if (id) {
        clearInterval(timer);
        if (userIdInput) userIdInput.value = id;
        if (submitBtn) submitBtn.disabled = false;
        setStatus('');
        return;
      }
      if (--attempts <= 0) {
        clearInterval(timer);
        setStatus(translations.openViaTelegram);
      }
    }, 250);
  }

  // Texture preview
  var textureSelect = document.getElementById('textureId');
  var previewWrap = document.getElementById('texturePreview');
  var previewImg = document.getElementById('texturePreviewImg');
  if (textureSelect) {
    textureSelect.addEventListener('change', function() {
      var opt = textureSelect.options[textureSelect.selectedIndex];
      var url = opt ? opt.getAttribute('data-preview') : '';
      if (url && previewWrap && previewImg) {
        previewImg.onerror = function() {
          previewWrap.style.display = 'none';
        };
        previewImg.onload = function() {
          previewWrap.style.display = 'block';
        };
        previewImg.src = url;
      } else if (previewWrap) {
        previewWrap.style.display = 'none';
      }
    });
  }

  // Photo preview
  var photoPreviewWrap = document.getElementById('photoPreview');
  var photoPreviewImg = document.getElementById('photoPreviewImg');
  if (photoInput) {
    photoInput.addEventListener('change', function() {
      var file = photoInput.files && photoInput.files[0];
      if (file && photoPreviewWrap && photoPreviewImg) {
        var reader = new FileReader();
        reader.onload = function(e) {
          photoPreviewImg.src = e.target.result;
          photoPreviewWrap.style.display = 'block';
        };
        reader.onerror = function() {
          photoPreviewWrap.style.display = 'none';
        };
        reader.readAsDataURL(file);
      } else if (photoPreviewWrap) {
        photoPreviewWrap.style.display = 'none';
      }
    });
  }

  // Color picker
  var colorPicker = document.getElementById('colorPicker');
  var colorHex = document.getElementById('colorHex');
  var clearBtn = document.getElementById('clearColor');
  var swatches = document.querySelectorAll('.color-swatch');

  function setColor(hex) {
    if (colorHex) colorHex.value = hex || '';
    if (colorPicker && hex) colorPicker.value = hex;
    swatches.forEach(function(s) {
      s.classList.toggle('selected', s.getAttribute('data-color') === hex);
    });
  }

  if (colorPicker) colorPicker.addEventListener('input', function() { setColor(colorPicker.value); });
  if (colorHex) colorHex.addEventListener('input', function() {
    var val = colorHex.value.trim();
    if (/^#[0-9A-Fa-f]{6}$/.test(val) && colorPicker) colorPicker.value = val;
    swatches.forEach(function(s) {
      s.classList.toggle('selected', s.getAttribute('data-color').toUpperCase() === val.toUpperCase());
    });
  });
  swatches.forEach(function(s) {
    s.addEventListener('click', function() { setColor(s.getAttribute('data-color')); });
  });
  if (clearBtn) clearBtn.addEventListener('click', function() {
    setColor('');
    if (colorPicker) colorPicker.value = '#FFFFFF';
  });

  // Form submit
  var form = document.getElementById('uploadForm');
  if (form) {
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      if (!userIdInput || !userIdInput.value) {
        setStatus(translations.errorTelegramNotFound);
        return;
      }
      if (photoInput && photoInput.files && photoInput.files[0] && photoInput.files[0].size > maxPhotoBytes) {
        setStatus(translations.photoTooLarge);
        return;
      }

      setStatus(translations.uploading);
      if (submitBtn) submitBtn.disabled = true;

      try {
        var res = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        var data = await res.json();
        if (!res.ok || !data || data.ok !== true) {
          setStatus(translations.error + ': ' + (data && data.message ? data.message : res.status));
          return;
        }
        setStatus(translations.requestQueued + ' #' + data.request_id);
        if (window.Telegram && Telegram.WebApp) {
          try { Telegram.WebApp.showAlert(translations.requestQueued + ': #' + data.request_id); } catch(e) {}
        }
      } catch(err) {
        setStatus(translations.error + ': ' + (err.message || 'unknown'));
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }

  initApp();
});
</script>
