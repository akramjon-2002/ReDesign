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

            <div class="mb-3">
                <label class="form-label">Telegram user id</label>
                <input id="userId" type="text" name="user_id" class="form-control" required>
            </div>

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
  if (window.Telegram && Telegram.WebApp && Telegram.WebApp.initDataUnsafe && Telegram.WebApp.initDataUnsafe.user) {
    try {
      var tgUserId = Telegram.WebApp.initDataUnsafe.user.id;
      if (tgUserId && userIdInput) userIdInput.value = String(tgUserId);
    } catch (e) {}
  }

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
  var submitBtn = document.getElementById('submitBtn');
  if (!form) return;

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
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
