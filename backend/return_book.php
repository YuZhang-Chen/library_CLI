<?php
// backend/return_book.php
session_start();
header('Content-Type: application/json');
require_once 'db_connection.php';

$response = ['status' => 'error', 'message' => '無效的請求。'];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = '需要進行身份驗證，請先登入。';
    http_response_code(401);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $book_id = isset($data['book_id']) ? filter_var($data['book_id'], FILTER_VALIDATE_INT) : null;
    $user_id = $_SESSION['user_id'];

    if (empty($book_id)) {
        $response['message'] = '書籍 ID 為必填欄位。';
        http_response_code(400);
    } else {
        $pdo = get_db_connection();
        try {
            $pdo->beginTransaction();

            // 步驟 1: 找出由該使用者借閱、且尚未歸還的紀錄，並鎖定它
            $sql_check = "SELECT id FROM borrowing_records WHERE book_id = :book_id AND user_id = :user_id AND return_date IS NULL FOR UPDATE";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->bindParam(':book_id', $book_id, PDO::PARAM_INT);
            $stmt_check->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_check->execute();
            $record = $stmt_check->fetch();

            if (!$record) {
                throw new Exception('查無此書的有效借閱紀錄，或您並非借閱者。', 404);
            }

            // 步驟 2: 更新借閱紀錄，填上歸還日期
            $sql_update_record = "UPDATE borrowing_records SET return_date = CURRENT_TIMESTAMP WHERE id = :record_id";
            $stmt_update_record = $pdo->prepare($sql_update_record);
            $stmt_update_record->bindParam(':record_id', $record['id'], PDO::PARAM_INT);
            $stmt_update_record->execute();

            // 步驟 3: 將書本狀態更新回 '在庫'
            $sql_update_book = "UPDATE books SET status = '在庫' WHERE id = :book_id";
            $stmt_update_book = $pdo->prepare($sql_update_book);
            $stmt_update_book->bindParam(':book_id', $book_id, PDO::PARAM_INT);
            $stmt_update_book->execute();

            // 步驟 4: 提交交易
            $pdo->commit();

            $response['status'] = 'success';
            $response['message'] = '書籍歸還成功。';
            http_response_code(200);

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $response['message'] = $e->getMessage();
            $http_code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
            http_response_code($http_code);
        }
    }
} else {
    http_response_code(405);
    $response['message'] = '僅允許 POST 方法。';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
