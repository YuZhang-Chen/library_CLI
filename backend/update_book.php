<?php
// backend/update_book.php
session_start();
header('Content-Type: application/json');

// Role-based access control
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => '權限不足：只有管理員可以更新書籍。']);
    exit();
}

require_once 'db_connection.php';

$response = ['status' => 'error', 'message' => '無效的請求。'];

// 我們使用 POST 來接收更新資料，因為某些主機環境對 PUT/PATCH 的支援不一
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    $id = isset($data['id']) ? filter_var($data['id'], FILTER_VALIDATE_INT) : null;
    $title = isset($data['title']) ? trim($data['title']) : '';
    $author = isset($data['author']) ? trim($data['author']) : '';
    $isbn = isset($data['isbn']) ? trim($data['isbn']) : '';
    $publisher = isset($data['publisher']) ? trim($data['publisher']) : '';
    $publication_year = isset($data['publication_year']) ? filter_var($data['publication_year'], FILTER_VALIDATE_INT) : null;

    if (empty($id) || empty($title) || empty($author) || empty($isbn)) {
        $response['message'] = '書籍 ID、書名、作者和 ISBN 為必填欄位。';
        http_response_code(400);
    } else {
        try {
            $pdo = get_db_connection();
            
            $sql = "UPDATE books SET 
                        title = :title, 
                        author = :author, 
                        isbn = :isbn, 
                        publisher = :publisher, 
                        publication_year = :publication_year 
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':author', $author, PDO::PARAM_STR);
            $stmt->bindParam(':isbn', $isbn, PDO::PARAM_STR);
            $stmt->bindParam(':publisher', $publisher, PDO::PARAM_STR);
            if ($publication_year === false || $publication_year === null) {
                $stmt->bindValue(':publication_year', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':publication_year', $publication_year, PDO::PARAM_INT);
            }
            
            if ($stmt->execute()) {
                // rowCount() 可以判斷是否有資料列被影響
                if ($stmt->rowCount() > 0) {
                    $response['status'] = 'success';
                    $response['message'] = '書籍資料更新成功。';
                    http_response_code(200);
                } else {
                    $response['status'] = 'fail';
                    $response['message'] = '找不到該書籍或資料無變更。';
                    http_response_code(404); // Not Found
                }
            } else {
                $response['message'] = '更新書籍失敗。';
                http_response_code(500);
            }

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                 $response['message'] = '錯誤：此 ISBN 已被另一本書使用。';
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
