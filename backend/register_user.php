<?php
// backend/register_user.php
header('Content-Type: application/json');
require_once 'db_connection.php';

$response = ['status' => 'error', 'message' => '無效的請求。'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $username = isset($data['username']) ? trim($data['username']) : '';
    $email = isset($data['email']) ? trim($data['email']) : '';
    $password = $data['password'] ?? '';

    // 基本驗證
    if (empty($username) || empty($email) || empty($password)) {
        $response['message'] = '使用者名稱、電子郵件和密碼為必填欄位。';
        http_response_code(400);
    } elseif (strlen($password) < 6) {
        $response['message'] = '密碼長度至少需要 6 個字元。';
        http_response_code(400);
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = '無效的電子郵件格式。';
        http_response_code(400);
    } else {
        try {
            $pdo = get_db_connection();

            // 使用 password_hash 對密碼進行安全的加密
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password_hash)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password_hash', $password_hash, PDO::PARAM_STR);
            
            $stmt->execute();

            $response['status'] = 'success';
            $response['message'] = '使用者註冊成功。';
            http_response_code(201); // Created

        } catch (PDOException $e) {
            // 檢查是否為重複鍵值的錯誤 (username 或 email 已存在)
            if ($e->getCode() == 23000) {
                 $response['message'] = '此使用者名稱或電子郵件已被註冊。';
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
