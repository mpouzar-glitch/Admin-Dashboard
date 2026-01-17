<?php
// api.php
require_once 'config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

$pdo = getDbConnection();

switch ($action) {
    case 'get_all':
        $pageId = $_GET['page_id'] ?? 1;
        $stmt = $pdo->prepare("SELECT * FROM dashboard_buttons WHERE parent_page_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$pageId]);
        echo json_encode($stmt->fetchAll());
        break;
    
    case 'get_page_info':
        $pageId = $_GET['page_id'] ?? 1;
        $stmt = $pdo->prepare("SELECT * FROM dashboard_pages WHERE id = ?");
        $stmt->execute([$pageId]);
        echo json_encode($stmt->fetch());
        break;
    
    case 'get_breadcrumb':
        $pageId = $_GET['page_id'] ?? 1;
        $breadcrumb = [];
        
        while ($pageId) {
            $stmt = $pdo->prepare("SELECT id, page_name, parent_page_id FROM dashboard_pages WHERE id = ?");
            $stmt->execute([$pageId]);
            $page = $stmt->fetch();
            
            if ($page) {
                array_unshift($breadcrumb, $page);
                $pageId = $page['parent_page_id'];
            } else {
                break;
            }
        }
        
        echo json_encode($breadcrumb);
        break;
    
    case 'get_pages':
        $stmt = $pdo->query("SELECT id, page_name, parent_page_id FROM dashboard_pages ORDER BY id ASC");
        echo json_encode($stmt->fetchAll());
        break;
    
    case 'get_settings':
        echo json_encode(getSettings());
        break;
    
    case 'update_settings':
        $data = json_decode(file_get_contents('php://input'), true);
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE dashboard_settings SET setting_value = ? WHERE setting_key = ?");
            foreach ($data as $key => $value) {
                $stmt->execute([$value, $key]);
            }
            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
    
    case 'add':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO dashboard_buttons (title, url, description, color, background_color, icon, button_type, parent_page_id, target_page_id, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $maxOrder = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order FROM dashboard_buttons WHERE parent_page_id = ?");
        $maxOrder->execute([$data['parent_page_id'] ?? 1]);
        $nextOrder = $maxOrder->fetch()['next_order'];
        
        $stmt->execute([
            $data['title'] ?? '',
            $data['url'] ?? '',
            $data['description'] ?? '',
            $data['color'] ?? '#3498db',
            $data['background_color'] ?? '#ffffff',
            $data['icon'] ?? 'fa-link',
            $data['button_type'] ?? 'external_url',
            $data['parent_page_id'] ?? 1,
            $data['target_page_id'] ?? null,
            $nextOrder
        ]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;
    
    case 'update':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("UPDATE dashboard_buttons SET title=?, url=?, description=?, color=?, background_color=?, icon=?, button_type=?, target_page_id=?, parent_page_id=? WHERE id=?");
        $stmt->execute([
            $data['title'] ?? '',
            $data['url'] ?? '',
            $data['description'] ?? '',
            $data['color'],
            $data['background_color'],
            $data['icon'],
            $data['button_type'] ?? 'external_url',
            $data['target_page_id'] ?? null,
            $data['parent_page_id'] ?? 1,
            $data['id']
        ]);
        echo json_encode(['success' => true]);
        break;
    
    case 'delete':
        $id = $_POST['id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM dashboard_buttons WHERE id=?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;
    
    case 'update_order':
        $order = json_decode(file_get_contents('php://input'), true);
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE dashboard_buttons SET sort_order=? WHERE id=?");
            foreach ($order as $index => $id) {
                $stmt->execute([$index, $id]);
            }
            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;
    
    case 'add_page':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO dashboard_pages (page_name, parent_page_id) VALUES (?, ?)");
        $stmt->execute([$data['page_name'], $data['parent_page_id'] ?? null]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;
    
    case 'delete_page':
        $id = $_POST['id'] ?? 0;
        // Kontrola, zda stránka nemá podstránky nebo tlačítka
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM dashboard_pages WHERE parent_page_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetch()['cnt'] > 0) {
            echo json_encode(['success' => false, 'error' => 'Stránka obsahuje podstránky']);
            break;
        }
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM dashboard_buttons WHERE parent_page_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetch()['cnt'] > 0) {
            echo json_encode(['success' => false, 'error' => 'Stránka obsahuje tlačítka']);
            break;
        }
        
        $stmt = $pdo->prepare("DELETE FROM dashboard_pages WHERE id = ? AND id != 1");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;
    
    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>
