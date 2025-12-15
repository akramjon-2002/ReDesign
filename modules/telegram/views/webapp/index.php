<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Interior Remodel';
?>

<div class="container py-4">
    <div class="mb-3">
        <div class="h4 mb-1">Remodel your room</div>
        <div class="muted">Загрузи фото и выбери стиль.</div>
    </div>

    <div class="card p-3">
        <form id="uploadForm" method="post" enctype="multipart/form-data" action="<?= Html::encode(Url::to(['/telegram/webapp/upload'])) ?>">
            <input type="hidden" name="_csrf" value="<?= Html::encode(Yii::$app->request->csrfToken) ?>">

            <input id="userId" type="hidden" name="user_id" value="">
            <input id="maskInput" type="hidden" name="mask" value="">

          

            <div class="mb-3">
                <label class="form-label">Texture</label>
                <select id="textureId" name="texture_id" class="form-select">
                    <option value="">-- select --</option>
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
                <label class="form-label">Photo</label>
                <input id="photo" type="file" name="photo" class="form-control" accept="image/*" required>
                <div class="mt-2" id="maskWrap" style="display:none;">
                    <div class="muted" style="margin-bottom:8px;">Пальцем закрась стену (это маска). Белое = что менять.</div>
                    <canvas id="paintCanvas" style="width:100%; border-radius:10px; touch-action:none;"></canvas>
                    <div class="d-grid gap-2 mt-2">
                        <button id="clearMaskBtn" type="button" class="btn btn-outline-secondary">Очистить маску</button>
                    </div>
                </div>
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
  var maskInput = document.getElementById('maskInput');
  var maskWrap = document.getElementById('maskWrap');
  var paintCanvas = document.getElementById('paintCanvas');
  var clearMaskBtn = document.getElementById('clearMaskBtn');

  var baseImg = null;
  var maskCanvas = null;
  var maskCtx = null;
  var paintCtx = paintCanvas ? paintCanvas.getContext('2d') : null;
  var drawing = false;
  var lastX = 0;
  var lastY = 0;

  function resetMask() {
    if (!baseImg || !paintCanvas || !paintCtx) return;
    paintCtx.clearRect(0, 0, paintCanvas.width, paintCanvas.height);
    paintCtx.drawImage(baseImg, 0, 0);
    if (maskCanvas && maskCtx) {
      maskCtx.fillStyle = 'black';
      maskCtx.fillRect(0, 0, maskCanvas.width, maskCanvas.height);
    }
    if (maskInput) maskInput.value = '';
  }

  function canvasPoint(ev) {
    var rect = paintCanvas.getBoundingClientRect();
    var x = (ev.clientX - rect.left) * (paintCanvas.width / rect.width);
    var y = (ev.clientY - rect.top) * (paintCanvas.height / rect.height);
    return { x: x, y: y };
  }

  function startDraw(ev) {
    if (!baseImg || !paintCanvas || !paintCtx || !maskCtx) return;
    drawing = true;
    var p = canvasPoint(ev);
    lastX = p.x;
    lastY = p.y;
  }

  function moveDraw(ev) {
    if (!drawing || !paintCtx || !maskCtx) return;
    ev.preventDefault();
    var p = canvasPoint(ev);
    paintCtx.strokeStyle = 'rgba(255,255,255,0.35)';
    paintCtx.lineWidth = Math.max(40, Math.min(paintCanvas.width, paintCanvas.height) * 0.06);
    paintCtx.lineCap = 'round';
    paintCtx.beginPath();
    paintCtx.moveTo(lastX, lastY);
    paintCtx.lineTo(p.x, p.y);
    paintCtx.stroke();

    maskCtx.strokeStyle = 'white';
    maskCtx.lineWidth = paintCtx.lineWidth;
    maskCtx.lineCap = 'round';
    maskCtx.beginPath();
    maskCtx.moveTo(lastX, lastY);
    maskCtx.lineTo(p.x, p.y);
    maskCtx.stroke();

    lastX = p.x;
    lastY = p.y;
  }

  function endDraw() {
    drawing = false;
    if (maskInput && maskCanvas) {
      maskInput.value = maskCanvas.toDataURL('image/png');
    }
  }

  if (photoInput) {
    photoInput.addEventListener('change', function () {
      var file = photoInput.files && photoInput.files[0];
      if (!file) return;

      var url = URL.createObjectURL(file);
      baseImg = new Image();
      baseImg.onload = function () {
        if (!paintCanvas || !paintCtx) return;
        paintCanvas.width = baseImg.naturalWidth || 1024;
        paintCanvas.height = baseImg.naturalHeight || 1024;
        maskCanvas = document.createElement('canvas');
        maskCanvas.width = paintCanvas.width;
        maskCanvas.height = paintCanvas.height;
        maskCtx = maskCanvas.getContext('2d');
        resetMask();
        if (maskWrap) maskWrap.style.display = 'block';
      };
      baseImg.src = url;
    });
  }

  if (paintCanvas) {
    paintCanvas.addEventListener('pointerdown', startDraw);
    paintCanvas.addEventListener('pointermove', moveDraw);
    paintCanvas.addEventListener('pointerup', endDraw);
    paintCanvas.addEventListener('pointercancel', endDraw);
    paintCanvas.addEventListener('pointerleave', endDraw);
  }

  if (clearMaskBtn) {
    clearMaskBtn.addEventListener('click', function () {
      resetMask();
    });
  }

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

  var form = document.getElementById('uploadForm');
  if (!form) return;

  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    if (maskInput && maskCanvas && !maskInput.value) {
      try {
        maskInput.value = maskCanvas.toDataURL('image/png');
      } catch (e) {}
    }

    if (!userIdInput || !userIdInput.value) {
      setStatus('Ошибка: не найден Telegram user id. Открой WebApp через кнопку /start в боте.');
      return;
    }

    if (!maskInput || !maskInput.value) {
      setStatus('Ошибка: нарисуй маску на стене (где менять текстуру).');
      return;
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
