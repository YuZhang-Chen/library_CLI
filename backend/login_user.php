<?php
// backend/login_user.php

// 為了使用 $_SESSION，必須在任何輸出之前啟動 session
// 這將會自動處理客戶端的 cookie
session_start();

header('Content-Type: application/json');
require_once 'db_connection.php';

$response = ['status' => 'error', 'message' => '無效的請求。'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
        $response['message'] = '使用者名稱和密碼為必填欄位。';
        http_response_code(400);
    } else {
        try {
            $pdo = get_db_connection();

            $sql = "SELECT id, username, password, role FROM users WHERE username = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            $user = $stmt->fetch();

            // 驗證使用者是否存在，並使用 password_verify 檢查密碼雜湊值
            if ($user && password_verify($password, $user['password'])) {
                
                // 為了安全性，登入成功後重新生成 session ID
                session_regenerate_id(true);

                // 將使用者資訊存入 session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                $response['status'] = 'success';
                $response['message'] = '登入成功。';
                // 同時返回使用者資訊給前端，方便前端更新介面
                $response['data'] = [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ];
                http_response_code(200);

            } else {
                $response['message'] = '使用者名稱或密碼錯誤。';
                http_response_code(401); // Unauthorized
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
