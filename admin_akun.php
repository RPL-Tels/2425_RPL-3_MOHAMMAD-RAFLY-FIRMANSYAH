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
$user_id = $_SESSION['user_id'];
$stmtUser = $pdo->prepare("SELECT nip, nama, role, jabatan FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Get current date for display
$current_date = date('l, d F Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Saya</title>
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

        /* Profile Card Styling */
        .profile-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }

        .profile-card {
            background-color: var(--card-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
            animation: fadeIn 0.5s ease;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 2rem;
            color: white;
            text-align: center;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: var(--shadow);
        }

        .profile-avatar i {
            font-size: 3.5rem;
            color: var(--primary-color);
        }

        .profile-name {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .profile-role {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .profile-details {
            padding: 2rem;
        }

        .profile-item {
            display: flex;
            margin-bottom: 1.25rem;
            align-items: center;
        }

        .profile-item:last-child {
            margin-bottom: 0;
        }

        .profile-item i {
            font-size: 1.25rem;
            color: var(--primary-color);
            margin-right: 1rem;
            width: 1.25rem;
            text-align: center;
        }

        .profile-item-content {
            flex: 1;
        }

        .profile-item-label {
            font-size: 0.875rem;
            color: var(--text-light);
            margin-bottom: 0.25rem;
        }

        .profile-item-value {
            font-size: 1.125rem;
            font-weight: 500;
            color: var(--text-dark);
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
        <a href="admin_datauser.php">
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
            <a href="admin_akun.php" class="active">
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
                <h2><i class="fas fa-user-circle"></i> My Account</h2>
                <span><?php echo $current_date; ?></span>
            </div>
            
            <div class="profile-container">
                <div class="profile-card">
                <div class="profile-header">
    <div class="profile-avatar">
        <i class="fas fa-user"></i>
    </div>
    <h2 class="profile-name"><?php echo htmlspecialchars($userData['nama']); ?></h2>
    <!-- Status below name is removed -->
</div>

<div class="profile-details">
    <div class="profile-item">
        <i class="fas fa-id-badge"></i>
        <div class="profile-item-content">
            <div class="profile-item-label">NIP</div>
            <div class="profile-item-value"><?php echo htmlspecialchars($userData['nip']); ?></div>
        </div>
    </div>
    
    <div class="profile-item">
        <i class="fas fa-user-tag"></i>
        <div class="profile-item-content">
            <div class="profile-item-label">Jabatan</div>
            <div class="profile-item-value"><?php echo htmlspecialchars($userData['jabatan']); ?></div>
        </div>
    </div>
    
    <div class="profile-item">
        <i class="fas fa-shield-alt"></i>
        <div class="profile-item-content">
            <div class="profile-item-label">Status</div>
            <div class="profile-item-value"><?php echo htmlspecialchars($userData['role']); ?></div>
        </div>
    </div>
</div>

    <script>
        function confirmLogout() {
            return confirm("Apakah Anda yakin ingin keluar?");
        }
    </script>
</body>
</html>