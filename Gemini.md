# Project Context: 圖書管理系統 (Library Management System)

## 1. 專案概述 (Project Overview)
本專案為一個「圖書管理系統」，核心目標是透過 **前後端分離 (Decoupled Architecture)** 的架構，實現書籍管理、借閱紀錄與搜尋功能。

## 2. 技術棧 (Tech Stack)
- **Frontend**: 純 HTML5, CSS3, Vanilla JavaScript (ES6+)。
- **Backend**: PHP 8.x (純 PHP，不使用大型框架)。
- **Database**: MySQL。
- **Communication**: 前端透過 `Fetch API` 與後端 RESTful API 進行 JSON 數據交換。

## 3. 開發必要規則 (Mandatory Rules)
- **前後端分離**: 
    - PHP 僅負責資料邏輯與資料庫互動，**禁止**在 PHP 中撰寫 HTML 或使用 `echo` 輸出 JS 代碼。
    - 所有後端回應必須是 `header('Content-Type: application/json')` 格式。
- **前端標準**:
    - 嚴格分離 HTML 結構、CSS 樣式與 JS 邏輯。
    - 不使用 jQuery 或其他前端框架，保持程式碼輕量化。
- **程式碼規範**:
    - PHP 變數與函式使用 `snake_case`。
    - JavaScript 變數與函式使用 `camelCase`。
    - API 路由設計需符合 RESTful 原則 (例如: GET `/api/books.php` 獲取清單)。

## 4. 核心功能需求 (Core Features)
1. **書籍管理**: 實現 CRUD (新增、讀取、修改、刪除) 功能。
2. **即時搜尋**: 前端透過 JS 監聽輸入框，即時向後端請求過濾後的書籍資料。
3. **借閱系統**: 紀錄書籍狀態（在庫/已借出）及借閱人資訊。

## 5. 目錄架構參考 (Project Structure)
- `/api` : 存放所有 PHP API 檔案 (例如 `list_books.php`, `add_book.php`)。
- `/assets` : 存放 CSS 與 JS 檔案。
- `/index.html` : 系統主介面。
- `db_config.php` : 資料庫連線設定。

## 6. Environment Setup (環境設定)
- **Runtime**: Local XAMPP / Apache Server.
- **PHP Version**: 8.x (Local).
- **Database Host**: `localhost` (127.0.0.1).
- **Project Path**: `C:\xampp\htdocs\db_project` (或是你的實際路徑).

## 7. 請用中文與使用者進行對話