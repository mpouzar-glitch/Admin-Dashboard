<?php
require_once 'auth.php';
checkAuth();

$pdo = getDbConnection();
$message = '';
$messageType = '';
$lang = getCurrentLanguage();

// Zpracování akcí
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $email = $_POST['email'];
        
        // Kontrola duplicity
        $stmt = $pdo->prepare("SELECT id FROM dashboard_users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $message = t('user_manager.msg.username_exists');
            $messageType = 'error';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO dashboard_users (username, password_hash, email) VALUES (?, ?, ?)");
            $stmt->execute([$username, $passwordHash, $email]);
            $message = t('user_manager.msg.user_added');
            $messageType = 'success';
        }
    } 
    elseif ($action === 'update') {
        $id = $_POST['id'];
        $email = $_POST['email'];
        
        $stmt = $pdo->prepare("UPDATE dashboard_users SET email = ? WHERE id = ?");
        $stmt->execute([$email, $id]);
        $message = t('user_manager.msg.user_updated');
        $messageType = 'success';
    }
    elseif ($action === 'change_password') {
        $id = $_POST['id'];
        $newPassword = $_POST['new_password'];
        
        if (strlen($newPassword) >= 6) {
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE dashboard_users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$passwordHash, $id]);
            $message = t('user_manager.msg.password_changed');
            $messageType = 'success';
        } else {
            $message = t('user_manager.msg.password_short');
            $messageType = 'error';
        }
    }
    elseif ($action === 'toggle') {
        $id = $_POST['id'];
        // Kontrola, aby se admin nemohl deaktivovat sám
        if ($id == $_SESSION['user_id']) {
            $message = t('user_manager.msg.cannot_deactivate_self');
            $messageType = 'error';
        } else {
            $stmt = $pdo->prepare("UPDATE dashboard_users SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$id]);
            $message = t('user_manager.msg.status_changed');
            $messageType = 'success';
        }
    }
    elseif ($action === 'delete') {
        $id = $_POST['id'];
        // Kontrola, aby se admin nemohl smazat sám
        if ($id == $_SESSION['user_id']) {
            $message = t('user_manager.msg.cannot_delete_self');
            $messageType = 'error';
        } else {
            $stmt = $pdo->prepare("DELETE FROM dashboard_users WHERE id = ?");
            $stmt->execute([$id]);
            $message = t('user_manager.msg.user_deleted');
            $messageType = 'success';
        }
    }
}

$users = $pdo->query("SELECT id, username, email, is_active, last_login, created_at FROM dashboard_users ORDER BY id ASC")->fetchAll();
$currentUserId = $_SESSION['user_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(t('user_manager.title')); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 1400px; margin: 0 auto; }
        header {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        h1 { color: #2c3e50; font-size: 2em; }
        h2 { color: #2c3e50; margin-bottom: 20px; }
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
        .btn-back { background: #95a5a6; color: white; }
        .btn-back:hover { background: #7f8c8d; }
        .content-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s;
        }
        .message.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .message.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: bold;
            color: #2c3e50;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; }
        th { background: #f8f9fa; font-weight: bold; color: #2c3e50; }
        .status-active { color: #27ae60; font-weight: bold; }
        .status-inactive { color: #e74c3c; font-weight: bold; }
        .btn-small { 
            padding: 6px 12px; 
            font-size: 13px; 
            border-radius: 6px;
            margin: 2px;
        }
        .btn-primary { background: #3498db; color: white; }
        .btn-primary:hover { background: #2980b9; }
        .btn-success { background: #27ae60; color: white; }
        .btn-success:hover { background: #229954; }
        .btn-warning { background: #f39c12; color: white; }
        .btn-warning:hover { background: #e67e22; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-danger:hover { background: #c0392b; }
        .user-badge {
            background: #667eea;
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        
        /* Modal */
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
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            animation: slideDown 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 20px;
        }
        .close:hover { color: #000; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-users-cog"></i> <?php echo htmlspecialchars(t('user_manager.title')); ?></h1>
            <a href="admin.php" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> <?php echo htmlspecialchars(t('nav.back_dashboard')); ?>
            </a>
        </header>
        
        <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <div class="content-box">
            <h2><i class="fas fa-user-plus"></i> <?php echo htmlspecialchars(t('user_manager.add_title')); ?></h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group">
                        <label for="username"><?php echo htmlspecialchars(t('user_manager.username_label')); ?></label>
                        <input type="text" id="username" name="username" required minlength="3">
                    </div>
                    <div class="form-group">
                        <label for="email"><?php echo htmlspecialchars(t('label.email')); ?></label>
                        <input type="email" id="email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="password"><?php echo htmlspecialchars(t('user_manager.password_label')); ?></label>
                        <input type="password" id="password" name="password" required minlength="6">
                        <small style="color: #7f8c8d;"><?php echo htmlspecialchars(t('user_manager.password_min')); ?></small>
                    </div>
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus"></i> <?php echo htmlspecialchars(t('user_manager.create_user')); ?>
                </button>
            </form>
        </div>
        
        <div class="content-box">
            <h2><i class="fas fa-list"></i> <?php echo htmlspecialchars(t('user_manager.list_title')); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?php echo htmlspecialchars(t('user_manager.user_label')); ?></th>
                        <th><?php echo htmlspecialchars(t('label.email')); ?></th>
                        <th><?php echo htmlspecialchars(t('label.status')); ?></th>
                        <th><?php echo htmlspecialchars(t('user_manager.last_login')); ?></th>
                        <th><?php echo htmlspecialchars(t('user_manager.created_at')); ?></th>
                        <th><?php echo htmlspecialchars(t('label.actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                            <?php if ($user['id'] == $currentUserId): ?>
                                <span class="user-badge"><?php echo htmlspecialchars(t('label.you')); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                        <td>
                            <?php if ($user['is_active']): ?>
                                <span class="status-active">
                                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars(t('label.active')); ?>
                                </span>
                            <?php else: ?>
                                <span class="status-inactive">
                                    <i class="fas fa-times-circle"></i> <?php echo htmlspecialchars(t('label.inactive')); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            if ($user['last_login']) {
                                $lastLogin = new DateTime($user['last_login']);
                                echo $lastLogin->format('d.m.Y H:i');
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            $created = new DateTime($user['created_at']);
                            echo $created->format('d.m.Y');
                            ?>
                        </td>
                        <td>
                            <button class="btn btn-small btn-primary" onclick="openEditModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['email'], ENT_QUOTES); ?>')">
                                <i class="fas fa-edit"></i> <?php echo htmlspecialchars(t('user_manager.edit')); ?>
                            </button>
                            <button class="btn btn-small btn-warning" onclick="openPasswordModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>')">
                                <i class="fas fa-key"></i> <?php echo htmlspecialchars(t('user_manager.password')); ?>
                            </button>
                            
                            <?php if ($user['id'] != $currentUserId): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-small btn-success" title="<?php echo htmlspecialchars(t('user_manager.activate_deactivate')); ?>">
                                        <i class="fas fa-toggle-<?php echo $user['is_active'] ? 'on' : 'off'; ?>"></i>
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('<?php echo htmlspecialchars(t('user_manager.confirm_delete_user'), ENT_QUOTES); ?>');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-small btn-danger" title="<?php echo htmlspecialchars(t('user_manager.delete')); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Modal pro editaci uživatele -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editModal')">&times;</span>
            <h2><i class="fas fa-edit"></i> <?php echo htmlspecialchars(t('user_manager.edit_modal_title')); ?></h2>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                    <label for="edit_email"><?php echo htmlspecialchars(t('label.email')); ?></label>
                    <input type="email" id="edit_email" name="email">
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> <?php echo htmlspecialchars(t('user_manager.save_changes')); ?>
                </button>
            </form>
        </div>
    </div>
    
    <!-- Modal pro změnu hesla -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('passwordModal')">&times;</span>
            <h2><i class="fas fa-key"></i> <?php echo htmlspecialchars(t('user_manager.change_password_title')); ?></h2>
            <p style="margin-bottom: 20px;"><?php echo htmlspecialchars(t('user_manager.user_label')); ?>: <strong id="password_username"></strong></p>
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                <input type="hidden" id="password_id" name="id">
                <div class="form-group">
                    <label for="new_password"><?php echo htmlspecialchars(t('user_manager.new_password')); ?></label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                    <small style="color: #7f8c8d;"><?php echo htmlspecialchars(t('user_manager.password_min')); ?></small>
                </div>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> <?php echo htmlspecialchars(t('user_manager.change_password')); ?>
                </button>
            </form>
        </div>
    </div>
    
    <script>
        function openEditModal(id, email) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_email').value = email;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function openPasswordModal(id, username) {
            document.getElementById('password_id').value = id;
            document.getElementById('password_username').textContent = username;
            document.getElementById('new_password').value = '';
            document.getElementById('passwordModal').style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
