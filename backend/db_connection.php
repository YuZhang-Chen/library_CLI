<?php
// backend/db_connection.php
require_once 'db_config.php';

/**
 * 建立並返回一個 PDO 資料庫連線物件。
 * 如果連線失敗，會直接輸出 JSON 錯誤訊息並中止程式。
 * @return PDO
 */
function get_db_connection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        return new PDO($dsn, DB_USER, DB_PASS, $options);

    } catch (PDOException $e) {
        // 在生產環境中，應記錄錯誤日誌而不是直接輸出
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => '資料庫連線失敗: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit; // 中止腳本執行
    }
}
?>
