<?php
// auth.php - Autentizační funkce a IP kontrola
require_once 'config.php';

session_start();

require_once 'i18n.php';

// Kontrola IP adresy proti povoleným rozsahům (CIDR)
function isIpAllowed($ip) {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT ip_range FROM allowed_ip_ranges WHERE is_active = 1");
    $ranges = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($ranges)) {
        // Pokud nejsou definované žádné rozsahy, povolíme vše
        return true;
    }
    
    foreach ($ranges as $range) {
        if (cidrMatch($ip, $range)) {
            return true;
        }
    }
    
    return false;
}

// Kontrola IP adresy proti CIDR rozsahu
function cidrMatch($ip, $cidr) {
    if (strpos($cidr, '/') === false) {
        // Pokud není specifikována maska, jedná se o jednotlivou IP
        $cidr .= '/32';
    }
    
    list($subnet, $mask) = explode('/', $cidr);
    
    // Kontrola validity IP adres
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return false;
    }
    if (!filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return false;
    }
    
    $ip_long = ip2long($ip);
    $subnet_long = ip2long($subnet);
    $mask_long = -1 << (32 - (int)$mask);
    $subnet_long &= $mask_long; // Normalize subnet
    
    return ($ip_long & $mask_long) === $subnet_long;
}

// Získání IP adresy klienta
function getClientIp() {
    $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// Kontrola, zda je uživatel přihlášen
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Kontrola autentizace a IP
function checkAuth($redirectToLogin = true) {
    $clientIp = getClientIp();
    $ipAllowed = isIpAllowed($clientIp);
    
    // Pokud je IP povolená, nemusíme řešit login
    if ($ipAllowed) {
        return true;
    }
    
    // IP není povolená, musíme být přihlášeni
    if (!isLoggedIn()) {
        if ($redirectToLogin) {
            header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        return false;
    }
    
    return true;
}

// Přihlášení uživatele
function loginUser($username, $password) {
    $pdo = getDbConnection();
    
    $stmt = $pdo->prepare("SELECT id, username, password_hash, is_active FROM dashboard_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return ['success' => false, 'error' => t('auth.invalid_credentials')];
    }
    
    if (!$user['is_active']) {
        return ['success' => false, 'error' => t('auth.account_disabled')];
    }
    
    if (!password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'error' => t('auth.invalid_credentials')];
    }
    
    // Úspěšné přihlášení
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['login_time'] = time();
    
    // Aktualizace last_login
    $stmt = $pdo->prepare("UPDATE dashboard_users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Záznam do session logu
    $stmt = $pdo->prepare("INSERT INTO login_sessions (user_id, ip_address, user_agent) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], getClientIp(), $_SERVER['HTTP_USER_AGENT'] ?? '']);
    
    return ['success' => true, 'user_id' => $user['id']];
}

// Odhlášení uživatele
function logoutUser() {
    if (isset($_SESSION['user_id'])) {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("UPDATE login_sessions SET logout_time = NOW() 
                               WHERE user_id = ? AND logout_time IS NULL 
                               ORDER BY login_time DESC LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
    }
    
    $_SESSION = array();
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

// Získání informací o přihlášeném uživateli
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT id, username, email, last_login FROM dashboard_users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
?>
