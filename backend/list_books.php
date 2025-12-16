<?php
// backend/list_books.php
session_start();
header('Content-Type: application/json');
require_once 'db_connection.php';

$response = ['status' => 'error', 'message' => '發生錯誤。', 'data' => []];

try {
    $pdo = get_db_connection();

    $search_term = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);

    // SQL query now joins with borrowing_records to find out who borrowed the book, if anyone.
    $sql = "
        SELECT 
            b.*, 
            br.user_id AS borrower_id 
        FROM 
            books b
        LEFT JOIN 
            borrowing_records br ON b.id = br.book_id AND br.return_date IS NULL
    ";

    if ($search_term) {
        $sql .= " WHERE b.title LIKE :search OR b.author LIKE :search";
    }

    $sql .= " ORDER BY b.created_at DESC";
    
    $stmt = $pdo->prepare($sql);

    if ($search_term) {
        $stmt->bindValue(':search', '%' . $search_term . '%', PDO::PARAM_STR);
    }

    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['status'] = 'success';
    $response['message'] = '書籍列表獲取成功。';
    $response['data'] = $books;
    http_response_code(200);

} catch (PDOException $e) {
    $response['message'] = '資料庫錯誤: ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
