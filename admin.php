<?php
require_once 'auth.php';
checkAuth();

$currentUser = getCurrentUser();
require_once 'config.php';
$settings = getSettings();

$currentPageId = $_GET['page'] ?? 1;
$lang = getCurrentLanguage();
$jsTranslations = getJsTranslations([
    'admin.select_page',
    'label.current_suffix',
    'admin.separator_label',
    'admin.edit_move',
    'admin.edit',
    'label.page',
    'admin.modal.add_title',
    'admin.modal.edit_title',
    'admin.button_moved',
    'admin.settings_saved',
    'admin.subpage_created',
    'admin.confirm_delete_button',
]);
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(t('admin.title')); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <style>
        :root {
            --button-min-width: <?php echo $settings['button_min_width']; ?>px;
            --button-min-height: <?php echo $settings['button_min_height']; ?>px;
            --icon-size: <?php echo $settings['icon_size']; ?>em;
            --title-size: <?php echo $settings['title_size']; ?>em;
            --grid-gap: <?php echo $settings['grid_gap']; ?>px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        header {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        header h1 {
            color: #2c3e50;
            font-size: 2em;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            padding: 10px 0;
            font-size: 0.95em;
            border-top: 1px solid #e0e0e0;
            margin-top: 15px;
        }
        
        .breadcrumb a {
            color: #667eea;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s;
        }
        
        .breadcrumb a:hover {
            color: #5568d3;
            text-decoration: underline;
        }
        
        .breadcrumb .separator {
            color: #7f8c8d;
        }
        
        .breadcrumb .current {
            color: #2c3e50;
            font-weight: bold;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-settings {
            background: #27ae60;
            color: white;
        }
        
        .btn-settings:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.4);
        }
        
        .btn-back {
            background: #95a5a6;
            color: white;
        }
        
        .btn-back:hover {
            background: #7f8c8d;
        }
        
        .header-actions a:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(var(--button-min-width), 1fr));
            gap: var(--grid-gap);
            padding: 10px;
        }
        
        @media (max-width: 992px) {
            header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .header-actions {
                justify-content: center;
            }
        }
        
        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
            .header-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .header-actions .btn,
            .header-actions a,
            .header-actions span {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .grid-container {
                grid-template-columns: 1fr;
            }
            
            header h1 {
                font-size: 1.5em;
            }
        }
        
        .dashboard-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            cursor: grab;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: var(--button-min-height);
        }
        
        .dashboard-card:active {
            cursor: grabbing;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        
        .dashboard-card.separator {
            background: transparent;
            border: 2px dashed #bdc3c7;
            box-shadow: none;
            min-height: 100px;
        }
        
        .dashboard-card.page-link {
            border: 3px dashed #667eea;
        }
        
        .card-icon {
            font-size: var(--icon-size);
            margin-bottom: 15px;
            display: block;
        }
        
        .card-title {
            font-size: var(--title-size);
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .card-description {
            font-size: 0.9em;
            color: #7f8c8d;
            margin-bottom: 15px;
        }
        
        .card-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: auto;
            padding-top: 15px;
        }
        
        .btn-small {
            padding: 8px 12px;
            font-size: 14px;
            border-radius: 6px;
        }
        
        .btn-edit {
            background: #3498db;
            color: white;
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        
        .page-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #667eea;
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .ui-sortable-helper {
            opacity: 0.8;
            transform: rotate(3deg);
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
            animation: fadeIn 0.3s;
            overflow-y: auto;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            animation: slideIn 0.3s;
        }
        
        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 20px;
        }
        
        .close:hover {
            color: #000;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-group small {
            display: block;
            margin-top: 5px;
            font-size: 0.85em;
            color: #7f8c8d;
        }
        
        .color-picker-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .color-picker-wrapper input[type="color"] {
            width: 60px;
            height: 45px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .icon-preview {
            font-size: 2em;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            display: inline-block;
            min-width: 60px;
            text-align: center;
        }
        
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .info-box {
            background: #e3f2fd;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 0.9em;
            color: #1565c0;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-top">
                <h1><i class="fas fa-tools"></i> <?php echo htmlspecialchars(t('admin.header')); ?></h1>
                <div class="header-actions">
                    <?php if ($currentUser): ?>
                        <span style="color: #2c3e50; padding: 12px; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-user-circle"></i> 
                            <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong>
                        </span>
                    <?php endif; ?>
                    
                    <a href="user_manager.php" class="btn" style="background: #9b59b6; color: white;">
                        <i class="fas fa-users-cog"></i> <?php echo htmlspecialchars(t('nav.users')); ?>
                    </a>
                    
                    <a href="ip_manager.php" class="btn" style="background: #16a085; color: white;">
                        <i class="fas fa-network-wired"></i> <?php echo htmlspecialchars(t('nav.ip_ranges')); ?>
                    </a>
                    
                    <button class="btn" style="background: #f39c12; color: white;" onclick="openPageModal()">
                        <i class="fas fa-folder-plus"></i> <?php echo htmlspecialchars(t('admin.new_subpage')); ?>
                    </button>
                    
                    <button class="btn btn-settings" onclick="openSettingsModal()">
                        <i class="fas fa-sliders-h"></i> <?php echo htmlspecialchars(t('admin.appearance')); ?>
                    </button>
                    
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> <?php echo htmlspecialchars(t('admin.add_button')); ?>
                    </button>
                    
                    <a href="index.php?page=<?php echo $currentPageId; ?>" class="btn btn-back">
                        <i class="fas fa-eye"></i> <?php echo htmlspecialchars(t('nav.view')); ?>
                    </a>
                    
                    <?php if (isLoggedIn()): ?>
                        <a href="logout.php" class="btn" style="background: #e74c3c; color: white;">
                            <i class="fas fa-sign-out-alt"></i> <?php echo htmlspecialchars(t('nav.logout')); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="breadcrumb" id="breadcrumb">
                <!-- Breadcrumb will be loaded here -->
            </div>
        </header>
        
        <div class="grid-container" id="dashboard-grid">
            <!-- Buttons will be loaded here via JavaScript -->
        </div>
    </div>
    
    <!-- Modal for Add/Edit Button -->
    <div id="buttonModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('buttonModal')">&times;</span>
            <h2 id="modalTitle"><?php echo htmlspecialchars(t('admin.modal.add_title')); ?></h2>
            <form id="buttonForm" onsubmit="saveButton(event)" novalidate>
                <input type="hidden" id="buttonId" name="buttonId">

                <div class="form-group">
                    <label for="buttonType"><?php echo htmlspecialchars(t('admin.button_type')); ?></label>
                    <select id="buttonType" name="buttonType">
                        <option value="external_url"><?php echo htmlspecialchars(t('admin.type.external')); ?></option>
                        <option value="page_link"><?php echo htmlspecialchars(t('admin.type.page_link')); ?></option>
                        <option value="separator"><?php echo htmlspecialchars(t('admin.type.separator')); ?></option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="buttonParentPage"><?php echo htmlspecialchars(t('admin.parent_page')); ?></label>
                    <select id="buttonParentPage" name="buttonParentPage">
                        <option value=""><?php echo htmlspecialchars(t('admin.select_page')); ?></option>
                    </select>
                    <small>
                        <i class="fas fa-info-circle"></i> 
                        <?php echo htmlspecialchars(t('admin.select_page_help')); ?>
                    </small>
                </div>

                <div class="form-group" id="titleGroup">
                    <label for="buttonTitle"><?php echo htmlspecialchars(t('admin.title_label')); ?></label>
                    <input type="text" id="buttonTitle" name="buttonTitle">
                </div>

                <div class="form-group" id="urlGroup">
                    <label for="buttonUrl"><?php echo htmlspecialchars(t('admin.url_label')); ?></label>
                    <input type="url" id="buttonUrl" name="buttonUrl">
                </div>

                <div class="form-group" id="targetPageGroup" style="display: none;">
                    <label for="buttonTargetPage"><?php echo htmlspecialchars(t('admin.target_page')); ?></label>
                    <select id="buttonTargetPage" name="buttonTargetPage">
                        <option value=""><?php echo htmlspecialchars(t('admin.select_page')); ?></option>
                    </select>
                </div>

                <div class="form-group" id="descGroup">
                    <label for="buttonDescription"><?php echo htmlspecialchars(t('admin.description')); ?></label>
                    <textarea id="buttonDescription" name="buttonDescription"></textarea>
                </div>

                <div class="form-group" id="bgColorGroup">
                    <label for="buttonBgColor"><?php echo htmlspecialchars(t('admin.bg_color')); ?></label>
                    <div class="color-picker-wrapper">
                        <input type="color" id="buttonBgColor" name="buttonBgColor" value="#ffffff">
                        <input type="text" id="buttonBgColorText" value="#ffffff" 
                               oninput="document.getElementById('buttonBgColor').value = this.value">
                    </div>
                </div>

                <div class="form-group" id="colorGroup">
                    <label for="buttonColor"><?php echo htmlspecialchars(t('admin.icon_color')); ?></label>
                    <div class="color-picker-wrapper">
                        <input type="color" id="buttonColor" name="buttonColor" value="#3498db">
                        <input type="text" id="buttonColorText" value="#3498db" 
                               oninput="document.getElementById('buttonColor').value = this.value">
                    </div>
                </div>

                <div class="form-group" id="iconGroup">
                    <label for="buttonIcon"><?php echo htmlspecialchars(t('admin.icon_label')); ?></label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" id="buttonIcon" name="buttonIcon" placeholder="fa-link" 
                               oninput="updateIconPreview()">
                        <span class="icon-preview" id="iconPreview">
                            <i class="fas fa-link"></i>
                        </span>
                    </div>
                    <small><?php echo htmlspecialchars(t('admin.icon_help')); ?></small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo htmlspecialchars(t('admin.save')); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for Settings -->
    <div id="settingsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('settingsModal')">&times;</span>
            <h2><i class="fas fa-sliders-h"></i> <?php echo htmlspecialchars(t('admin.settings_title')); ?></h2>
            <form id="settingsForm" onsubmit="saveSettings(event)" novalidate>
                <div class="settings-grid">
                    <div class="form-group">
                        <label for="buttonMinWidth"><?php echo htmlspecialchars(t('admin.min_width')); ?></label>
                        <input type="number" id="buttonMinWidth" name="buttonMinWidth" min="150" max="500" 
                               value="<?php echo $settings['button_min_width']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="buttonMinHeight"><?php echo htmlspecialchars(t('admin.min_height')); ?></label>
                        <input type="number" id="buttonMinHeight" name="buttonMinHeight" min="100" max="400" 
                               value="<?php echo $settings['button_min_height']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="iconSize"><?php echo htmlspecialchars(t('admin.icon_size')); ?></label>
                        <input type="number" id="iconSize" name="iconSize" min="1" max="6" step="0.1" 
                               value="<?php echo $settings['icon_size']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="titleSize"><?php echo htmlspecialchars(t('admin.title_size')); ?></label>
                        <input type="number" id="titleSize" name="titleSize" min="0.8" max="2.5" step="0.1" 
                               value="<?php echo $settings['title_size']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="gridGap"><?php echo htmlspecialchars(t('admin.grid_gap')); ?></label>
                        <input type="number" id="gridGap" name="gridGap" min="5" max="50" 
                               value="<?php echo $settings['grid_gap']; ?>">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo htmlspecialchars(t('admin.save_settings')); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for New Page -->
    <div id="pageModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('pageModal')">&times;</span>
            <h2><i class="fas fa-folder-plus"></i> <?php echo htmlspecialchars(t('admin.new_page_title')); ?></h2>
            <div class="info-box">
                <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars(t('admin.new_page_info')); ?>
            </div>
            <form id="pageForm" onsubmit="savePage(event)" novalidate>
                <div class="form-group">
                    <label for="pageName"><?php echo htmlspecialchars(t('admin.page_name')); ?></label>
                    <input type="text" id="pageName" name="pageName" placeholder="<?php echo htmlspecialchars(t('admin.page_placeholder')); ?>">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo htmlspecialchars(t('admin.create_page')); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        let buttons = [];
        let pages = [];
        const currentPageId = <?php echo $currentPageId; ?>;
        const i18n = <?php echo json_encode($jsTranslations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        
        // Load pages for dropdown
        function loadPages() {
            fetch('api.php?action=get_pages')
                .then(response => response.json())
                .then(data => {
                    pages = data;
                    updatePageDropdowns();
                })
                .catch(error => console.error('Error loading pages:', error));
        }
        
        function updatePageDropdowns() {
            // Update target page dropdown (pro page_link typ)
            const targetPageSelect = document.getElementById('buttonTargetPage');
            if (targetPageSelect) {
                targetPageSelect.innerHTML = `<option value="">${i18n['admin.select_page']}</option>`;
                pages.forEach(page => {
                    const option = document.createElement('option');
                    option.value = page.id;
                    option.textContent = page.page_name;
                    targetPageSelect.appendChild(option);
                });
            }
            
            // Update parent page dropdown (umístění tlačítka)
            const parentPageSelect = document.getElementById('buttonParentPage');
            if (parentPageSelect) {
                parentPageSelect.innerHTML = `<option value="">${i18n['admin.select_page']}</option>`;
                pages.forEach(page => {
                    const option = document.createElement('option');
                    option.value = page.id;
                    option.textContent = page.page_name;
                    // Označit aktuální stránku
                    if (page.id == currentPageId) {
                        option.textContent += ` ${i18n['label.current_suffix']}`;
                        option.selected = true;
                    }
                    parentPageSelect.appendChild(option);
                });
            }
        }
        
        // Load breadcrumb
        function loadBreadcrumb() {
            fetch(`api.php?action=get_breadcrumb&page_id=${currentPageId}`)
                .then(response => response.json())
                .then(breadcrumb => {
                    const breadcrumbDiv = document.getElementById('breadcrumb');
                    if (!breadcrumbDiv) return;
                    
                    breadcrumbDiv.innerHTML = '';
                    
                    breadcrumb.forEach((page, index) => {
                        if (index > 0) {
                            const separator = document.createElement('span');
                            separator.className = 'separator';
                            separator.innerHTML = '<i class="fas fa-chevron-right"></i>';
                            breadcrumbDiv.appendChild(separator);
                        }
                        
                        if (index === breadcrumb.length - 1) {
                            const current = document.createElement('span');
                            current.className = 'current';
                            current.textContent = page.page_name;
                            breadcrumbDiv.appendChild(current);
                        } else {
                            const link = document.createElement('a');
                            link.href = `?page=${page.id}`;
                            link.innerHTML = `<i class="fas fa-home"></i> ${page.page_name}`;
                            breadcrumbDiv.appendChild(link);
                        }
                    });
                })
                .catch(error => console.error('Error loading breadcrumb:', error));
        }
        
        function loadButtons() {
            fetch(`api.php?action=get_all&page_id=${currentPageId}`)
                .then(response => response.json())
                .then(data => {
                    buttons = data;
                    renderButtons();
                    initSortable();
                })
                .catch(error => console.error('Error loading buttons:', error));
        }
        
        function renderButtons() {
            const grid = document.getElementById('dashboard-grid');
            grid.innerHTML = '';
            
            buttons.forEach(button => {
                const card = document.createElement('div');
                card.className = 'dashboard-card';
                card.setAttribute('data-id', button.id);
                
                if (button.button_type === 'separator') {
                    card.classList.add('separator');
                    card.style.backgroundColor = 'transparent';
                    card.innerHTML = `
                        <div style="color: #95a5a6; font-style: italic;">
                            <i class="fas fa-minus"></i> ${i18n['admin.separator_label']}
                        </div>
                        <div class="card-actions">
                            <button class="btn btn-small btn-edit" onclick="event.stopPropagation(); openEditModal(${button.id})" title="${i18n['admin.edit_move']}">
                                <i class="fas fa-arrows-alt"></i>
                            </button>
                            <button class="btn btn-small btn-delete" onclick="event.stopPropagation(); deleteButton(${button.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                } else {
                    card.style.backgroundColor = button.background_color;
                    
                    let badge = '';
                    if (button.button_type === 'page_link') {
                        card.classList.add('page-link');
                        badge = `<span class="page-badge"><i class="fas fa-layer-group"></i> ${i18n['label.page']}</span>`;
                    }
                    
                    card.innerHTML = `
                        <div>
                            ${badge}
                            <i class="fas ${button.icon} card-icon" style="color: ${button.color}"></i>
                            <div class="card-title">${button.title}</div>
                            <div class="card-description">${button.description || ''}</div>
                        </div>
                        <div class="card-actions">
                            <button class="btn btn-small btn-edit" onclick="event.stopPropagation(); openEditModal(${button.id})" title="${i18n['admin.edit_move']}">
                                <i class="fas fa-edit"></i> ${i18n['admin.edit']}
                            </button>
                            <button class="btn btn-small btn-delete" onclick="event.stopPropagation(); deleteButton(${button.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                    
                    if (button.button_type === 'external_url') {
                        card.addEventListener('click', (e) => {
                            if (!e.target.closest('button')) {
                                window.open(button.url, '_blank');
                            }
                        });
                    } else if (button.button_type === 'page_link') {
                        card.addEventListener('click', (e) => {
                            if (!e.target.closest('button')) {
                                window.location.href = `?page=${button.target_page_id}`;
                            }
                        });
                    }
                }
                
                grid.appendChild(card);
            });
        }
        
        function initSortable() {
            $('#dashboard-grid').sortable({
                tolerance: 'pointer',
                cursor: 'grabbing',
                update: function(event, ui) {
                    const order = $(this).sortable('toArray', { attribute: 'data-id' });
                    updateOrder(order);
                }
            });
        }
        
        function updateOrder(order) {
            fetch('api.php?action=update_order', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(order)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Order updated successfully');
                }
            })
            .catch(error => console.error('Error updating order:', error));
        }
        
        function openAddModal() {
            document.getElementById('modalTitle').textContent = i18n['admin.modal.add_title'];
            document.getElementById('buttonForm').reset();
            document.getElementById('buttonId').value = '';
            document.getElementById('buttonColor').value = '#3498db';
            document.getElementById('buttonColorText').value = '#3498db';
            document.getElementById('buttonBgColor').value = '#ffffff';
            document.getElementById('buttonBgColorText').value = '#ffffff';
            document.getElementById('buttonType').value = 'external_url';
            
            // Nastavit aktuální stránku jako výchozí
            const parentPageSelect = document.getElementById('buttonParentPage');
            if (parentPageSelect) {
                parentPageSelect.value = currentPageId;
            }
            
            toggleButtonTypeFields();
            document.getElementById('buttonModal').style.display = 'block';
            updateIconPreview();
        }
        
        function openEditModal(id) {
            const button = buttons.find(b => b.id == id);
            if (!button) return;
            
            document.getElementById('modalTitle').textContent = i18n['admin.modal.edit_title'];
            document.getElementById('buttonId').value = button.id;
            document.getElementById('buttonTitle').value = button.title;
            document.getElementById('buttonUrl').value = button.url;
            document.getElementById('buttonDescription').value = button.description || '';
            document.getElementById('buttonColor').value = button.color;
            document.getElementById('buttonColorText').value = button.color;
            document.getElementById('buttonBgColor').value = button.background_color;
            document.getElementById('buttonBgColorText').value = button.background_color;
            document.getElementById('buttonIcon').value = button.icon;
            document.getElementById('buttonType').value = button.button_type;
            document.getElementById('buttonTargetPage').value = button.target_page_id || '';
            document.getElementById('buttonParentPage').value = button.parent_page_id || currentPageId;
            
            toggleButtonTypeFields();
            document.getElementById('buttonModal').style.display = 'block';
            updateIconPreview();
        }
        
        function toggleButtonTypeFields() {
            const buttonType = document.getElementById('buttonType').value;
            const urlGroup = document.getElementById('urlGroup');
            const targetPageGroup = document.getElementById('targetPageGroup');
            const titleGroup = document.getElementById('titleGroup');
            const descGroup = document.getElementById('descGroup');
            const iconGroup = document.getElementById('iconGroup');
            const colorGroup = document.getElementById('colorGroup');
            const bgColorGroup = document.getElementById('bgColorGroup');
            
            if (buttonType === 'separator') {
                urlGroup.style.display = 'none';
                targetPageGroup.style.display = 'none';
                titleGroup.style.display = 'none';
                descGroup.style.display = 'none';
                iconGroup.style.display = 'none';
                colorGroup.style.display = 'none';
                bgColorGroup.style.display = 'none';
                
                document.getElementById('buttonTitle').removeAttribute('required');
                document.getElementById('buttonUrl').removeAttribute('required');
            } else if (buttonType === 'page_link') {
                urlGroup.style.display = 'none';
                targetPageGroup.style.display = 'block';
                titleGroup.style.display = 'block';
                descGroup.style.display = 'block';
                iconGroup.style.display = 'block';
                colorGroup.style.display = 'block';
                bgColorGroup.style.display = 'block';
                
                document.getElementById('buttonTitle').setAttribute('required', 'required');
                document.getElementById('buttonUrl').removeAttribute('required');
            } else {
                urlGroup.style.display = 'block';
                targetPageGroup.style.display = 'none';
                titleGroup.style.display = 'block';
                descGroup.style.display = 'block';
                iconGroup.style.display = 'block';
                colorGroup.style.display = 'block';
                bgColorGroup.style.display = 'block';
                
                document.getElementById('buttonTitle').setAttribute('required', 'required');
                document.getElementById('buttonUrl').setAttribute('required', 'required');
            }
        }
        
        function openSettingsModal() {
            document.getElementById('settingsModal').style.display = 'block';
        }
        
        function openPageModal() {
            document.getElementById('pageForm').reset();
            document.getElementById('pageModal').style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function updateIconPreview() {
            const iconInput = document.getElementById('buttonIcon');
            const colorInput = document.getElementById('buttonColor');
            const preview = document.getElementById('iconPreview');
            
            if (iconInput && colorInput && preview) {
                const icon = iconInput.value || 'fa-link';
                const color = colorInput.value;
                preview.innerHTML = `<i class="fas ${icon}" style="color: ${color}"></i>`;
            }
        }
        
        // Event listeners
        const buttonColorInput = document.getElementById('buttonColor');
        const buttonColorTextInput = document.getElementById('buttonColorText');
        const buttonBgColorInput = document.getElementById('buttonBgColor');
        const buttonBgColorTextInput = document.getElementById('buttonBgColorText');
        const buttonTypeSelect = document.getElementById('buttonType');
        
        if (buttonColorInput) {
            buttonColorInput.addEventListener('input', function() {
                if (buttonColorTextInput) buttonColorTextInput.value = this.value;
                updateIconPreview();
            });
        }
        
        if (buttonBgColorInput) {
            buttonBgColorInput.addEventListener('input', function() {
                if (buttonBgColorTextInput) buttonBgColorTextInput.value = this.value;
            });
        }
        
        if (buttonTypeSelect) {
            buttonTypeSelect.addEventListener('change', toggleButtonTypeFields);
        }
        
        function saveButton(event) {
            event.preventDefault();
            
            const id = document.getElementById('buttonId').value;
            const buttonType = document.getElementById('buttonType').value;
            const selectedParentPage = document.getElementById('buttonParentPage').value;
            
            const data = {
                id: id || null,
                title: document.getElementById('buttonTitle').value,
                url: document.getElementById('buttonUrl').value,
                description: document.getElementById('buttonDescription').value,
                color: document.getElementById('buttonColor').value,
                background_color: document.getElementById('buttonBgColor').value,
                icon: document.getElementById('buttonIcon').value || 'fa-link',
                button_type: buttonType,
                parent_page_id: selectedParentPage || currentPageId,
                target_page_id: buttonType === 'page_link' ? document.getElementById('buttonTargetPage').value : null
            };
            
            const action = id ? 'update' : 'add';
            
            fetch(`api.php?action=${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    closeModal('buttonModal');
                    
                    // Pokud bylo tlačítko přesunuto na jinou stránku
                    if (selectedParentPage != currentPageId && id) {
                        alert(i18n['admin.button_moved']);
                    }
                    
                    loadButtons();
                }
            })
            .catch(error => console.error('Error saving button:', error));
        }
        
        function saveSettings(event) {
            event.preventDefault();
            
            const data = {
                button_min_width: document.getElementById('buttonMinWidth').value,
                button_min_height: document.getElementById('buttonMinHeight').value,
                icon_size: document.getElementById('iconSize').value,
                title_size: document.getElementById('titleSize').value,
                grid_gap: document.getElementById('gridGap').value
            };
            
            fetch('api.php?action=update_settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert(i18n['admin.settings_saved']);
                    location.reload();
                }
            })
            .catch(error => console.error('Error saving settings:', error));
        }
        
        function savePage(event) {
            event.preventDefault();
            
            const data = {
                page_name: document.getElementById('pageName').value,
                parent_page_id: currentPageId
            };
            
            fetch('api.php?action=add_page', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    closeModal('pageModal');
                    loadPages();
                    alert(i18n['admin.subpage_created'] + result.id);
                    document.getElementById('pageForm').reset();
                }
            })
            .catch(error => console.error('Error saving page:', error));
        }
        
        function deleteButton(id) {
            if (!confirm(i18n['admin.confirm_delete_button'])) return;
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            
            fetch('api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    loadButtons();
                }
            })
            .catch(error => console.error('Error deleting button:', error));
        }
        
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            loadPages();
            loadBreadcrumb();
            loadButtons();
        });
    </script>
</body>
</html>
