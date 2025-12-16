<?php
// backend/borrow_book.php
session_start();
header('Content-Type: application/json');
require_once 'db_connection.php';

$response = ['status' => 'error', 'message' => '無效的請求。'];

// 步驟 1: 驗證使用者是否登入
if (!isset($_SESSION['user_id'])) {
    $response['message'] = '需要進行身份驗證，請先登入。';
    http_response_code(401); // Unauthorized
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
            // 步驟 2: 開始資料庫交易
            $pdo->beginTransaction();

            // 步驟 3: 檢查書籍狀態並鎖定該資料列，防止多人同時借閱
            $sql_check = "SELECT status FROM books WHERE id = :book_id FOR UPDATE";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->bindParam(':book_id', $book_id, PDO::PARAM_INT);
            $stmt_check->execute();
            $book = $stmt_check->fetch();

            if (!$book) {
                throw new Exception('找不到該書籍。', 404);
            }

            if ($book['status'] !== '在庫') {
                throw new Exception('此書目前已被借出或狀態異常。', 409); // Conflict
            }

            // 步驟 4: 更新書籍狀態為 '已借出'
            $sql_update = "UPDATE books SET status = '已借出' WHERE id = :book_id";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->bindParam(':book_id', $book_id, PDO::PARAM_INT);
            $stmt_update->execute();

            // 步驟 5: 在 borrowing_records 建立新紀錄 (假設借期為 14 天)
            $due_date = date('Y-m-d H:i:s', strtotime('+14 days'));
            $sql_insert = "INSERT INTO borrowing_records (book_id, user_id, due_date) VALUES (:book_id, :user_id, :due_date)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->bindParam(':book_id', $book_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(':due_date', $due_date, PDO::PARAM_STR);
            $stmt_insert->execute();

            // 步驟 6: 提交交易
            $pdo->commit();

            $response['status'] = 'success';
            $response['message'] = '書籍借閱成功，應還日期為 ' . date('Y-m-d', strtotime($due_date)) . '。';
            http_response_code(200);

        } catch (Exception $e) {
            // 如果交易中發生任何錯誤，則回滾
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $response['message'] = $e->getMessage();
            // 使用 Exception 的 code 作為 HTTP 狀態碼
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
