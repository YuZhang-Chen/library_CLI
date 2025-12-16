<?php
// backend/delete_book.php
session_start();
header('Content-Type: application/json');

// Role-based access control
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => '權限不足：只有管理員可以刪除書籍。']);
    exit();
}

require_once 'db_connection.php';

$response = ['status' => 'error', 'message' => '無效的請求。'];

// 我們使用 POST 來接收刪除資料，以簡化前端操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    
    $id = isset($data['id']) ? filter_var($data['id'], FILTER_VALIDATE_INT) : null;

    if (empty($id)) {
        $response['message'] = '書籍 ID 為必填欄位。';
        http_response_code(400); // Bad Request
    } else {
        try {
            $pdo = get_db_connection();
            
            $sql = "DELETE FROM books WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    $response['status'] = 'success';
                    $response['message'] = '書籍刪除成功。';
                    http_response_code(200);
                } else {
                    $response['status'] = 'fail';
                    $response['message'] = '找不到該書籍，可能已被刪除。';
                    http_response_code(404); // Not Found
                }
            } else {
                $response['message'] = '刪除書籍失敗。';
                http_response_code(500);
            }

        } catch (PDOException $e) {
            $response['message'] = '資料庫錯誤: ' . $e->getMessage();
            http_response_code(500);
        }
    }
} else {
    http_response_code(405); // Method Not Allowed
    $response['message'] = '僅允許 POST 方法。';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
