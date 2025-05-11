<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$host = 'localhost';
$db = 'projek_ruangan';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi ke database gagal: " . $e->getMessage());
}
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total users
$user_count = count($users);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User - Admin Panel</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --success-color: #4ade80;
            --danger-color: #f43f5e;
            --warning-color: #facc15;
            --info-color: #60a5fa;
            --background-color: #f8fafc;
            --card-color: #ffffff;
            --text-dark: #1e293b;
            --text-light: #64748b;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --sidebar-width: 250px;
            --header-height: 60px;
            --border-radius: 8px;
            --transition-speed: 0.3s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--card-color);
            box-shadow: var(--shadow);
            padding: 1.5rem 0;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all var(--transition-speed) ease;
            z-index: 100;
        }

        .logo-area {
            padding: 0 1.5rem 1.5rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 1rem;
        }

        .logo-area img {
            height: 40px;
        }

        .sidebar a {
            padding: 0.875rem 1.5rem;
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: all var(--transition-speed) ease;
            position: relative;
        }

        .sidebar a i {
            margin-right: 1rem;
            font-size: 1.25rem;
            width: 1.25rem;
            text-align: center;
            color: var(--text-light);
            transition: all var(--transition-speed) ease;
        }

        .sidebar a.active {
            background-color: rgba(67, 97, 238, 0.1);
            border-left: 3px solid var(--primary-color);
            color: var(--primary-color);
        }

        .sidebar a.active i {
            color: var(--primary-color);
        }

        .sidebar a:hover {
            background-color: rgba(67, 97, 238, 0.05);
            color: var(--primary-color);
        }

        .sidebar a:hover i {
            color: var(--primary-color);
        }

        .bottom-links {
            margin-top: auto;
            border-top: 1px solid var(--border-color);
            padding-top: 1rem;
        }

        .main-admin {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        .container-admin {
            background-color: var(--card-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .page-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .page-header h2 i {
            color: var(--primary-color);
        }

        .user-count {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
        }

        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            animation: fadeIn 0.5s ease;
        }

        .alert i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }

        .alert-success {
            background-color: rgba(74, 222, 128, 0.1);
            color: #16a34a;
        }

        .alert-error {
            background-color: rgba(244, 63, 94, 0.1);
            color: #e11d48;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .users-table thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            text-align: left;
            padding: 1rem;
            font-size: 0.875rem;
            position: relative;
        }

        .users-table th:after {
            content: '';
            position: absolute;
            right: 0;
            top: 25%;
            height: 50%;
            width: 1px;
            background-color: rgba(255, 255, 255, 0.2);
        }

        .users-table th:last-child:after {
            display: none;
        }

        .users-table tbody tr {
            background-color: var(--card-color);
            transition: all var(--transition-speed) ease;
        }

        .users-table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .users-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.9375rem;
            color: var(--text-dark);
        }

        .users-table tr:last-child td {
            border-bottom: none;
        }

        .users-table tbody tr:nth-child(even) {
            background-color: rgba(241, 245, 249, 0.5);
        }

        .action-cell {
            width: 150px;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .btn-edit {
            background-color: var(--info-color);
            color: white;
        }

        .btn-edit:hover {
            background-color: #3b82f6;
        }

        .btn-delete {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-delete:hover {
            background-color: #e11d48;
        }

        .add-user-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }

        .password-cell {
            font-family: monospace;
            letter-spacing: 1px;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 0;
            text-align: center;
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--text-light);
            max-width: 400px;
        }

        .search-filter {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .search-input {
            flex: 1;
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            transition: all var(--transition-speed) ease;
        }

        .search-input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }
            
            .sidebar a span {
                display: none;
            }
            
            .sidebar a i {
                margin-right: 0;
                font-size: 1.5rem;
            }
            
            .logo-area {
                padding: 0 1rem 1rem 1rem;
            }
            
            .main-admin {
                margin-left: 80px;
            }
        }

        @media (max-width: 768px) {
            .main-admin {
                padding: 1rem;
            }
            
            .container-admin {
                padding: 1rem;
            }
            
            .users-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .search-filter {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-area">
            <img src="gambar/LOGO 1 MITRAS.jpg" alt="Logo">
        </div>
        <a href="admin_home.php">
            <i class="fas fa-home"></i> <span>Home</span>
        </a>
        <a href="admin_approve.php">
            <i class="fas fa-check-circle"></i> <span>Approve</span>
        </a>
        <a href="admin_datauser.php" class="active">
            <i class="fas fa-id-card"></i> <span>User Data</span>
        </a>
        <a href="admin_jadwal.php">
            <i class="fas fa-calendar"></i> <span>Jadwal</span>
        </a>
        <a href="admin_statistik.php">
            <i class="fas fa-chart-bar"></i> <span>Statistik</span>
        </a>
        <a href="admin_ruang.php">
            <i class="fas fa-door-closed"></i> <span>Ruangan</span>
        </a>
        <div class="bottom-links">
            <a href="admin_akun.php">
                <i class="fas fa-user-shield"></i> <span>My Account</span>
            </a>
            <a href="logout.php" onclick="return confirmLogout();">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </div>
    </div>

    <div class="main-admin">
        <div class="container-admin">
            <div class="page-header">
                <h2><i class="fas fa-users"></i> User Data </h2>
                <span class="user-count"><?php echo $user_count; ?> User</span>
            </div>
            
            <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                    <i class="fas fa-<?php echo $_SESSION['message_type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $_SESSION['message']; ?>
                </div>
                <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
            <?php endif; ?>
            
            <div class="search-filter">
                <input type="text" id="searchInput" class="search-input" placeholder="Search user by name or NIP...">
            </div>
            
            <?php if(count($users) > 0): ?>
                <table class="users-table">
                <thead>
                        <tr>
                            <th>NIP</th>
                            <th>Nama</th>
                            <th>Jabatan</th>  <!-- Add this new column -->
                            <th>Password</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
    <?php foreach ($users as $row): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['nip']); ?></td>
            <td><?php echo htmlspecialchars($row['nama']); ?></td>
            <td><?php echo htmlspecialchars($row['jabatan']); ?></td>  <!-- Add this new cell -->
            <td class="password-cell">
                <?php echo str_repeat('•', 8); ?>
            </td>
            <td>
                <?php if ($row['is_admin'] == 1): ?>
                    <span style="color: var(--primary-color); font-weight: 500;">Admin</span>
                <?php else: ?>
                    <span style="color: var(--text-light);">User</span>
                <?php endif; ?>
            </td>
            <td class="action-cell">
                <div class="action-buttons">
                    <a href="pegawai_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="pegawai_hapus.php?id=<?php echo $row['id']; ?>" class="btn btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                        <i class="fas fa-trash-alt"></i> Hapus
                    </a>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>Belum Ada User</h3>
                    <p>Belum ada user yang terdaftar dalam sistem. Silakan tambahkan user baru.</p>
                </div>
            <?php endif; ?>
            
            <div class="add-user-container">
                <a href="pegawai_tambah.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Tambah User
                </a>
            </div>
        </div>
    </div>

    <script>
        function confirmLogout() {
            return confirm("Apakah Anda yakin ingin keluar?");
        }
        
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const rows = document.getElementById('userTableBody').getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const nip = rows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
        const nama = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
        const jabatan = rows[i].getElementsByTagName('td')[2].textContent.toLowerCase();
        
        if (nip.includes(searchValue) || nama.includes(searchValue)) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
});
        </script>
</body>
</html>