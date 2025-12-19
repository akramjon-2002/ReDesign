<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Interior Remodel';
?>

<div class="container py-4">
    <div class="mb-3">
        <div class="h4 mb-1">Remodel your room</div>
        <div class="muted">Загрузи фото, выбери текстуру и/или цвет.</div>
    </div>

    <div class="card p-3">
        <form id="uploadForm" method="post" enctype="multipart/form-data" action="<?= Html::encode(Url::to(['/telegram/webapp/upload'])) ?>">
            <input type="hidden" name="_csrf" value="<?= Html::encode(Yii::$app->request->csrfToken) ?>">
            <input id="userId" type="hidden" name="user_id" value="">

            <div class="mb-3">
                <label class="form-label">Texture (optional)</label>
                <select id="textureId" name="texture_id" class="form-select">
                    <option value="">-- no texture --</option>
                    <?php foreach ($textures as $texture): ?>
                        <option value="<?= (int)$texture->id ?>" data-preview="<?= Html::encode($texture->image_path ? '/' . $texture->image_path : '') ?>">
                            <?= Html::encode($texture->title) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="mt-2" id="texturePreview" style="display:none;">
                    <img id="texturePreviewImg" src="" alt="preview" style="max-width: 100%; border-radius: 10px;">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Color (optional)</label>
                <?php if (!empty($colors)): ?>
                <div class="color-palette mb-2" style="display: flex; flex-wrap: wrap; gap: 6px;">
                    <?php foreach ($colors as $color): ?>
                        <div class="color-swatch" data-color="<?= Html::encode($color->hex) ?>" title="<?= Html::encode($color->title) ?>"
                             style="width: 32px; height: 32px; background: <?= Html::encode($color->hex) ?>; border: 2px solid #ccc; border-radius: 6px; cursor: pointer;"></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <div class="d-flex align-items-center gap-2">
                    <input type="color" id="colorPicker" value="#FFFFFF" style="width: 50px; height: 38px; padding: 0; border: none; cursor: pointer;">
                    <input type="text" id="colorHex" name="color" class="form-control" placeholder="#FFFFFF" style="max-width: 120px;">
                    <button type="button" id="clearColor" class="btn btn-outline-secondary btn-sm">Clear</button>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Photo</label>
                <input id="photo" type="file" name="photo" class="form-control" accept="image/*" required>
            </div>

            <div class="d-grid gap-2">
                <button id="submitBtn" type="submit" class="btn btn-primary">Generate</button>
                <div id="status" class="muted" style="min-height: 22px;"></div>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
  function setStatus(text) {
    var el = document.getElementById('status');
    if (el) el.textContent = text || '';
  }

  var userIdInput = document.getElementById('userId');
  var submitBtn = document.getElementById('submitBtn');
  var photoInput = document.getElementById('photo');
  var maxPhotoBytes = 5 * 1024 * 1024;
  void photoInput;

  function getTelegramUserId() {
    try {
      // 1. Попробовать из initDataUnsafe.user
      if (window.Telegram && Telegram.WebApp && Telegram.WebApp.initDataUnsafe && Telegram.WebApp.initDataUnsafe.user) {
        return Telegram.WebApp.initDataUnsafe.user.id;
      }

      // 2. Попробовать распарсить из initData строки
      if (window.Telegram && Telegram.WebApp && Telegram.WebApp.initData) {
        var initData = String(Telegram.WebApp.initData || '');
        if (initData) {
          var params = new URLSearchParams(initData);
          var userStr = params.get('user');
          if (userStr) {
            var userObj = JSON.parse(userStr);
            if (userObj && userObj.id) return userObj.id;
          }
        }
      }

      // 3. Fallback: взять user_id из query-параметра URL (передаётся ботом)
      var urlParams = new URLSearchParams(window.location.search);
      var urlUserId = urlParams.get('user_id');
      if (urlUserId) return urlUserId;

    } catch (e) {}
    return null;
  }

  function setTelegramUserId(id) {
    if (userIdInput) {
      userIdInput.value = String(id);
    }
    if (submitBtn) {
      submitBtn.disabled = false;
    }
    setStatus('Telegram user id: ' + String(id));
  }

  // Telegram WebApp может подгружать initDataUnsafe с задержкой — ждём чуть-чуть.
  if (submitBtn) submitBtn.disabled = true;
  setStatus('Ожидание Telegram user id...');
  var attemptsLeft = 240; // ~60 секунд при интервале 250мс
  var warned = false;
  var tgTimer = setInterval(function () {
    var id = getTelegramUserId();
    if (id) {
      clearInterval(tgTimer);
      setTelegramUserId(id);
      return;
    }
    attemptsLeft--;
    if (!warned && attemptsLeft === 200) {
      warned = true;
      var hasTelegram = !!window.Telegram;
      var hasWebApp = !!(window.Telegram && Telegram.WebApp);
      var hasInitDataUnsafe = !!(window.Telegram && Telegram.WebApp && Telegram.WebApp.initDataUnsafe);
      var hasUser = !!(window.Telegram && Telegram.WebApp && Telegram.WebApp.initDataUnsafe && Telegram.WebApp.initDataUnsafe.user);
      setStatus('Ожидание Telegram user id... (Telegram=' + hasTelegram + ', WebApp=' + hasWebApp + ', initData=' + hasInitDataUnsafe + ', user=' + hasUser + ')');
    }
    if (attemptsLeft <= 0) {
      clearInterval(tgTimer);
      setStatus('Не удалось получить Telegram user id. Открой WebApp через кнопку /start в боте и попробуй снова.');
    }
  }, 250);

  var select = document.getElementById('textureId');
  var previewWrap = document.getElementById('texturePreview');
  var previewImg = document.getElementById('texturePreviewImg');
  if (select) {
    select.addEventListener('change', function () {
      var opt = select.options[select.selectedIndex];
      var url = opt ? opt.getAttribute('data-preview') : '';
      if (url) {
        previewImg.src = url;
        previewWrap.style.display = 'block';
      } else {
        previewWrap.style.display = 'none';
      }
    });
  }

  // Color picker logic
  var colorPicker = document.getElementById('colorPicker');
  var colorHex = document.getElementById('colorHex');
  var clearColorBtn = document.getElementById('clearColor');
  var swatches = document.querySelectorAll('.color-swatch');

  function setColor(hex) {
    if (colorHex) colorHex.value = hex || '';
    if (colorPicker && hex) colorPicker.value = hex;
    swatches.forEach(function(s) {
      s.style.borderColor = (s.getAttribute('data-color') === hex) ? '#000' : '#ccc';
    });
  }

  if (colorPicker) {
    colorPicker.addEventListener('input', function() {
      setColor(colorPicker.value);
    });
  }

  if (colorHex) {
    colorHex.addEventListener('input', function() {
      var val = colorHex.value.trim();
      if (/^#[0-9A-Fa-f]{6}$/.test(val) && colorPicker) {
        colorPicker.value = val;
      }
      swatches.forEach(function(s) {
        s.style.borderColor = (s.getAttribute('data-color').toUpperCase() === val.toUpperCase()) ? '#000' : '#ccc';
      });
    });
  }

  swatches.forEach(function(swatch) {
    swatch.addEventListener('click', function() {
      setColor(swatch.getAttribute('data-color'));
    });
  });

  if (clearColorBtn) {
    clearColorBtn.addEventListener('click', function() {
      setColor('');
      if (colorPicker) colorPicker.value = '#FFFFFF';
    });
  }

  var form = document.getElementById('uploadForm');
  if (!form) return;

  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    if (!userIdInput || !userIdInput.value) {
      setStatus('Ошибка: не найден Telegram user id. Открой WebApp через кнопку /start в боте.');
      return;
    }

    if (photoInput && photoInput.files && photoInput.files[0]) {
      if (photoInput.files[0].size > maxPhotoBytes) {
        setStatus('Фото больше 5 МБ. Загрузите файл меньше.');
        return;
      }
    }

    setStatus('Uploading...');
    if (submitBtn) submitBtn.disabled = true;

    try {
      var fd = new FormData(form);
      var res = await fetch(form.action, {
        method: 'POST',
        body: fd,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      var data = await res.json();
      if (!res.ok || !data || data.ok !== true) {
        setStatus('Error: ' + (data && data.message ? data.message : res.status));
        return;
      }

      setStatus('Queued. Request #' + data.request_id + ' (' + data.status + ')');
      if (window.Telegram && Telegram.WebApp) {
        try { Telegram.WebApp.showAlert('Заявка поставлена в очередь: #' + data.request_id); } catch (e) {}
      }
    } catch (err) {
      setStatus('Error: ' + (err && err.message ? err.message : 'unknown'));
    } finally {
      if (submitBtn) submitBtn.disabled = false;
    }
  });
})();
</script>
