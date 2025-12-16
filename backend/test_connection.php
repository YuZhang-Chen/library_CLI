<?php
// backend/test_connection.php

// 設定回應標頭為 JSON 格式
header('Content-Type: application/json');

require_once 'db_config.php';

$response = [];

try {
    // 建立 DSN (Data Source Name)
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    // 設定 PDO 連線選項
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // 發生錯誤時拋出例外
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // 結果集以關聯陣列形式返回
        PDO::ATTR_EMULATE_PREPARES   => false,                  // 禁用模擬預處理，使用原生預處理
    ];
    
    // 建立 PDO 物件
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // 設定成功回應
    $response['status'] = 'success';
    $response['message'] = '資料庫連線成功！';
    http_response_code(200);
    
} catch (PDOException $e) {
    // 設定失敗回應
    $response['status'] = 'error';
    $response['message'] = '資料庫連線失敗: ' . $e->getMessage();
    // 在正式環境中，不應將詳細錯誤訊息直接顯示給使用者
    http_response_code(500);
}

// 將回應編碼為 JSON 字串並輸出
echo json_encode($response);
?>
