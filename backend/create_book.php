<?php
// backend/create_book.php
session_start();
header('Content-Type: application/json');

// Role-based access control
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => '權限不足：只有管理員可以新增書籍。']);
    exit();
}

require_once 'db_connection.php';

$response = ['status' => 'error', 'message' => '無效的請求。'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 讀取原始的 POST 資料 (JSON)
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    // 過濾和驗證輸入
    $title = isset($data['title']) ? trim($data['title']) : '';
    $author = isset($data['author']) ? trim($data['author']) : '';
    $isbn = isset($data['isbn']) ? trim($data['isbn']) : '';
    $publisher = isset($data['publisher']) ? trim($data['publisher']) : '';
    $publication_year = isset($data['publication_year']) ? filter_var($data['publication_year'], FILTER_VALIDATE_INT) : null;

    if (empty($title) || empty($author) || empty($isbn)) {
        $response['message'] = '書名、作者和 ISBN 為必填欄位。';
        http_response_code(400); // Bad Request
    } else {
        try {
            $pdo = get_db_connection();
            
            $sql = "INSERT INTO books (title, author, isbn, publisher, publication_year) VALUES (:title, :author, :isbn, :publisher, :publication_year)";
            $stmt = $pdo->prepare($sql);
            
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':author', $author, PDO::PARAM_STR);
            $stmt->bindParam(':isbn', $isbn, PDO::PARAM_STR);
            $stmt->bindParam(':publisher', $publisher, PDO::PARAM_STR);
            // 如果年份為空或無效，則綁定為 NULL
            if ($publication_year === false || $publication_year === null) {
                $stmt->bindValue(':publication_year', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':publication_year', $publication_year, PDO::PARAM_INT);
            }
            
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = '書籍新增成功。';
                $response['book_id'] = $pdo->lastInsertId();
                http_response_code(201); // Created
            } else {
                $response['message'] = '新增書籍失敗。';
                http_response_code(500);
            }

        } catch (PDOException $e) {
            // 檢查是否為重複鍵值的錯誤 (例如 ISBN 已存在)
            if ($e->getCode() == 23000) {
                 $response['message'] = '錯誤：此 ISBN 已存在於資料庫中。';
                 http_response_code(409); // Conflict
            } else {
                $response['message'] = '資料庫錯誤: ' . $e->getMessage();
                http_response_code(500);
            }
        }
    }
} else {
    http_response_code(405); // Method Not Allowed
    $response['message'] = '僅允許 POST 方法。';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
