<?php
// backend/list_users.php
session_start();
header('Content-Type: application/json');
require_once 'db_connection.php';

// 1. 權限檢查：只有管理員可以存取
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => '權限不足：只有管理員可以瀏覽會員列表。']);
    exit;
}

$response = ['status' => 'error', 'message' => '無法獲取會員列表。'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $pdo = get_db_connection();

        // 2. 查詢使用者列表，不包含密碼
        $sql = "SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC";
        $stmt = $pdo->query($sql);
        
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['status'] = 'success';
        $response['message'] = '會員列表獲取成功。';
        $response['data'] = $users;
        http_response_code(200);

    } catch (PDOException $e) {
        $response['message'] = '資料庫錯誤: ' . $e->getMessage();
        http_response_code(500);
    }
} else {
    http_response_code(405); // Method Not Allowed
    $response['message'] = '僅允許 GET 方法。';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
