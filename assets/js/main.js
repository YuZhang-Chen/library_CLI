document.addEventListener('DOMContentLoaded', () => {
    // --- STATE MANAGEMENT ---
    const state = {
        currentUser: null,
        books: [],
        bootstrap: {
            addBookModal: null,
            editBookModal: null,
            historyModal: null,
            registerModal: null,
        }
    };

    // --- DOM ELEMENTS ---
    // Views
    const loginView = document.getElementById('login-view');
    const mainView = document.getElementById('main-view');
    const userDashboardView = document.getElementById('user-dashboard-view');
    const adminDashboardView = document.getElementById('admin-dashboard-view');

    // General
    const loginForm = document.getElementById('login-form');
    const loginError = document.getElementById('login-error');
    const userInfo = document.getElementById('user-info');
    const logoutBtn = document.getElementById('logout-btn');
    const historyBtn = document.getElementById('history-btn');
    const showRegisterModalBtn = document.getElementById('show-register-modal-btn');

    // User Dashboard
    const bookList = document.getElementById('book-list');
    const searchInput = document.getElementById('search-input');
    const searchBtn = document.getElementById('search-btn');
    
    // Admin Dashboard
    const adminContentArea = document.getElementById('admin-content-area');
    const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
    const adminNavLinks = {
        dashboard: document.getElementById('admin-nav-dashboard'),
        books: document.getElementById('admin-nav-books'),
        users: document.getElementById('admin-nav-users'),
        records: document.getElementById('admin-nav-records'),
    };

    // Modals
    const addBookModalEl = document.getElementById('add-book-modal');
    const addBookForm = document.getElementById('add-book-form');
    const editBookModalEl = document.getElementById('edit-book-modal');
    const editBookForm = document.getElementById('edit-book-form');
    const historyModalEl = document.getElementById('history-modal');
    const historyTableBody = document.getElementById('history-table-body');
    const registerModalEl = document.getElementById('register-modal');
    const registerForm = document.getElementById('register-form');
    const registerMessage = document.getElementById('register-message');
    

    // --- API HELPERS ---
    const apiCall = async (url, method = 'GET', body = null) => {
        try {
            const options = { method, headers: { 'Content-Type': 'application/json' } };
            if (body) options.body = JSON.stringify(body);
            const response = await fetch(url, options);
            if (!response.ok) {
                const errorResult = await response.json();
                throw new Error(errorResult.message || `HTTP error! status: ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            console.error('API Call Error:', error);
            throw error;
        }
    };

    // --- VIEW MANAGEMENT ---
    const showLoginView = () => {
        loginView.style.display = 'block';
        mainView.style.display = 'none';
    };

    const showMainView = () => {
        loginView.style.display = 'none';
        mainView.style.display = 'block';
        userInfo.textContent = `${state.currentUser.username} (${state.currentUser.role})`;

        if (state.currentUser.role === 'admin') {
            userDashboardView.style.display = 'none';
            adminDashboardView.style.display = 'block';
            historyBtn.style.display = 'none';
            sidebarToggleBtn.style.display = 'block';
            
            // Restore sidebar state
            if (localStorage.getItem('sidebarState') === 'toggled') {
                adminDashboardView.classList.add('sidebar-toggled');
            }

            handleAdminNav(adminNavLinks.dashboard);
            renderAdminDashboard();
        } else {
            userDashboardView.style.display = 'block';
            adminDashboardView.style.display = 'none';
            historyBtn.style.display = 'block';
            sidebarToggleBtn.style.display = 'none';
            fetchAndRenderBooks();
        }
    };

    // --- ADMIN DASHBOARD RENDERERS ---
    const renderAdminDashboard = async () => {
        try {
            const result = await apiCall('backend/admin_stats.php');
            const stats = result.data;
            adminContentArea.innerHTML = `
                <h2 class="mb-4">儀表板</h2>
                <div class="row g-4">
                    <div class="col-md-6 col-xl-3"><div class="card text-bg-primary"><div class="card-body"><h5 class="card-title">${stats.total_books}</h5><p class="card-text">書籍總數</p></div></div></div>
                    <div class="col-md-6 col-xl-3"><div class="card text-bg-secondary"><div class="card-body"><h5 class="card-title">${stats.total_users}</h5><p class="card-text">會員總數</p></div></div></div>
                    <div class="col-md-6 col-xl-3"><div class="card text-bg-warning"><div class="card-body"><h5 class="card-title">${stats.borrowed_books}</h5><p class="card-text">已借出數量</p></div></div></div>
                    <div class="col-md-6 col-xl-3"><div class="card text-bg-success"><div class="card-body"><h5 class="card-title">${stats.available_books}</h5><p class="card-text">可借閱數量</p></div></div></div>
                </div>
            `;
        } catch (error) {
            adminContentArea.innerHTML = `<p class="text-danger">無法載入儀表板資料: ${error.message}</p>`;
        }
    };

    const renderAdminBookManagement = async () => {
        adminContentArea.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>書籍管理</h2>
                <button class="btn btn-primary" id="admin-add-book-btn">新增書籍</button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead><tr><th>ID</th><th>書名</th><th>作者</th><th>ISBN</th><th>狀態</th><th>操作</th></tr></thead>
                    <tbody id="admin-book-table-body"></tbody>
                </table>
            </div>
        `;
        document.getElementById('admin-add-book-btn').addEventListener('click', () => state.bootstrap.addBookModal.show());
        const books = await apiCall('backend/list_books.php').then(res => res.data);
        const bookTableBody = document.getElementById('admin-book-table-body');
        bookTableBody.innerHTML = '';
        books.forEach(book => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${book.id}</td>
                <td>${book.title}</td>
                <td>${book.author}</td>
                <td>${book.isbn}</td>
                <td>${book.status}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary edit-btn" data-id="${book.id}">編輯</button>
                    <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${book.id}">刪除</button>
                </td>
            `;
            bookTableBody.appendChild(row);
        });
    };

    const renderAdminUserManagement = async () => {
        try {
            const result = await apiCall('backend/list_users.php');
            adminContentArea.innerHTML = `
                <h2 class="mb-3">會員管理</h2>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead><tr><th>ID</th><th>使用者名稱</th><th>Email</th><th>角色</th><th>註冊時間</th></tr></thead>
                        <tbody id="admin-user-table-body">
                            ${result.data.map(user => `
                                <tr>
                                    <td>${user.id}</td>
                                    <td>${user.username}</td>
                                    <td>${user.email}</td>
                                    <td>${user.role}</td>
                                    <td>${new Date(user.created_at).toLocaleString()}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        } catch (error) {
            adminContentArea.innerHTML = `<p class="text-danger">無法載入會員列表: ${error.message}</p>`;
        }
    };
    
    const renderAdminBorrowingRecords = async () => {
        try {
            const result = await apiCall('backend/list_records.php');
            adminContentArea.innerHTML = `
                <h2 class="mb-3">借閱紀錄</h2>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead><tr><th>ID</th><th>書名</th><th>借閱人</th><th>借閱日期</th><th>應還日期</th><th>歸還日期</th></tr></thead>
                        <tbody>
                            ${result.data.map(record => `
                                <tr>
                                    <td>${record.id}</td>
                                    <td>${record.book_title}</td>
                                    <td>${record.username}</td>
                                    <td>${new Date(record.borrow_date).toLocaleString()}</td>
                                    <td>${new Date(record.due_date).toLocaleString()}</td>
                                    <td>${record.return_date ? new Date(record.return_date).toLocaleString() : '<em>尚未歸還</em>'}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        } catch (error) {
            adminContentArea.innerHTML = `<p class="text-danger">無法載入借閱紀錄: ${error.message}</p>`;
        }
    };

    // --- AUTHENTICATION & REGISTRATION ---
    const handleLogin = async (event) => {
        event.preventDefault();
        loginError.style.display = 'none';
        const formData = new FormData(loginForm);
        const credentials = Object.fromEntries(formData.entries());

        try {
            const result = await apiCall('backend/login_user.php', 'POST', credentials);
            if (result.status === 'success') {
                state.currentUser = result.data;
                localStorage.setItem('currentUser', JSON.stringify(result.data));
                showMainView();
            }
        } catch (error) {
            loginError.textContent = error.message;
            loginError.style.display = 'block';
        }
    };

    const handleRegister = async (event) => {
        event.preventDefault();
        registerMessage.style.display = 'none';
        const userData = {
            username: document.getElementById('reg-username').value,
            email: document.getElementById('reg-email').value,
            password: document.getElementById('reg-password').value
        };

        try {
            const result = await apiCall('backend/register_user.php', 'POST', userData);
            registerMessage.textContent = result.message;
            registerMessage.className = 'mt-3 text-center text-success';
            registerMessage.style.display = 'block';
            registerForm.reset();
            setTimeout(() => {
                state.bootstrap.registerModal.hide();
            }, 2000);
        } catch (error) {
            registerMessage.textContent = error.message;
            registerMessage.className = 'mt-3 text-center text-danger';
            registerMessage.style.display = 'block';
        }
    };

    const handleLogout = () => {
        state.currentUser = null;
        localStorage.removeItem('currentUser');
        sidebarToggleBtn.style.display = 'none';
        showLoginView();
    };

    // --- SIDEBAR ---
    const handleSidebarToggle = () => {
        adminDashboardView.classList.toggle('sidebar-toggled');
        if (adminDashboardView.classList.contains('sidebar-toggled')) {
            localStorage.setItem('sidebarState', 'toggled');
        } else {
            localStorage.setItem('sidebarState', '');
        }
    };

    // --- USER DASHBOARD BOOK RENDERING ---
    const fetchAndRenderBooks = async (searchTerm = '') => {
        try {
            const url = `backend/list_books.php${searchTerm ? `?search=${encodeURIComponent(searchTerm)}` : ''}`;
            const result = await apiCall(url);
            if (result.status === 'success') {
                state.books = result.data;
                renderBooks();
            }
        } catch (error) {
            bookList.innerHTML = `<div class="col-12"><p class="text-danger">無法載入書籍資料: ${error.message}</p></div>`;
        }
    };

    const renderBooks = () => {
        bookList.innerHTML = '';
        if (state.books.length === 0) {
            bookList.innerHTML = '<div class="col-12"><p class="text-center text-muted">目前無任何書籍資料。</p></div>';
            return;
        }
        state.books.forEach(book => {
            const card = document.createElement('div');
            card.className = 'col-lg-4 col-md-6';
            const statusBadge = book.status === '在庫' ? `<span class="badge bg-success">在庫</span>` : `<span class="badge bg-warning text-dark">已借出</span>`;
            let actionButtons = '';
            if (book.status === '在庫') {
                actionButtons = `<button class="btn btn-sm btn-primary borrow-btn" data-id="${book.id}">借閱</button>`;
            } else {
                if (book.borrower_id == state.currentUser.user_id) {
                     actionButtons = `<button class="btn btn-sm btn-success return-btn" data-id="${book.id}">歸還</button>`;
                } else {
                    actionButtons = `<button class="btn btn-sm btn-secondary" disabled>已借出</button>`;
                }
            }
            card.innerHTML = `<div class="card h-100 shadow-sm"><div class="card-body"><h5 class="card-title">${book.title}</h5><p class="card-subtitle mb-2 text-muted">${book.author}</p><p class="card-text small">ISBN: ${book.isbn}</p></div><div class="card-footer d-flex justify-content-between align-items-center">${statusBadge}<div class="btn-group">${actionButtons}</div></div></div>`;
            bookList.appendChild(card);
        });
    };

    // --- USER HISTORY MODAL ---
    const renderHistory = (records) => {
        historyTableBody.innerHTML = '';
        if (records.length === 0) {
            historyTableBody.innerHTML = `<tr><td colspan="5" class="text-center">無任何借閱紀錄。</td></tr>`;
            return;
        }
        records.forEach(record => {
            const row = document.createElement('tr');
            row.innerHTML = `<td>${record.id}</td><td>${record.book_title}</td><td>${new Date(record.borrow_date).toLocaleString()}</td><td>${new Date(record.due_date).toLocaleString()}</td><td>${record.return_date ? new Date(record.return_date).toLocaleString() : '<em>尚未歸還</em>'}</td>`;
            historyTableBody.appendChild(row);
        });
    };

    const handleShowHistory = async () => {
        try {
            const result = await apiCall('backend/list_records.php');
            if (result.status === 'success') {
                document.querySelectorAll('.borrower-col').forEach(el => el.style.display = 'none');
                renderHistory(result.data);
                state.bootstrap.historyModal.show();
            }
        } catch (error) {
            alert(`無法獲取歷史紀錄: ${error.message}`);
        }
    };

    // --- BOOK ACTIONS (CRUD, BORROW, RETURN) ---
    const handleAddBook = async (event) => {
        event.preventDefault();
        const bookData = {
            title: document.getElementById('add-title').value,
            author: document.getElementById('add-author').value,
            isbn: document.getElementById('add-isbn').value,
            publisher: document.getElementById('add-publisher').value,
            publication_year: document.getElementById('add-publication_year').value,
        };
        try {
            await apiCall('backend/create_book.php', 'POST', bookData);
            state.bootstrap.addBookModal.hide();
            renderAdminBookManagement(); // Refresh the admin book table
        } catch (error) {
            alert(`新增書籍失敗: ${error.message}`);
        }
    };
    
    const handleEditBook = async (event) => {
        event.preventDefault();
        const bookData = {
            id: document.getElementById('edit-book-id').value,
            title: document.getElementById('edit-title').value,
            author: document.getElementById('edit-author').value,
            isbn: document.getElementById('edit-isbn').value,
            publisher: document.getElementById('edit-publisher').value,
            publication_year: document.getElementById('edit-publication_year').value,
        };
        try {
            await apiCall('backend/update_book.php', 'POST', bookData);
            state.bootstrap.editBookModal.hide();
            renderAdminBookManagement(); // Refresh the admin book table
        } catch (error) {
            alert(`更新書籍失敗: ${error.message}`);
        }
    };
    
    const handleDeleteBook = async (bookId) => {
        if (!confirm('確定要刪除這本書嗎？此操作無法復原。')) return;
        try {
            await apiCall('backend/delete_book.php', 'POST', { id: bookId });
            renderAdminBookManagement(); // Refresh the admin book table
        } catch (error) {
            alert(`刪除書籍失敗: ${error.message}`);
        }
    };

    const handleBorrowBook = async (bookId) => {
        try {
            const result = await apiCall('backend/borrow_book.php', 'POST', { book_id: bookId });
            alert(result.message);
            fetchAndRenderBooks(); // Refresh user book list
        } catch (error) {
            alert(`借閱失敗: ${error.message}`);
        }
    };

    const handleReturnBook = async (bookId) => {
        try {
            const result = await apiCall('backend/return_book.php', 'POST', { book_id: bookId });
            alert(result.message);
            fetchAndRenderBooks(); // Refresh user book list
        } catch (error) {
            alert(`歸還失敗: ${error.message}`);
        }
    };

    const openEditModal = (bookId) => {
        apiCall(`backend/list_books.php`).then(result => {
             const book = result.data.find(b => b.id == bookId);
             if (!book) return;
            document.getElementById('edit-book-id').value = book.id;
            document.getElementById('edit-title').value = book.title;
            document.getElementById('edit-author').value = book.author;
            document.getElementById('edit-isbn').value = book.isbn;
            document.getElementById('edit-publisher').value = book.publisher;
            document.getElementById('edit-publication_year').value = book.publication_year;
            state.bootstrap.editBookModal.show();
        });
    };

    // --- EVENT LISTENERS ---
    loginForm.addEventListener('submit', handleLogin);
    logoutBtn.addEventListener('click', handleLogout);
    searchBtn.addEventListener('click', () => fetchAndRenderBooks(searchInput.value));
    searchInput.addEventListener('keyup', (e) => {
        if (e.key === 'Enter') fetchAndRenderBooks(searchInput.value);
    });
    historyBtn.addEventListener('click', handleShowHistory);
    addBookForm.addEventListener('submit', handleAddBook);
    editBookForm.addEventListener('submit', handleEditBook);
    sidebarToggleBtn.addEventListener('click', handleSidebarToggle);
    showRegisterModalBtn.addEventListener('click', (e) => {
        e.preventDefault();
        state.bootstrap.registerModal.show();
    });
    registerForm.addEventListener('submit', handleRegister);


    // User dashboard event delegation
    bookList.addEventListener('click', (e) => {
        const target = e.target;
        if (target.classList.contains('borrow-btn')) handleBorrowBook(target.dataset.id);
        if (target.classList.contains('return-btn')) handleReturnBook(target.dataset.id);
    });

    // Admin dashboard event delegation
    adminContentArea.addEventListener('click', (e) => {
        const target = e.target;
        if (target.classList.contains('edit-btn')) openEditModal(target.dataset.id);
        if (target.classList.contains('delete-btn')) handleDeleteBook(target.dataset.id);
    });

    // Admin navigation
    const handleAdminNav = (target) => {
        Object.values(adminNavLinks).forEach(link => link.classList.remove('active'));
        target.classList.add('active');
    };
    adminNavLinks.dashboard.addEventListener('click', (e) => { e.preventDefault(); handleAdminNav(e.target); renderAdminDashboard(); });
    adminNavLinks.books.addEventListener('click', (e) => { e.preventDefault(); handleAdminNav(e.target); renderAdminBookManagement(); });
    adminNavLinks.users.addEventListener('click', (e) => { e.preventDefault(); handleAdminNav(e.target); renderAdminUserManagement(); });
    adminNavLinks.records.addEventListener('click', (e) => { e.preventDefault(); handleAdminNav(e.target); renderAdminBorrowingRecords(); });


    // --- INITIALIZATION ---
    const init = () => {
        state.bootstrap.addBookModal = new bootstrap.Modal(addBookModalEl);
        state.bootstrap.editBookModal = new bootstrap.Modal(editBookModalEl);
        state.bootstrap.historyModal = new bootstrap.Modal(historyModalEl);
        state.bootstrap.registerModal = new bootstrap.Modal(registerModalEl);
        const savedUser = localStorage.getItem('currentUser');
        if (savedUser) {
            state.currentUser = JSON.parse(savedUser);
            showMainView();
        } else {
            showLoginView();
        }
    };

    init();
});
