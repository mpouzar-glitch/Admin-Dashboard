<?php
require_once 'auth.php';
checkAuth();

require_once 'config.php';
$settings = getSettings();

$currentPageId = $_GET['page'] ?? 1;
$lang = getCurrentLanguage();
$jsTranslations = getJsTranslations([
    'dashboard.page_badge',
]);
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(t('app.name')); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        
        .btn-admin {
            background: #e67e22;
            color: white;
        }
        
        .btn-admin:hover {
            background: #d35400;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(230, 126, 34, 0.4);
        }
        
.grid-container {
    display: grid;
    gap: var(--grid-gap);
    padding: 10px;
}

/* Desktop - použít dynamické sloupce podle nastavení */
@media (min-width: 1025px) {
    .grid-container {
        grid-template-columns: repeat(auto-fill, minmax(var(--button-min-width), 1fr));
    }
}

/* Tablety a mobily na šířku - 3 sloupce */
@media (max-width: 1024px) and (min-width: 577px) {
    .grid-container {
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }
    
    .dashboard-card {
        padding: 20px;
        min-height: calc(var(--button-min-height) * 0.8);
    }
}

/* Menší mobily - 2 sloupce */
@media (max-width: 576px) and (min-width: 401px) {
    .grid-container {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    
    .dashboard-card {
        padding: 18px;
        min-height: 150px;
    }
    
    .card-icon {
        font-size: 2.2em;
    }
    
    .card-title {
        font-size: 1.1em;
    }
}

/* Velmi malé mobily - 1 sloupec */
@media (max-width: 400px) {
    .grid-container {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .dashboard-card {
        padding: 20px;
        min-height: 120px;
    }
}

/* Mobily v krajině - 4 sloupce */
@media (max-height: 600px) and (orientation: landscape) {
    .grid-container {
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }
    
    .dashboard-card {
        padding: 12px;
        min-height: 100px;
    }
    
    .card-icon {
        font-size: 1.8em;
        margin-bottom: 8px;
    }
    
    .card-title {
        font-size: 0.9em;
        margin-bottom: 5px;
    }
    
    .card-description {
        font-size: 0.75em;
    }
}
        
        .dashboard-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: var(--button-min-height);
            text-decoration: none;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        
        .dashboard-card.separator {
            background: transparent;
            box-shadow: none;
            cursor: default;
            pointer-events: none;
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
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-top">
                <a href="index.php">
                    <img src="logo.png" width="200"/>
                </a>
                <a href="admin.php?page=<?php echo $currentPageId; ?>" class="btn btn-admin">
                    <i class="fas fa-cog"></i> <?php echo htmlspecialchars(t('dashboard.admin_button')); ?>
                </a>
            </div>
            <div class="breadcrumb" id="breadcrumb">
                <!-- Breadcrumb will be loaded here -->
            </div>
        </header>
        
        <div class="grid-container" id="dashboard-grid">
            <!-- Buttons will be loaded here -->
        </div>
    </div>
    
    <script>
        const currentPageId = <?php echo $currentPageId; ?>;
        const i18n = <?php echo json_encode($jsTranslations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        
        // Load breadcrumb
        function loadBreadcrumb() {
            fetch(`api.php?action=get_breadcrumb&page_id=${currentPageId}`)
                .then(response => response.json())
                .then(breadcrumb => {
                    const breadcrumbDiv = document.getElementById('breadcrumb');
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
                });
        }
        
        // Load and display buttons
        function loadButtons() {
            fetch(`api.php?action=get_all&page_id=${currentPageId}`)
                .then(response => response.json())
                .then(buttons => {
                    const grid = document.getElementById('dashboard-grid');
                    grid.innerHTML = '';
                    
                    buttons.forEach(button => {
                        if (button.button_type === 'separator') {
                            const separator = document.createElement('div');
                            separator.className = 'dashboard-card separator';
                            grid.appendChild(separator);
                        } else if (button.button_type === 'page_link') {
                            const card = document.createElement('a');
                            card.href = `?page=${button.target_page_id}`;
                            card.className = 'dashboard-card page-link';
                            card.style.backgroundColor = button.background_color;
                            card.innerHTML = `
                                <span class="page-badge"><i class="fas fa-layer-group"></i> ${i18n['dashboard.page_badge']}</span>
                                <i class="fas ${button.icon} card-icon" style="color: ${button.color}"></i>
                                <div class="card-title">${button.title}</div>
                                <div class="card-description">${button.description || ''}</div>
                            `;
                            grid.appendChild(card);
                        } else {
                            const card = document.createElement('a');
                            card.href = button.url;
                            card.target = '_blank';
                            card.className = 'dashboard-card';
                            card.style.backgroundColor = button.background_color;
                            card.innerHTML = `
                                <i class="fas ${button.icon} card-icon" style="color: ${button.color}"></i>
                                <div class="card-title">${button.title}</div>
                                <div class="card-description">${button.description || ''}</div>
                            `;
                            grid.appendChild(card);
                        }
                    });
                })
                .catch(error => console.error('Error loading buttons:', error));
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            loadBreadcrumb();
            loadButtons();
        });
    </script>
</body>
</html>
