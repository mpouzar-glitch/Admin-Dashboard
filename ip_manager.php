<?php
require_once 'auth.php';
checkAuth();

$pdo = getDbConnection();
$lang = getCurrentLanguage();

// Zpracování akcí
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO allowed_ip_ranges (ip_range, description) VALUES (?, ?)");
        $stmt->execute([$_POST['ip_range'], $_POST['description']]);
        header('Location: ip_manager.php?success=added');
        exit;
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM allowed_ip_ranges WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        header('Location: ip_manager.php?success=deleted');
        exit;
    } elseif ($action === 'toggle') {
        $stmt = $pdo->prepare("UPDATE allowed_ip_ranges SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        header('Location: ip_manager.php?success=toggled');
        exit;
    }
}

$ranges = $pdo->query("SELECT * FROM allowed_ip_ranges ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(t('ip_manager.title')); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
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
        h1 { color: #2c3e50; }
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
        .content-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; }
        th { background: #f8f9fa; font-weight: bold; }
        .status-active { color: #27ae60; }
        .status-inactive { color: #e74c3c; }
        .btn-small { padding: 6px 12px; font-size: 14px; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .current-ip { background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
    <header>
        <h1><i class="fas fa-network-wired"></i> <?php echo htmlspecialchars(t('ip_manager.header')); ?></h1>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="user_manager.php" class="btn" style="background: #9b59b6; color: white;">
                <i class="fas fa-users-cog"></i> <?php echo htmlspecialchars(t('nav.users')); ?>
            </a>
            <a href="admin.php" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> <?php echo htmlspecialchars(t('nav.back_dashboard')); ?>
            </a>
        </div>
    </header>
        
        <div class="current-ip">
            <strong><?php echo htmlspecialchars(t('ip_manager.current_ip')); ?></strong> <?php echo htmlspecialchars(getClientIp()); ?>
            <br><small><?php echo htmlspecialchars(t('ip_manager.status_label')); ?> <?php echo isIpAllowed(getClientIp()) ? '<span class="status-active">' . htmlspecialchars(t('ip_manager.status_allowed')) . '</span>' : '<span class="status-inactive">' . htmlspecialchars(t('ip_manager.status_denied')) . '</span>'; ?></small>
        </div>
        
        <div class="content-box">
            <h2><?php echo htmlspecialchars(t('ip_manager.add_title')); ?></h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label><?php echo htmlspecialchars(t('ip_manager.range_label')); ?></label>
                    <input type="text" name="ip_range" placeholder="192.168.1.0/24" required>
                    <small><?php echo htmlspecialchars(t('ip_manager.range_help')); ?></small>
                </div>
                <div class="form-group">
                    <label><?php echo htmlspecialchars(t('ip_manager.description_label')); ?></label>
                    <input type="text" name="description" placeholder="<?php echo htmlspecialchars(t('ip_manager.description_placeholder')); ?>">
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus"></i> <?php echo htmlspecialchars(t('ip_manager.add_range')); ?>
                </button>
            </form>
        </div>
        
        <div class="content-box">
            <h2><?php echo htmlspecialchars(t('ip_manager.list_title')); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th><?php echo htmlspecialchars(t('ip_manager.table.range')); ?></th>
                        <th><?php echo htmlspecialchars(t('ip_manager.table.description')); ?></th>
                        <th><?php echo htmlspecialchars(t('ip_manager.table.status')); ?></th>
                        <th><?php echo htmlspecialchars(t('ip_manager.table.actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ranges as $range): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($range['ip_range']); ?></code></td>
                        <td><?php echo htmlspecialchars($range['description']); ?></td>
                        <td>
                            <?php if ($range['is_active']): ?>
                                <span class="status-active"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars(t('ip_manager.active')); ?></span>
                            <?php else: ?>
                                <span class="status-inactive"><i class="fas fa-times-circle"></i> <?php echo htmlspecialchars(t('ip_manager.inactive')); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?php echo $range['id']; ?>">
                                <button type="submit" class="btn btn-small btn-success">
                                    <i class="fas fa-toggle-on"></i>
                                </button>
                            </form>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('<?php echo htmlspecialchars(t('ip_manager.confirm_delete'), ENT_QUOTES); ?>');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $range['id']; ?>">
                                <button type="submit" class="btn btn-small btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
