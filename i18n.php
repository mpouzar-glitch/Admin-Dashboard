<?php
function getSupportedLanguages() {
    return ['cs', 'en'];
}

function detectBrowserLanguage($supported, $default = 'cs') {
    $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    if (!$header) {
        return $default;
    }

    $parts = explode(',', $header);
    foreach ($parts as $part) {
        $lang = strtolower(trim(explode(';', $part)[0] ?? ''));
        if (!$lang) {
            continue;
        }

        if (in_array($lang, $supported, true)) {
            return $lang;
        }

        $short = substr($lang, 0, 2);
        if (in_array($short, $supported, true)) {
            return $short;
        }
    }

    return $default;
}

function getCurrentLanguage() {
    $supported = getSupportedLanguages();
    $default = 'cs';

    if (isset($_GET['lang']) && in_array($_GET['lang'], $supported, true)) {
        $lang = $_GET['lang'];
    } elseif (isset($_SESSION['lang']) && in_array($_SESSION['lang'], $supported, true)) {
        $lang = $_SESSION['lang'];
    } else {
        $lang = detectBrowserLanguage($supported, $default);
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['lang'] = $lang;
    }

    return $lang;
}

function getTranslationMap() {
    return [
        'cs' => [
            'app.name' => 'Admin Dashboard',
            'nav.admin' => 'Správa',
            'nav.users' => 'Uživatelé',
            'nav.ip_ranges' => 'IP rozsahy',
            'nav.back_dashboard' => 'Zpět na dashboard',
            'nav.view' => 'Zobrazit',
            'nav.logout' => 'Odhlásit',
            'label.page' => 'Stránka',
            'label.separator' => 'Oddělovač',
            'label.active' => 'Aktivní',
            'label.inactive' => 'Neaktivní',
            'label.actions' => 'Akce',
            'label.status' => 'Status',
            'label.username' => 'Uživatelské jméno',
            'label.password' => 'Heslo',
            'label.email' => 'E-mail',
            'label.you' => 'Vy',
            'label.current_suffix' => '(aktuální)',
            'login.title' => 'Přihlášení - Admin Dashboard',
            'login.heading' => 'Přihlášení',
            'login.ip_warning_line1' => 'Přistupujete z nepovolené IP adresy.',
            'login.ip_warning_line2' => 'Vyžaduje se přihlášení.',
            'login.error.fill_all' => 'Vyplňte všechna pole',
            'login.button' => 'Přihlásit se',
            'login.ip_label' => 'Vaše IP adresa',
            'login.secured' => 'Zabezpečené připojení',
            'auth.invalid_credentials' => 'Neplatné přihlašovací údaje',
            'auth.account_disabled' => 'Účet je deaktivován',
            'dashboard.admin_button' => 'Správa',
            'dashboard.page_badge' => 'Stránka',
            'admin.title' => 'Admin Dashboard - Správa',
            'admin.header' => 'Správa dashboardu',
            'admin.new_subpage' => 'Nová podstránka',
            'admin.appearance' => 'Vzhled',
            'admin.add_button' => 'Přidat tlačítko',
            'admin.button_type' => 'Typ prvku *',
            'admin.type.external' => 'Externí odkaz (URL)',
            'admin.type.page_link' => 'Odkaz na podstránku',
            'admin.type.separator' => 'Oddělovač (prázdné místo)',
            'admin.parent_page' => 'Umístění na stránce *',
            'admin.select_page' => '-- Vyberte stránku --',
            'admin.select_page_help' => 'Vyberte, na které stránce se má tlačítko zobrazovat',
            'admin.title_label' => 'Název *',
            'admin.url_label' => 'URL *',
            'admin.target_page' => 'Cílová stránka *',
            'admin.description' => 'Popis',
            'admin.bg_color' => 'Barva pozadí tlačítka',
            'admin.icon_color' => 'Barva ikony',
            'admin.icon_label' => 'Ikona (FontAwesome)',
            'admin.icon_help' => 'Např: fa-server, fa-shield-alt, fa-chart-line',
            'admin.save' => 'Uložit',
            'admin.modal.add_title' => 'Přidat tlačítko',
            'admin.modal.edit_title' => 'Upravit tlačítko',
            'admin.settings_title' => 'Nastavení vzhledu',
            'admin.min_width' => 'Minimální šířka (px)',
            'admin.min_height' => 'Minimální výška (px)',
            'admin.icon_size' => 'Velikost ikony (em)',
            'admin.title_size' => 'Velikost názvu (em)',
            'admin.grid_gap' => 'Mezera mezi tlačítky (px)',
            'admin.save_settings' => 'Uložit nastavení',
            'admin.new_page_title' => 'Vytvořit novou podstránku',
            'admin.new_page_info' => 'Podstránka bude vytvořena pod aktuální stránkou',
            'admin.page_name' => 'Název stránky *',
            'admin.page_placeholder' => 'Např: Monitoring, Administrace...',
            'admin.create_page' => 'Vytvořit stránku',
            'admin.separator_label' => 'Oddělovač',
            'admin.edit_move' => 'Upravit / Přesunout',
            'admin.edit' => 'Upravit',
            'admin.button_moved' => 'Tlačítko bylo přesunuto na jinou stránku.',
            'admin.settings_saved' => 'Nastavení uloženo. Stránka se nyní obnoví.',
            'admin.subpage_created' => 'Podstránka byla vytvořena s ID: ',
            'admin.confirm_delete_button' => 'Opravdu chcete smazat toto tlačítko?',
            'user_manager.title' => 'Správa uživatelů',
            'user_manager.add_title' => 'Přidat nového uživatele',
            'user_manager.list_title' => 'Seznam uživatelů',
            'user_manager.username_label' => 'Uživatelské jméno *',
            'user_manager.password_label' => 'Heslo *',
            'user_manager.password_min' => 'Minimálně 6 znaků',
            'user_manager.create_user' => 'Vytvořit uživatele',
            'user_manager.last_login' => 'Poslední přihlášení',
            'user_manager.created_at' => 'Vytvořeno',
            'user_manager.edit' => 'Upravit',
            'user_manager.password' => 'Heslo',
            'user_manager.activate_deactivate' => 'Aktivovat/Deaktivovat',
            'user_manager.delete' => 'Smazat',
            'user_manager.edit_modal_title' => 'Upravit uživatele',
            'user_manager.save_changes' => 'Uložit změny',
            'user_manager.change_password_title' => 'Změnit heslo',
            'user_manager.user_label' => 'Uživatel',
            'user_manager.new_password' => 'Nové heslo',
            'user_manager.change_password' => 'Změnit heslo',
            'user_manager.msg.username_exists' => 'Uživatelské jméno již existuje',
            'user_manager.msg.user_added' => 'Uživatel byl úspěšně přidán',
            'user_manager.msg.user_updated' => 'Uživatel byl aktualizován',
            'user_manager.msg.password_changed' => 'Heslo bylo změněno',
            'user_manager.msg.password_short' => 'Heslo musí mít alespoň 6 znaků',
            'user_manager.msg.cannot_deactivate_self' => 'Nemůžete deaktivovat svůj vlastní účet',
            'user_manager.msg.status_changed' => 'Status uživatele byl změněn',
            'user_manager.msg.cannot_delete_self' => 'Nemůžete smazat svůj vlastní účet',
            'user_manager.msg.user_deleted' => 'Uživatel byl smazán',
            'user_manager.confirm_delete_user' => 'Opravdu smazat uživatele?',
            'ip_manager.title' => 'Správa IP rozsahů',
            'ip_manager.header' => 'Správa povolených IP rozsahů',
            'ip_manager.current_ip' => 'Vaše současná IP adresa:',
            'ip_manager.status_label' => 'Status:',
            'ip_manager.status_allowed' => '✓ Povolená',
            'ip_manager.status_denied' => '✗ Nepovolená',
            'ip_manager.add_title' => 'Přidat nový IP rozsah',
            'ip_manager.range_label' => 'IP rozsah (CIDR notace)',
            'ip_manager.range_help' => 'Příklady: 192.168.1.0/24 (celá podsíť), 10.0.0.5/32 (jedna IP)',
            'ip_manager.description_label' => 'Popis',
            'ip_manager.description_placeholder' => 'Lokální síť',
            'ip_manager.add_range' => 'Přidat rozsah',
            'ip_manager.list_title' => 'Povolené IP rozsahy',
            'ip_manager.table.range' => 'IP rozsah',
            'ip_manager.table.description' => 'Popis',
            'ip_manager.table.status' => 'Status',
            'ip_manager.table.actions' => 'Akce',
            'ip_manager.confirm_delete' => 'Opravdu smazat?',
            'ip_manager.active' => 'Aktivní',
            'ip_manager.inactive' => 'Neaktivní',
            'api.page_has_children' => 'Stránka obsahuje podstránky',
            'api.page_has_buttons' => 'Stránka obsahuje tlačítka',
            'api.invalid_action' => 'Neplatná akce',
        ],
        'en' => [
            'app.name' => 'Admin Dashboard',
            'nav.admin' => 'Admin',
            'nav.users' => 'Users',
            'nav.ip_ranges' => 'IP ranges',
            'nav.back_dashboard' => 'Back to dashboard',
            'nav.view' => 'View',
            'nav.logout' => 'Log out',
            'label.page' => 'Page',
            'label.separator' => 'Separator',
            'label.active' => 'Active',
            'label.inactive' => 'Inactive',
            'label.actions' => 'Actions',
            'label.status' => 'Status',
            'label.username' => 'Username',
            'label.password' => 'Password',
            'label.email' => 'Email',
            'label.you' => 'You',
            'label.current_suffix' => '(current)',
            'login.title' => 'Sign in - Admin Dashboard',
            'login.heading' => 'Sign in',
            'login.ip_warning_line1' => 'You are accessing from an unauthorized IP address.',
            'login.ip_warning_line2' => 'Sign in is required.',
            'login.error.fill_all' => 'Fill in all fields',
            'login.button' => 'Sign in',
            'login.ip_label' => 'Your IP address',
            'login.secured' => 'Secure connection',
            'auth.invalid_credentials' => 'Invalid credentials',
            'auth.account_disabled' => 'Account is disabled',
            'dashboard.admin_button' => 'Admin',
            'dashboard.page_badge' => 'Page',
            'admin.title' => 'Admin Dashboard - Admin',
            'admin.header' => 'Dashboard management',
            'admin.new_subpage' => 'New subpage',
            'admin.appearance' => 'Appearance',
            'admin.add_button' => 'Add button',
            'admin.button_type' => 'Element type *',
            'admin.type.external' => 'External link (URL)',
            'admin.type.page_link' => 'Link to subpage',
            'admin.type.separator' => 'Separator (empty space)',
            'admin.parent_page' => 'Location on page *',
            'admin.select_page' => '-- Select page --',
            'admin.select_page_help' => 'Select the page where the button should appear',
            'admin.title_label' => 'Title *',
            'admin.url_label' => 'URL *',
            'admin.target_page' => 'Target page *',
            'admin.description' => 'Description',
            'admin.bg_color' => 'Button background color',
            'admin.icon_color' => 'Icon color',
            'admin.icon_label' => 'Icon (FontAwesome)',
            'admin.icon_help' => 'e.g.: fa-server, fa-shield-alt, fa-chart-line',
            'admin.save' => 'Save',
            'admin.modal.add_title' => 'Add button',
            'admin.modal.edit_title' => 'Edit button',
            'admin.settings_title' => 'Appearance settings',
            'admin.min_width' => 'Minimum width (px)',
            'admin.min_height' => 'Minimum height (px)',
            'admin.icon_size' => 'Icon size (em)',
            'admin.title_size' => 'Title size (em)',
            'admin.grid_gap' => 'Button gap (px)',
            'admin.save_settings' => 'Save settings',
            'admin.new_page_title' => 'Create new subpage',
            'admin.new_page_info' => 'The subpage will be created under the current page',
            'admin.page_name' => 'Page name *',
            'admin.page_placeholder' => 'e.g.: Monitoring, Administration...',
            'admin.create_page' => 'Create page',
            'admin.separator_label' => 'Separator',
            'admin.edit_move' => 'Edit / Move',
            'admin.edit' => 'Edit',
            'admin.button_moved' => 'The button was moved to another page.',
            'admin.settings_saved' => 'Settings saved. The page will now reload.',
            'admin.subpage_created' => 'Subpage created with ID: ',
            'admin.confirm_delete_button' => 'Do you really want to delete this button?',
            'user_manager.title' => 'User management',
            'user_manager.add_title' => 'Add new user',
            'user_manager.list_title' => 'User list',
            'user_manager.username_label' => 'Username *',
            'user_manager.password_label' => 'Password *',
            'user_manager.password_min' => 'At least 6 characters',
            'user_manager.create_user' => 'Create user',
            'user_manager.last_login' => 'Last login',
            'user_manager.created_at' => 'Created',
            'user_manager.edit' => 'Edit',
            'user_manager.password' => 'Password',
            'user_manager.activate_deactivate' => 'Activate/Deactivate',
            'user_manager.delete' => 'Delete',
            'user_manager.edit_modal_title' => 'Edit user',
            'user_manager.save_changes' => 'Save changes',
            'user_manager.change_password_title' => 'Change password',
            'user_manager.user_label' => 'User',
            'user_manager.new_password' => 'New password',
            'user_manager.change_password' => 'Change password',
            'user_manager.msg.username_exists' => 'Username already exists',
            'user_manager.msg.user_added' => 'User added successfully',
            'user_manager.msg.user_updated' => 'User updated',
            'user_manager.msg.password_changed' => 'Password changed',
            'user_manager.msg.password_short' => 'Password must be at least 6 characters',
            'user_manager.msg.cannot_deactivate_self' => 'You cannot deactivate your own account',
            'user_manager.msg.status_changed' => 'User status changed',
            'user_manager.msg.cannot_delete_self' => 'You cannot delete your own account',
            'user_manager.msg.user_deleted' => 'User deleted',
            'user_manager.confirm_delete_user' => 'Really delete the user?',
            'ip_manager.title' => 'IP range management',
            'ip_manager.header' => 'Allowed IP ranges',
            'ip_manager.current_ip' => 'Your current IP address:',
            'ip_manager.status_label' => 'Status:',
            'ip_manager.status_allowed' => '✓ Allowed',
            'ip_manager.status_denied' => '✗ Not allowed',
            'ip_manager.add_title' => 'Add new IP range',
            'ip_manager.range_label' => 'IP range (CIDR notation)',
            'ip_manager.range_help' => 'Examples: 192.168.1.0/24 (entire subnet), 10.0.0.5/32 (single IP)',
            'ip_manager.description_label' => 'Description',
            'ip_manager.description_placeholder' => 'Local network',
            'ip_manager.add_range' => 'Add range',
            'ip_manager.list_title' => 'Allowed IP ranges',
            'ip_manager.table.range' => 'IP range',
            'ip_manager.table.description' => 'Description',
            'ip_manager.table.status' => 'Status',
            'ip_manager.table.actions' => 'Actions',
            'ip_manager.confirm_delete' => 'Really delete?',
            'ip_manager.active' => 'Active',
            'ip_manager.inactive' => 'Inactive',
            'api.page_has_children' => 'Page has subpages',
            'api.page_has_buttons' => 'Page contains buttons',
            'api.invalid_action' => 'Invalid action',
        ],
    ];
}

function t($key, $replacements = []) {
    $lang = getCurrentLanguage();
    $translations = getTranslationMap();
    $value = $translations[$lang][$key] ?? $translations['cs'][$key] ?? $key;

    foreach ($replacements as $placeholder => $replacement) {
        $value = str_replace('{' . $placeholder . '}', $replacement, $value);
    }

    return $value;
}

function getJsTranslations($keys) {
    $result = [];
    foreach ($keys as $key) {
        $result[$key] = t($key);
    }
    return $result;
}
?>
