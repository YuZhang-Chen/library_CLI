<?php
// backend/admin_stats.php
session_start();
header('Content-Type: application/json');
require_once 'db_connection.php';

// 1. 權限檢查：只有管理員可以存取
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => '權限不足：只有管理員可以存取此頁面。']);
    exit;
}

$response = ['status' => 'error', 'message' => '無法獲取統計資料。'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $pdo = get_db_connection();

        // 獲取書籍總數
        $stmt_books = $pdo->query("SELECT COUNT(*) FROM books");
        $total_books = $stmt_books->fetchColumn();

        // 獲取會員總數
        $stmt_users = $pdo->query("SELECT COUNT(*) FROM users");
        $total_users = $stmt_users->fetchColumn();

        // 獲取已借出書籍數量
        $stmt_borrowed = $pdo->query("SELECT COUNT(*) FROM books WHERE status = '已借出'");
        $borrowed_books = $stmt_borrowed->fetchColumn();
        
        // 獲取總借閱次數
        $stmt_records = $pdo->query("SELECT COUNT(*) FROM borrowing_records");
        $total_records = $stmt_records->fetchColumn();

        $stats = [
            'total_books' => (int)$total_books,
            'total_users' => (int)$total_users,
            'borrowed_books' => (int)$borrowed_books,
            'available_books' => (int)($total_books - $borrowed_books),
            'total_records' => (int)$total_records
        ];

        $response['status'] = 'success';
        $response['message'] = '儀表板統計資料獲取成功。';
        $response['data'] = $stats;
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
