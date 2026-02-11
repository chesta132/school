<?php
$page_title = 'User Management';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth(); // All authenticated users can access

require_once __DIR__ . '/includes/header.php';
?>

<div class="users-container">
    <div class="page-header">
        <div>
            <h1>Manajemen User</h1>
            <p>Lihat kasir yang terdaftar</p>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="search-filter-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari user..." onkeyup="searchUsers()">
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Tanggal Daftar</th>
                            <th>Update Terakhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/modal.js"></script>
<script src="/assets/js/notification.js"></script>
<script>
    let allUsers = [];
    
    // Load all users
    async function loadUsers() {
        try {
            const response = await fetch('/api/get-users.php');
            const data = await response.json();
            
            if (data.success) {
                allUsers = data.users;
                displayUsers(allUsers);
            }
        } catch (error) {
            console.error('Error loading users:', error);
            Notification.show({ message: 'Gagal memuat data user', type: 'error' });
        }
    }
    
    // Display users in table
    function displayUsers(users) {
        const tbody = document.querySelector('#usersTable tbody');
        
        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada user</td></tr>';
            return;
        }
        
        tbody.innerHTML = users.map(user => `
            <tr>
                <td><code>${user.id}</code></td>
                <td style="white-space: nowrap;">${user.username}</td>
                <td style="white-space: nowrap;">${user.email}</td>
                <td style="white-space: nowrap;">${formatDateTime(user.created_at)}</td>
                <td style="white-space: nowrap;">${formatDateTime(user.updated_at)}</td>
            </tr>
        `).join('');
    }
    
    // Search users
    function searchUsers() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        
        let filtered = allUsers.filter(user => {
            const matchesSearch = 
                user.username.toLowerCase().includes(query) ||
                user.email.toLowerCase().includes(query) ||
                user.id.toString().includes(query);
            
            
            return matchesSearch;
        });
        
        displayUsers(filtered);
    }
    
    function formatDateTime(datetime) {
        const date = new Date(datetime);
        return date.toLocaleString('id-ID', { 
            day: '2-digit', 
            month: 'short',
            year: 'numeric',
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }
    
    // Load users on page load
    loadUsers();
</script>

<?php
$additional_scripts = [];
require_once __DIR__ . '/includes/footer.php';
?>
