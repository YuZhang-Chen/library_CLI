<?php
// backend/list_records.php
session_start();
header('Content-Type: application/json');
require_once 'db_connection.php';

$response = ['status' => 'error', 'message' => '無效的請求。'];

// 1. 權限檢查：使用者必須登入
if (!isset($_SESSION['user_id'])) {
    $response['message'] = '需要進行身份驗證，請先登入。';
    http_response_code(401); // Unauthorized
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $pdo = get_db_connection();
        
        $user_id = $_SESSION['user_id'];
        $user_role = $_SESSION['role'];

        // 2. 建立 SQL 查詢
        $sql = "
            SELECT 
                br.id,
                b.title AS book_title,
                u.username,
                br.borrow_date,
                br.due_date,
                br.return_date
            FROM 
                borrowing_records br
            JOIN 
                books b ON br.book_id = b.id
            JOIN 
                users u ON br.user_id = u.id
        ";

        // 3. 根據角色決定查詢範圍
        if ($user_role === 'user') {
            // 一般使用者只能看到自己的紀錄
            $sql .= " WHERE br.user_id = :user_id";
        }

        $sql .= " ORDER BY br.borrow_date DESC";

        $stmt = $pdo->prepare($sql);

        if ($user_role === 'user') {
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        }

        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['status'] = 'success';
        $response['message'] = '借閱紀錄獲取成功。';
        $response['data'] = $records;
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
