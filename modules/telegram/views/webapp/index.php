<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'AI Wall Editor';
?>

<!-- Page 1: Onboarding -->
<div id="page-onboarding" class="page active">
    <div class="onboarding-container">
        <div class="onboarding-header">
            <div class="logo-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="#00D9FF" stroke-width="2"/>
                    <path d="M8 12L11 15L16 9" stroke="#00D9FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <button class="skip-btn" onclick="goToPage('home')">O'tkazib yuborish</button>
        </div>

        <div class="onboarding-slider">
            <div class="slides-container" id="slidesContainer">
                <div class="slide active">
                    <div class="comparison-image">
                        <div class="comparison-wrapper">
                            <div class="comparison-left">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='300' viewBox='0 0 200 300'%3E%3Crect fill='%23e8e4df' width='200' height='300'/%3E%3Crect fill='%23c4a574' y='250' width='200' height='50'/%3E%3C/svg%3E" alt="Before">
                                <span class="label label-old">ESKI (Old)</span>
                            </div>
                            <div class="comparison-divider">
                                <div class="divider-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M8 12L4 8M4 8L8 4M4 8H20M16 12L20 16M20 16L16 20M20 16H4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="comparison-right">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='300' viewBox='0 0 200 300'%3E%3Crect fill='%23006666' width='200' height='300'/%3E%3Crect fill='%23c4a574' y='250' width='200' height='50'/%3E%3C/svg%3E" alt="After">
                                <span class="label label-new">YANGI (New)</span>
                            </div>
                        </div>
                        <div class="ai-magic-badge">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="#00D9FF"/>
                            </svg>
                            AI MAGIC
                        </div>
                    </div>
                    <div class="slide-content">
                        <h2>Orzuingizdagi <span class="highlight">interyer</span></h2>
                        <p>Shunchaki rasmga oling va AI yordamida devor ranglarini yoki teksturasini soniyalar ichida o'zgartiring.</p>
                    </div>
                </div>
                <div class="slide">
                    <div class="comparison-image">
                        <div class="comparison-wrapper single">
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300' viewBox='0 0 300 300'%3E%3Crect fill='%23334455' width='300' height='300'/%3E%3Crect fill='%23c4a574' y='250' width='300' height='50'/%3E%3C/svg%3E" alt="Demo">
                        </div>
                    </div>
                    <div class="slide-content">
                        <h2>Tez va <span class="highlight">oson</span></h2>
                        <p>Bir necha bosqichda xonangizni yangi ko'rinishga ega qiling.</p>
                    </div>
                </div>
                <div class="slide">
                    <div class="comparison-image">
                        <div class="comparison-wrapper single">
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300' viewBox='0 0 300 300'%3E%3Crect fill='%23553344' width='300' height='300'/%3E%3Crect fill='%23c4a574' y='250' width='300' height='50'/%3E%3C/svg%3E" alt="Demo">
                        </div>
                    </div>
                    <div class="slide-content">
                        <h2>Professional <span class="highlight">natija</span></h2>
                        <p>AI texnologiyasi bilan professional darajadagi natijalar oling.</p>
                    </div>
                </div>
            </div>
            <div class="slide-dots">
                <span class="dot active" data-slide="0"></span>
                <span class="dot" data-slide="1"></span>
                <span class="dot" data-slide="2"></span>
            </div>
        </div>

        <div class="onboarding-footer">
            <button class="btn-primary" onclick="goToPage('home')">
                BOSHLASH <span class="arrow">→</span>
            </button>
            <p class="terms">Davom etish orqali siz <a href="#">Foydalanish shartlariga</a> rozilik bildirasiz.</p>
        </div>
    </div>
</div>

<!-- Page 2: Home -->
<div id="page-home" class="page">
    <div class="home-container">
        <div class="home-header">
            <h1 class="app-title">AI Wall Editor</h1>
            <button class="settings-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 1V3M12 21V23M4.22 4.22L5.64 5.64M18.36 18.36L19.78 19.78M1 12H3M21 12H23M4.22 19.78L5.64 18.36M18.36 5.64L19.78 4.22" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <div class="hero-section">
            <h2>Devorlaringizni <span class="highlight">AI</span><br>bilan o'zgartiring</h2>
            <p>Yangi dizayn yaratish uchun xonani rasmga oling yoki yuklang.</p>

            <div class="action-buttons">
                <button class="btn-action btn-camera" onclick="capturePhoto()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M23 19C23 20.1046 22.1046 21 21 21H3C1.89543 21 1 20.1046 1 19V8C1 6.89543 1.89543 6 3 6H7L9 3H15L17 6H21C22.1046 6 23 6.89543 23 8V19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="13" r="4" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    Rasmga olish
                </button>
                <button class="btn-action btn-gallery" onclick="selectFromGallery()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
                        <path d="M21 15L16 10L5 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Galereyadan yuklash
                </button>
            </div>
        </div>

        <div class="projects-section">
            <div class="section-header">
                <h3>Oxirgi Loyihalar</h3>
                <button class="link-btn" onclick="showAllProjects()">Barchasi</button>
            </div>
            <div class="projects-grid" id="projectsGrid">
                <!-- Projects will be loaded dynamically -->
                <div class="project-placeholder">
                    <p>Hali loyihalar yo'q</p>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Page 3: Editor -->
<div id="page-editor" class="page">
    <div class="editor-container">
        <div class="editor-header">
            <button class="back-btn" onclick="goToPage('home')">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M19 12H5M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <h1>Tahrirlash</h1>
            <button class="share-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M4 12V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="16,6 12,2 8,6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="12" y1="2" x2="12" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <div class="editor-preview">
            <img id="editorPreviewImg" src="" alt="Preview">
        </div>

        <div class="editor-panel">
            <div class="panel-handle"></div>
            
            <div class="tabs">
                <button class="tab active" data-tab="color">Rang</button>
                <button class="tab" data-tab="texture">Tekstura</button>
            </div>

            <div class="editor-panel-content">
                <div class="tab-content active" id="tab-color">
                    <div class="color-grid">
                        <?php foreach ($colors as $color): ?>
                        <div class="color-item" data-color="<?= Html::encode($color->hex) ?>" title="<?= Html::encode($color->title) ?>">
                            <div class="color-circle" style="background: <?= Html::encode($color->hex) ?>;"></div>
                            <span class="color-name"><?= Html::encode($color->title) ?></span>
                        </div>
                        <?php endforeach; ?>
                        <div class="color-item custom-color">
                            <input type="color" id="customColorPicker" value="#FFFFFF">
                            <div class="color-circle custom" id="customColorCircle"></div>
                            <span class="color-name">Boshqa</span>
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="tab-texture">
                    <div class="texture-grid">
                        <?php foreach ($textures as $texture): ?>
                        <div class="texture-item" data-texture-id="<?= (int)$texture->id ?>" data-preview="<?= Html::encode($texture->image_path ? Yii::$app->request->baseUrl . '/' . $texture->image_path : '') ?>">
                            <img src="<?= Html::encode($texture->image_path ? Yii::$app->request->baseUrl . '/' . $texture->image_path : '') ?>" alt="<?= Html::encode($texture->title) ?>">
                            <span class="texture-name"><?= Html::encode($texture->title) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <button class="btn-generate" id="generateBtn" onclick="generateImage()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="currentColor"/>
                </svg>
                AI bilan o'zgartirish
            </button>
        </div>
    </div>
    <input type="file" id="photoInput" accept="image/*" style="display:none">
    <input type="file" id="cameraInput" accept="image/*" capture="environment" style="display:none">
</div>

<!-- Page 4: Loading -->
<div id="page-loading" class="page">
    <div class="loading-container">
        <div class="loading-animation">
            <div class="loading-circle">
                <div class="loading-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="#00D9FF"/>
                    </svg>
                </div>
            </div>
        </div>
        <h2>Yuklanmoqda...</h2>
        <p>AI sizning devorlaringiz uchun yangi dizayn yaratmoqda</p>
        <button class="btn-cancel" onclick="cancelGeneration()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            Bekor qilish
        </button>
    </div>
</div>

<!-- Page 5: Result -->
<div id="page-result" class="page">
    <div class="result-container">
        <div class="result-header">
            <button class="back-btn" onclick="goToPage('home')">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M19 12H5M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <h1>Natija</h1>
            <div style="width:24px"></div>
        </div>

        <div class="result-image-container">
            <img id="resultImage" src="" alt="Result">
        </div>

        <div class="result-info">
            <div class="result-title-row">
                <h3 id="resultTitle">Yashash xonasi</h3>
                <button class="ai-edit-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="currentColor"/>
                    </svg>
                    AI EDIT
                </button>
            </div>
            <p class="result-details" id="resultDetails">Rang: Deep Teal #124A • Tekstura: Mat</p>
        </div>

        <div class="result-tabs">
            <button class="result-tab" onclick="showOriginal()">Oldin</button>
            <button class="result-tab active" onclick="showResult()">Keyin</button>
        </div>

        <div class="result-actions">
            <button class="btn-save" onclick="saveToGallery()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M21 15V19C21 20.1 20.1 21 19 21H5C3.9 21 3 20.1 3 19V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="7,10 12,15 17,10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Galereyaga Saqlash
            </button>
            <button class="btn-share" onclick="shareResult()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <circle cx="18" cy="5" r="3" stroke="currentColor" stroke-width="2"/>
                    <circle cx="6" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                    <circle cx="18" cy="19" r="3" stroke="currentColor" stroke-width="2"/>
                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" stroke="currentColor" stroke-width="2"/>
                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" stroke="currentColor" stroke-width="2"/>
                </svg>
                Ulashish
            </button>
        </div>
    </div>
</div>

<form id="uploadForm" method="post" enctype="multipart/form-data" action="<?= Html::encode(Url::to(['/telegram/webapp/upload'])) ?>" style="display:none">
    <input type="hidden" name="_csrf" value="<?= Html::encode(Yii::$app->request->csrfToken) ?>">
    <input id="userId" type="hidden" name="user_id" value="">
    <input id="formTextureId" type="hidden" name="texture_id" value="">
    <input id="formColor" type="hidden" name="color" value="">
    <input id="formPhoto" type="file" name="photo" accept="image/*">
</form>

<style>
:root {
    --primary: #00D9FF;
    --bg-dark: #0a0a0f;
    --bg-card: #1a1a2e;
    --bg-panel: #16213e;
    --text-primary: #ffffff;
    --text-secondary: rgba(255,255,255,0.7);
    --border-color: #2a2a3e;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body { 
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
    background: var(--bg-dark);
    color: var(--text-primary);
    min-height: 100vh;
    overflow-x: hidden;
}

/* Pages */
.page { display: none; min-height: 100vh; }
.page.active { display: block; }

/* Onboarding */
.onboarding-container {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    padding: 16px;
}
.onboarding-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
}
.logo-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: rgba(0,217,255,0.1); border-radius: 50%; }
.skip-btn { background: none; border: none; color: var(--primary); font-size: 14px; cursor: pointer; }

.onboarding-slider { flex: 1; display: flex; flex-direction: column; justify-content: center; }
.slides-container { position: relative; }
.slide { display: none; }
.slide.active { display: block; }

.comparison-image { position: relative; margin: 20px 0; }
.comparison-wrapper { display: flex; border-radius: 20px; overflow: hidden; position: relative; }
.comparison-wrapper.single { justify-content: center; }
.comparison-wrapper.single img { width: 100%; max-height: 350px; object-fit: cover; border-radius: 20px; }
.comparison-left, .comparison-right { flex: 1; position: relative; }
.comparison-left img, .comparison-right img { width: 100%; height: 350px; object-fit: cover; }
.comparison-divider { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); z-index: 10; }
.divider-icon { width: 48px; height: 48px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
.divider-icon svg { width: 24px; height: 24px; }
.divider-icon svg path { stroke: #333; }
.label { position: absolute; bottom: 12px; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 600; }
.label-old { left: 12px; background: rgba(0,0,0,0.7); color: white; }
.label-new { right: 12px; background: var(--primary); color: #000; }
.ai-magic-badge { position: absolute; top: 16px; left: 16px; background: rgba(0,0,0,0.7); color: var(--primary); padding: 8px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 6px; }

.slide-content { text-align: center; padding: 20px 0; }
.slide-content h2 { font-size: 28px; font-weight: 700; margin-bottom: 12px; }
.slide-content .highlight { color: var(--primary); }
.slide-content p { font-size: 15px; color: var(--text-secondary); line-height: 1.5; padding: 0 20px; }

.slide-dots { display: flex; justify-content: center; gap: 8px; margin-top: 20px; }
.dot { width: 24px; height: 6px; border-radius: 3px; background: rgba(255,255,255,0.3); cursor: pointer; transition: all 0.3s; }
.dot.active { width: 32px; background: var(--primary); }

.onboarding-footer { padding: 20px 0; }
.btn-primary { width: 100%; padding: 16px; background: var(--primary); color: #000; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }
.btn-primary .arrow { font-size: 18px; }
.terms { text-align: center; margin-top: 16px; font-size: 12px; color: var(--text-secondary); }
.terms a { color: var(--primary); text-decoration: underline; }

/* Home */
.home-container { min-height: 100vh; display: flex; flex-direction: column; padding-bottom: 80px; }
.home-header { display: flex; justify-content: space-between; align-items: center; padding: 16px; }
.app-title { font-size: 18px; font-weight: 600; }
.settings-btn { background: none; border: none; color: var(--primary); cursor: pointer; padding: 8px; }

.hero-section { background: linear-gradient(180deg, var(--bg-card) 0%, var(--bg-dark) 100%); border-radius: 20px; padding: 30px 20px; margin: 0 16px 24px; text-align: center; }
.hero-section h2 { font-size: 26px; font-weight: 700; margin-bottom: 12px; line-height: 1.3; }
.hero-section .highlight { color: var(--primary); }
.hero-section > p { font-size: 14px; color: var(--text-secondary); margin-bottom: 24px; }

.action-buttons { display: flex; flex-direction: column; gap: 12px; }
.btn-action { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 14px 20px; border-radius: 12px; font-size: 15px; font-weight: 500; cursor: pointer; border: none; }
.btn-camera { background: var(--primary); color: #000; }
.btn-gallery { background: var(--bg-panel); color: var(--text-primary); border: 1px solid var(--border-color); }

.projects-section { padding: 0 16px; flex: 1; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.section-header h3 { font-size: 18px; font-weight: 600; }
.link-btn { background: none; border: none; color: var(--primary); font-size: 14px; cursor: pointer; }

.projects-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
.project-card { background: var(--bg-card); border-radius: 12px; overflow: hidden; position: relative; }
.project-card img { width: 100%; height: 120px; object-fit: cover; }
.project-card .badge { position: absolute; top: 8px; right: 8px; padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; }
.project-card .badge.edited { background: var(--primary); color: #000; }
.project-card .badge.raw { background: rgba(255,255,255,0.2); color: white; }
.project-card .info { padding: 10px; }
.project-card .info h4 { font-size: 13px; font-weight: 500; margin-bottom: 4px; }
.project-card .info span { font-size: 11px; color: var(--text-secondary); }
.project-placeholder { grid-column: span 2; text-align: center; padding: 40px; color: var(--text-secondary); background: var(--bg-card); border-radius: 12px; }


/* Editor */
.editor-container { min-height: 100vh; display: flex; flex-direction: column; background: var(--bg-dark); }
.editor-header { display: flex; justify-content: space-between; align-items: center; padding: 16px; }
.editor-header h1 { font-size: 18px; font-weight: 600; }
.back-btn, .share-btn { background: none; border: none; color: var(--text-primary); cursor: pointer; padding: 8px; }

.editor-preview { 
    flex: 0 0 auto; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    position: relative; 
    padding: 16px; 
    max-height: 45vh;
    min-height: 200px;
    overflow: hidden;
}
.editor-preview img { 
    max-width: 100%; 
    max-height: 100%; 
    width: auto;
    height: auto;
    object-fit: contain; 
    border-radius: 12px; 
}

.editor-panel { 
    flex: 1 1 auto;
    background: var(--bg-card); 
    border-radius: 24px 24px 0 0; 
    padding: 16px;
    min-height: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.panel-handle { width: 40px; height: 4px; background: rgba(255,255,255,0.3); border-radius: 2px; margin: 0 auto 16px; }

.tabs { display: flex; background: var(--bg-panel); border-radius: 12px; padding: 4px; margin-bottom: 16px; flex-shrink: 0; }

.editor-panel-content {
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: hidden;
    margin-bottom: 16px;
    min-height: 0;
}
.tab { flex: 1; padding: 12px; background: none; border: none; color: var(--text-secondary); font-size: 14px; font-weight: 500; cursor: pointer; border-radius: 10px; transition: all 0.2s; }
.tab.active { background: var(--bg-card); color: var(--primary); }

.tab-content { display: none; }
.tab-content.active { display: block; }

.color-grid { display: flex; gap: 12px; overflow-x: auto; padding-bottom: 8px; }
.color-item { display: flex; flex-direction: column; align-items: center; gap: 6px; cursor: pointer; min-width: 60px; }
.color-circle { width: 50px; height: 50px; border-radius: 12px; border: 2px solid transparent; transition: all 0.2s; }
.color-item.selected .color-circle { border-color: var(--primary); box-shadow: 0 0 0 2px var(--primary); }
.color-name { font-size: 11px; color: var(--text-secondary); text-align: center; }
.custom-color { position: relative; }
.custom-color input[type="color"] { position: absolute; width: 50px; height: 50px; opacity: 0; cursor: pointer; }
.custom-color .color-circle.custom { background: linear-gradient(135deg, #ff0000, #00ff00, #0000ff); }

.texture-grid { display: flex; gap: 12px; overflow-x: auto; padding-bottom: 8px; }
.texture-item { display: flex; flex-direction: column; align-items: center; gap: 6px; cursor: pointer; min-width: 70px; }
.texture-item img { width: 60px; height: 60px; border-radius: 12px; object-fit: cover; border: 2px solid transparent; transition: all 0.2s; }
.texture-item.selected img { border-color: var(--primary); box-shadow: 0 0 0 2px var(--primary); }
.texture-name { font-size: 11px; color: var(--text-secondary); text-align: center; }


.btn-generate { 
    width: 100%; 
    padding: 16px; 
    background: var(--primary); 
    color: #000; 
    border: none; 
    border-radius: 12px; 
    font-size: 16px; 
    font-weight: 600; 
    cursor: pointer; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    gap: 8px;
    flex-shrink: 0;
    margin-top: auto;
}
.btn-generate:disabled { opacity: 0.5; cursor: not-allowed; }

/* Loading */
.loading-container { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; text-align: center; background: linear-gradient(180deg, var(--bg-card) 0%, var(--bg-dark) 100%); }
.loading-animation { margin-bottom: 30px; }
.loading-circle { width: 120px; height: 120px; border-radius: 50%; background: radial-gradient(circle, rgba(0,217,255,0.2) 0%, transparent 70%); display: flex; align-items: center; justify-content: center; position: relative; animation: pulse 2s ease-in-out infinite; }
.loading-circle::before { content: ''; position: absolute; width: 100%; height: 100%; border-radius: 50%; border: 2px solid var(--primary); border-top-color: transparent; animation: spin 1s linear infinite; }
.loading-icon { animation: float 2s ease-in-out infinite; }
@keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
@keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
.loading-container h2 { font-size: 24px; font-weight: 600; margin-bottom: 12px; }
.loading-container > p { font-size: 14px; color: var(--text-secondary); margin-bottom: 30px; }
.btn-cancel { background: var(--bg-panel); border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px 24px; border-radius: 10px; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 8px; }

/* Result */
.result-container { min-height: 100vh; display: flex; flex-direction: column; padding-bottom: 20px; }
.result-header { display: flex; justify-content: space-between; align-items: center; padding: 16px; }
.result-header h1 { font-size: 18px; font-weight: 600; }

.result-image-container { position: relative; margin: 0 16px 16px; border-radius: 16px; overflow: hidden; }
.result-image-container img { width: 100%; max-height: 400px; object-fit: cover; }

.result-info { padding: 0 16px 16px; }
.result-title-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.result-title-row h3 { font-size: 20px; font-weight: 600; }
.ai-edit-btn { background: none; border: 1px solid var(--primary); color: var(--primary); padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px; }
.result-details { font-size: 13px; color: var(--primary); }

.result-tabs { display: flex; margin: 0 16px 16px; background: var(--bg-panel); border-radius: 10px; overflow: hidden; }
.result-tab { flex: 1; padding: 12px; background: none; border: none; color: var(--text-secondary); font-size: 14px; cursor: pointer; }
.result-tab.active { background: var(--bg-card); color: var(--text-primary); }


.result-actions { padding: 0 16px; display: flex; flex-direction: column; gap: 12px; margin-top: auto; }
.btn-save { width: 100%; padding: 16px; background: var(--primary); color: #000; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }
.btn-share { width: 100%; padding: 16px; background: transparent; color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }

</style>

<script src="https://telegram.org/js/telegram-web-app.js"></script>
<script>
var APP = {
    userId: null,
    currentRequestId: null,
    selectedPhoto: null,
    selectedPhotoDataUrl: null,
    selectedTextureId: null,
    selectedColor: null,
    originalImageUrl: null,
    resultImageUrl: null,
    pollingTimer: null,
    statusUrl: '<?= Html::encode(Url::to(['/telegram/webapp/status'])) ?>',
    historyUrl: '<?= Html::encode(Url::to(['/telegram/webapp/history'])) ?>',
    uploadUrl: '<?= Html::encode(Url::to(['/telegram/webapp/upload'])) ?>',
    csrfToken: '<?= Html::encode(Yii::$app->request->csrfToken) ?>'
};

function goToPage(pageId) {
    document.querySelectorAll('.page').forEach(function(p) {
        p.classList.remove('active');
    });
    var page = document.getElementById('page-' + pageId);
    if (page) page.classList.add('active');
    
    if (pageId === 'home') {
        loadProjects();
    }
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

function capturePhoto() {
    var input = document.getElementById('cameraInput');
    if (input) input.click();
}

function selectFromGallery() {
    var input = document.getElementById('photoInput');
    if (input) input.click();
}

function handlePhotoSelected(file) {
    if (!file) return;
    
    var maxBytes = 5 * 1024 * 1024;
    if (file.size > maxBytes) {
        alert('Rasm hajmi 5MB dan oshmasligi kerak');
        return;
    }
    
    APP.selectedPhoto = file;
    
    var reader = new FileReader();
    reader.onload = function(e) {
        APP.selectedPhotoDataUrl = e.target.result;
        APP.originalImageUrl = e.target.result;
        
        var previewImg = document.getElementById('editorPreviewImg');
        if (previewImg) previewImg.src = e.target.result;
        
        goToPage('editor');
    };
    reader.readAsDataURL(file);
}

function showAllProjects() {
    alert('Galereya sahifasi tez orada qo\'shiladi');
}

async function loadProjects() {
    if (!APP.userId) return;
    
    try {
        var res = await fetch(APP.historyUrl + '?user_id=' + APP.userId);
        var data = await res.json();
        
        if (data.ok && data.items && data.items.length > 0) {
            var grid = document.getElementById('projectsGrid');
            if (!grid) return;
            
            grid.innerHTML = '';
            
            data.items.slice(0, 4).forEach(function(item) {
                var card = document.createElement('div');
                card.className = 'project-card';
                card.onclick = function() { openProject(item); };
                
                var imgSrc = item.output_url || item.input_url || '';
                var badgeClass = item.status === 'completed' ? 'edited' : 'raw';
                var badgeText = item.status === 'completed' ? 'Edited' : 'Raw';
                var title = item.texture_title || 'Loyiha #' + item.id;
                var timeAgo = formatTimeAgo(item.created_at);
                
                card.innerHTML = '<img src="' + imgSrc + '" alt="">' +
                    '<span class="badge ' + badgeClass + '">' + badgeText + '</span>' +
                    '<div class="info"><h4>' + title + '</h4><span>' + timeAgo + '</span></div>';
                
                grid.appendChild(card);
            });
        }
    } catch (e) {
        console.error('Failed to load projects:', e);
    }
}

function formatTimeAgo(dateStr) {
    if (!dateStr) return '';
    var date = new Date(dateStr);
    var now = new Date();
    var diff = Math.floor((now - date) / 1000);
    
    if (diff < 60) return 'Hozirgina';
    if (diff < 3600) return Math.floor(diff / 60) + ' daqiqa oldin';
    if (diff < 86400) return Math.floor(diff / 3600) + ' soat oldin';
    if (diff < 604800) return Math.floor(diff / 86400) + ' kun oldin';
    return 'Kecha';
}

function openProject(item) {
    if (item.status === 'completed' && item.output_url) {
        APP.resultImageUrl = item.output_url;
        APP.originalImageUrl = item.input_url;
        
        var resultImg = document.getElementById('resultImage');
        if (resultImg) resultImg.src = item.output_url;
        
        var details = document.getElementById('resultDetails');
        if (details) {
            var text = '';
            if (item.color_hex) text += 'Rang: ' + item.color_hex;
            if (item.texture_title) text += (text ? ' • ' : '') + 'Tekstura: ' + item.texture_title;
            details.textContent = text || 'AI bilan tahrirlangan';
        }
        
        goToPage('result');
    }
}

async function generateImage() {
    if (!APP.userId) {
        alert('Telegram orqali oching');
        return;
    }
    
    if (!APP.selectedPhoto) {
        alert('Iltimos, avval rasm tanlang');
        return;
    }
    
    if (!APP.selectedTextureId && !APP.selectedColor) {
        alert('Iltimos, rang yoki tekstura tanlang');
        return;
    }
    
    var generateBtn = document.getElementById('generateBtn');
    if (generateBtn) generateBtn.disabled = true;
    
    goToPage('loading');
    
    try {
        var formData = new FormData();
        formData.append('_csrf', APP.csrfToken);
        formData.append('user_id', APP.userId);
        formData.append('photo', APP.selectedPhoto);
        if (APP.selectedTextureId) formData.append('texture_id', APP.selectedTextureId);
        if (APP.selectedColor) formData.append('color', APP.selectedColor);
        
        var res = await fetch(APP.uploadUrl, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        var data = await res.json();
        
        if (!res.ok || !data || data.ok !== true) {
            throw new Error(data && data.message ? data.message : 'Upload failed');
        }
        
        APP.currentRequestId = data.request_id;
        startPolling();
        
    } catch (err) {
        alert('Xatolik: ' + (err.message || 'Noma\'lum xato'));
        goToPage('editor');
    } finally {
        if (generateBtn) generateBtn.disabled = false;
    }
}

function startPolling() {
    if (APP.pollingTimer) clearInterval(APP.pollingTimer);
    
    APP.pollingTimer = setInterval(async function() {
        try {
            var res = await fetch(APP.statusUrl + '?id=' + APP.currentRequestId);
            var data = await res.json();
            
            if (data.ok) {
                if (data.status === 'completed' && data.output_url) {
                    clearInterval(APP.pollingTimer);
                    APP.pollingTimer = null;
                    
                    APP.resultImageUrl = data.output_url;
                    
                    var resultImg = document.getElementById('resultImage');
                    if (resultImg) resultImg.src = data.output_url;
                    
                    var details = document.getElementById('resultDetails');
                    if (details) {
                        var text = '';
                        if (APP.selectedColor) text += 'Rang: ' + APP.selectedColor;
                        details.textContent = text || 'AI bilan tahrirlangan';
                    }
                    
                    goToPage('result');
                    
                } else if (data.status === 'failed') {
                    clearInterval(APP.pollingTimer);
                    APP.pollingTimer = null;
                    alert('Xatolik yuz berdi. Qaytadan urinib ko\'ring.');
                    goToPage('editor');
                }
            }
        } catch (e) {
            console.error('Polling error:', e);
        }
    }, 2000);
}

function cancelGeneration() {
    if (APP.pollingTimer) {
        clearInterval(APP.pollingTimer);
        APP.pollingTimer = null;
    }
    goToPage('editor');
}

function showOriginal() {
    var resultImg = document.getElementById('resultImage');
    if (resultImg && APP.originalImageUrl) {
        resultImg.src = APP.originalImageUrl;
    }
    document.querySelectorAll('.result-tab').forEach(function(t) { t.classList.remove('active'); });
    document.querySelector('.result-tab:first-child').classList.add('active');
}

function showResult() {
    var resultImg = document.getElementById('resultImage');
    if (resultImg && APP.resultImageUrl) {
        resultImg.src = APP.resultImageUrl;
    }
    document.querySelectorAll('.result-tab').forEach(function(t) { t.classList.remove('active'); });
    document.querySelector('.result-tab:last-child').classList.add('active');
}

async function saveToGallery() {
    if (!APP.resultImageUrl) return;
    
    try {
        var response = await fetch(APP.resultImageUrl);
        var blob = await response.blob();
        
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'ai-wall-result-' + Date.now() + '.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(link.href);
        
        alert('Rasm saqlandi!');
    } catch (e) {
        window.open(APP.resultImageUrl, '_blank');
    }
}

function shareResult() {
    if (navigator.share && APP.resultImageUrl) {
        navigator.share({
            title: 'AI Wall Editor natijasi',
            url: APP.resultImageUrl
        }).catch(function() {});
    } else if (APP.resultImageUrl) {
        navigator.clipboard.writeText(APP.resultImageUrl).then(function() {
            alert('Havola nusxalandi!');
        }).catch(function() {
            window.open(APP.resultImageUrl, '_blank');
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Init Telegram WebApp
    if (window.Telegram && Telegram.WebApp) {
        Telegram.WebApp.ready();
        Telegram.WebApp.expand();
    }
    
    // Get user ID
    var attempts = 40;
    var timer = setInterval(function() {
        var id = getTelegramUserId();
        if (id) {
            clearInterval(timer);
            APP.userId = id;
            document.getElementById('userId').value = id;
            loadProjects();
            return;
        }
        if (--attempts <= 0) {
            clearInterval(timer);
        }
    }, 250);
    
    // Photo inputs
    var photoInput = document.getElementById('photoInput');
    var cameraInput = document.getElementById('cameraInput');
    
    if (photoInput) {
        photoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) handlePhotoSelected(this.files[0]);
        });
    }
    if (cameraInput) {
        cameraInput.addEventListener('change', function() {
            if (this.files && this.files[0]) handlePhotoSelected(this.files[0]);
        });
    }
    
    // Tabs
    document.querySelectorAll('.tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            var tabId = this.getAttribute('data-tab');
            
            document.querySelectorAll('.tab').forEach(function(t) { t.classList.remove('active'); });
            document.querySelectorAll('.tab-content').forEach(function(c) { c.classList.remove('active'); });
            
            this.classList.add('active');
            var content = document.getElementById('tab-' + tabId);
            if (content) content.classList.add('active');
        });
    });
    
    // Color selection
    document.querySelectorAll('.color-item:not(.custom-color)').forEach(function(item) {
        item.addEventListener('click', function() {
            var color = this.getAttribute('data-color');
            APP.selectedColor = color;
            APP.selectedTextureId = null;
            
            document.querySelectorAll('.color-item').forEach(function(c) { c.classList.remove('selected'); });
            document.querySelectorAll('.texture-item').forEach(function(t) { t.classList.remove('selected'); });
            this.classList.add('selected');
        });
    });
    
    // Custom color picker
    var customPicker = document.getElementById('customColorPicker');
    var customCircle = document.getElementById('customColorCircle');
    if (customPicker) {
        customPicker.addEventListener('input', function() {
            APP.selectedColor = this.value;
            APP.selectedTextureId = null;
            if (customCircle) customCircle.style.background = this.value;
            
            document.querySelectorAll('.color-item').forEach(function(c) { c.classList.remove('selected'); });
            document.querySelectorAll('.texture-item').forEach(function(t) { t.classList.remove('selected'); });
            customPicker.parentElement.classList.add('selected');
        });
    }
    
    // Texture selection
    document.querySelectorAll('.texture-item').forEach(function(item) {
        item.addEventListener('click', function() {
            var textureId = this.getAttribute('data-texture-id');
            APP.selectedTextureId = textureId;
            APP.selectedColor = null;
            
            document.querySelectorAll('.texture-item').forEach(function(t) { t.classList.remove('selected'); });
            document.querySelectorAll('.color-item').forEach(function(c) { c.classList.remove('selected'); });
            this.classList.add('selected');
        });
    });
    
    // Slide dots
    document.querySelectorAll('.dot').forEach(function(dot) {
        dot.addEventListener('click', function() {
            var slideIndex = parseInt(this.getAttribute('data-slide'));
            
            document.querySelectorAll('.slide').forEach(function(s, i) {
                s.classList.toggle('active', i === slideIndex);
            });
            document.querySelectorAll('.dot').forEach(function(d, i) {
                d.classList.toggle('active', i === slideIndex);
            });
        });
    });
    
    // Check if onboarding was shown
    var onboardingShown = localStorage.getItem('onboarding_shown');
    if (onboardingShown) {
        goToPage('home');
    } else {
        localStorage.setItem('onboarding_shown', '1');
    }
});
</script>
