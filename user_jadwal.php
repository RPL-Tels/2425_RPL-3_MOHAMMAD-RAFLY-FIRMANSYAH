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

$dateFilter = '';
$query = "
    SELECT bookings.room, bookings.date, bookings.time_start, bookings.time_end, bookings.field, bookings.status, bookings.activity, users.nama, rejection_log.reason 
    FROM bookings 
    JOIN users ON bookings.user_id = users.id
    LEFT JOIN rejection_log ON bookings.id = rejection_log.booking_id
";

if (isset($_GET['date']) && !empty($_GET['date'])) {
    $dateFilter = $_GET['date'];
    $query .= " WHERE bookings.date = :date ";
}

$query .= " ORDER BY bookings.date";

$stmt = $pdo->prepare($query);

if ($dateFilter) {
    $stmt->bindParam(':date', $dateFilter);
}

$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get current date for display
$current_date = date('l, d F Y');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule</title>
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

        .main-schedule {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        .container-schedule {
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

        .filter-form {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background-color: rgba(67, 97, 238, 0.05);
            border-radius: var(--border-radius);
        }

        .filter-form label {
            margin-right: 1rem;
            font-weight: 500;
        }

        .filter-form input[type="date"] {
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            margin-right: 1rem;
            font-size: 1rem;
        }

        .filter-form button {
            padding: 0.5rem 1rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color var(--transition-speed) ease;
        }

        .filter-form button:hover {
            background-color: var(--secondary-color);
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: rgba(67, 97, 238, 0.05);
            font-weight: 600;
            color: var(--text-dark);
        }

        tr:hover {
            background-color: rgba(67, 97, 238, 0.02);
        }

        .view-btn {
            padding: 0.5rem 1rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color var(--transition-speed) ease;
        }

        .view-btn:hover {
            background-color: var(--secondary-color);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: var(--card-color);
            margin: 10% auto;
            padding: 2rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            width: 80%;
            max-width: 600px;
            box-shadow: var(--shadow-lg);
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-content h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-content p {
            margin: 0.75rem 0;
            display: flex;
            align-items: center;
        }

        .modal-content p i {
            color: var(--primary-color);
            margin-right: 0.5rem;
            width: 20px;
        }

        .close {
            color: var(--text-light);
            float: right;
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
            transition: color var(--transition-speed) ease;
        }

        .close:hover {
            color: var(--danger-color);
        }

        .rejection-reason {
            background-color: rgba(244, 63, 94, 0.1);
            padding: 0.75rem;
            border-radius: var(--border-radius);
            border-left: 3px solid var(--danger-color);
        }

        .no-data {
            text-align: center;
            padding: 2rem;
            color: var(--text-light);
            font-style: italic;
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
            
            .main-schedule {
                margin-left: 80px;
            }
        }

        @media (max-width: 768px) {
            .main-schedule {
                padding: 1rem;
            }
            
            .container-schedule {
                padding: 1rem;
            }
            
            .filter-form {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-form label,
            .filter-form input[type="date"],
            .filter-form button {
                margin-bottom: 0.5rem;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-area">
            <img src="gambar/LOGO 1 MITRAS.jpg" alt="Logo">
        </div>
        <a href="user_home.php">
            <i class="fas fa-home"></i> <span>Home</span>
        </a>
        <a href="user_pesan.php">
            <i class="fas fa-calendar-plus"></i> <span>Pesan</span>
        </a>
        <a href="user_jadwal.php" class="active">
            <i class="fas fa-calendar"></i> <span>Jadwal</span>
        </a>
        <a href="user_ruang.php">
            <i class="fas fa-door-closed"></i> <span>Ruangan</span>
        </a>
        <div class="bottom-links">
            <a href="user_akun.php">
                <i class="fas fa-user-shield"></i> <span>My Account</span>
            </a>
            <a href="logout.php" onclick="return confirmLogout();">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </div>
    </div>

    <div class="main-schedule">
        <div class="container-schedule">
            <div class="page-header">
                <h2><i class="fas fa-calendar"></i> Jadwal Ruangan</h2>
                <span><?php echo $current_date; ?></span>
            </div>
            
            <form method="GET" action="user_jadwal.php" class="filter-form">
                <label for="date"><i class="fas fa-filter"></i> Cari Berdasarkan Tanggal:</label>
                <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($dateFilter); ?>">
                <button type="submit"><i class="fas fa-search"></i> Cari</button>
            </form>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-door-open"></i> Ruang</th>
                            <th><i class="fas fa-calendar-day"></i> Tanggal</th>
                            <th><i class="fas fa-tag"></i> Bidang</th>
                            <th><i class="fas fa-eye"></i> Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($bookings) > 0): ?>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($booking['room']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['date']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['field']); ?></td>
                                    <td>
                                        <button class="view-btn" onclick="openModal(<?php echo htmlspecialchars(json_encode($booking)); ?>)">
                                            <i class="fas fa-info-circle"></i> Lihat Detail
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="no-data">
                                    <i class="fas fa-calendar-times"></i> Tidak ada pemesanan yang ditemukan untuk tanggal yang dipilih.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2><i class="fas fa-info-circle"></i> Detail Pesanan</h2>
            <p id="modal-room"><i class="fas fa-door-open"></i> <span></span></p>
            <p id="modal-date"><i class="fas fa-calendar-day"></i> <span></span></p>
            <p id="modal-time"><i class="fas fa-clock"></i> <span></span></p>
            <p id="modal-field"><i class="fas fa-tag"></i> <span></span></p>
            <p id="modal-status"><i class="fas fa-check-circle"></i> <span></span></p>
            <p id="modal-orderer"><i class="fas fa-user"></i> <span></span></p>
            <p id="modal-activity"><i class="fas fa-tasks"></i> <span></span></p>
            <p id="modal-reason" class="rejection-reason"><i class="fas fa-exclamation-triangle"></i> <span></span></p>
        </div>
    </div>

    <script>
    function openModal(booking) {
        document.getElementById("modal-room").querySelector("span").innerText = booking.room;
        document.getElementById("modal-date").querySelector("span").innerText = booking.date;
        document.getElementById("modal-time").querySelector("span").innerText = booking.time_start + " - " + booking.time_end;
        document.getElementById("modal-field").querySelector("span").innerText = booking.field;
        document.getElementById("modal-status").querySelector("span").innerText = booking.status;
        document.getElementById("modal-orderer").querySelector("span").innerText = booking.nama;
        document.getElementById("modal-activity").querySelector("span").innerText = booking.activity;

        if (booking.reason) {
            document.getElementById("modal-reason").querySelector("span").innerText = booking.reason;
            document.getElementById("modal-reason").style.display = "block";
        } else {
            document.getElementById("modal-reason").style.display = "none";
        }

        document.getElementById("bookingModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("bookingModal").style.display = "none";
    }

    function confirmLogout() {
        return confirm("Apakah Anda yakin ingin keluar?");
    }

    // Close the modal when clicking outside of it
    window.onclick = function(event) {
        if (event.target == document.getElementById("bookingModal")) {
            closeModal();
        }
    }
    </script>
</body>
</html>