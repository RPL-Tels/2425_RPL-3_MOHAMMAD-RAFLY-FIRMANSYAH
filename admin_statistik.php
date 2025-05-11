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

$monthFilter = date('Y-m');
if (isset($_GET['month']) && !empty($_GET['month'])) {
    $monthFilter = $_GET['month'];
}

$queryRoomStats = "
    SELECT room, COUNT(*) AS total_bookings
    FROM bookings
    WHERE DATE_FORMAT(date, '%Y-%m') = :monthFilter
    AND status = 'approved' -- Hanya menyertakan booking yang disetujui
    GROUP BY room
    ORDER BY total_bookings DESC
";

$queryFieldStats = "
    SELECT field, COUNT(*) AS total_bookings
    FROM bookings
    WHERE DATE_FORMAT(date, '%Y-%m') = :monthFilter
    AND status = 'approved' -- Hanya menyertakan booking yang disetujui
    GROUP BY field
    ORDER BY total_bookings DESC
";

$stmtRoomStats = $pdo->prepare($queryRoomStats);
$stmtFieldStats = $pdo->prepare($queryFieldStats);

$stmtRoomStats->bindParam(':monthFilter', $monthFilter);
$stmtFieldStats->bindParam(':monthFilter', $monthFilter);

$stmtRoomStats->execute();
$stmtFieldStats->execute();

$roomStats = $stmtRoomStats->fetchAll(PDO::FETCH_ASSOC);
$fieldStats = $stmtFieldStats->fetchAll(PDO::FETCH_ASSOC);

// Get current date for display
$current_date = date('l, d F Y');

// Get month and year for display
$monthName = date('F Y', strtotime($monthFilter . '-01'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Ruangan - MITRAS DUDI</title>
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

        .main-statistics {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        .container-statistics {
            max-width: 1200px;
            margin: 0 auto;
        }

        .dashboard {
            background-color: var(--card-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            animation: fadeIn 0.5s ease;
        }

        .dashboard h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dashboard h2 i {
            color: var(--primary-color);
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
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .page-header h2 i {
            color: var(--primary-color);
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            background-color: var(--background-color);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 24px;
            align-items: center;
            border: 1px solid var(--border-color);
        }

        .filter-form label {
            font-size: 0.95rem;
            color: var(--text-dark);
            font-weight: 500;
            margin-right: 10px;
        }

        .filter-form input[type="month"] {
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            color: var(--text-dark);
            transition: border-color 0.3s, box-shadow 0.3s;
            background-color: white;
            margin-right: 10px;
        }

        .filter-form input[type="month"]:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            outline: none;
        }

        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 500;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            box-shadow: var(--shadow-md);
        }

        .stats-section {
            margin-top: 2rem;
            padding: 1.5rem;
            background-color: var(--card-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .stats-section h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stats-section h3 i {
            color: var(--primary-color);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .stats-card {
            background-color: var(--background-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1.25rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 180px;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .stats-card.room {
            background-color: rgba(67, 97, 238, 0.05);
            border-left: 3px solid var(--primary-color);
        }

        .stats-card.field {
            background-color: rgba(76, 201, 240, 0.05);
            border-left: 3px solid var(--accent-color);
        }

        .stats-card-icon {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            color: var(--primary-color);
        }

        .stats-card.field .stats-card-icon {
            color: var(--accent-color);
        }

        .stats-card-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .stats-card-count {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stats-card.field .stats-card-count {
            color: var(--accent-color);
        }

        .stats-card-label {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-top: 0.25rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .stats-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stats-filter {
            font-weight: 500;
            color: var(--text-light);
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
            
            .main-statistics {
                margin-left: 80px;
            }
        }

        @media (max-width: 768px) {
            .main-statistics {
                padding: 1rem;
            }
            
            .dashboard {
                padding: 1rem;
            }
            
            .filter-form {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 1rem;
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
        <a href="admin_statistik.php" class="active">
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

    <div class="main-statistics">
        <div class="container-statistics">
            <div class="dashboard animate__animated animate__fadeIn">
                <div class="page-header">
                    <h2><i class="fas fa-chart-bar"></i> Statistik Penggunaan Ruangan</h2>
                    <span><?php echo $current_date; ?></span>
                </div>

                <form method="GET" action="admin_statistik.php" class="filter-form">
                    <label for="month"><i class="fas fa-calendar-alt"></i> Pilih Bulan:</label>
                    <input type="month" id="month" name="month" value="<?php echo htmlspecialchars($monthFilter); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Tampilkan
                    </button>
                </form>

                <div class="stats-section">
                    <div class="stats-header">
                        <h3><i class="fas fa-door-open"></i> Statistik Penggunaan Ruangan</h3>
                        <div class="stats-filter">Period: <strong><?php echo $monthName; ?></strong></div>
                    </div>
                    
                    <?php if (count($roomStats) > 0): ?>
                    <div class="stats-grid">
                        <?php foreach ($roomStats as $room): ?>
                        <div class="stats-card room">
                            <div class="stats-card-icon">
                                <i class="fas fa-door-open"></i>
                            </div>
                            <div class="stats-card-title"><?php echo htmlspecialchars($room['room']); ?></div>
                            <div class="stats-card-count"><?php echo htmlspecialchars($room['total_bookings']); ?></div>
                            <div class="stats-card-label">Pemesan</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <p>Tidak ditemukan data pemesanan ruangan untuk periode ini</p>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="stats-section">
                    <div class="stats-header">
                        <h3><i class="fas fa-users"></i> Statistik Penggunaan per Bidang</h3>
                        <div class="stats-filter">Period: <strong><?php echo $monthName; ?></strong></div>
                    </div>
                    
                    <?php if (count($fieldStats) > 0): ?>
                    <div class="stats-grid">
                        <?php foreach ($fieldStats as $field): ?>
                        <div class="stats-card field">
                            <div class="stats-card-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stats-card-title"><?php echo htmlspecialchars($field['field']); ?></div>
                            <div class="stats-card-count"><?php echo htmlspecialchars($field['total_bookings']); ?></div>
                            <div class="stats-card-label">Pesanan</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <p>Tidak ditemukan data pemesanan untuk periode ini</p>
                    </div>
                    <?php endif; ?>
                </div>
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