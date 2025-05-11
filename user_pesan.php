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

$response = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room = $_POST['room'];
    $date = $_POST['date'];
    $time_start = $_POST['time_start'];
    $time_end = $_POST['time_end'];
    $field = $_POST['field'];
    $activity = $_POST['activity'];
    $user_id = $_SESSION['user_id'];

    $current_time = date("H:i:s");
    $current_date = date("Y-m-d");
    if (strtotime($time_start) >= strtotime($time_end)) {
        $response['status'] = 'error';
        $response['message'] = "Jam Mulai Harus Lebih Awal Daripada Jam Selesai.";
    } elseif (strtotime($date) < strtotime($current_date)) {
        $response['status'] = 'error';
        $response['message'] = "Tanggal Tidak Boleh di Masa Lalu.";
    } elseif (strtotime($time_start) < strtotime($current_time) && $date === $current_date) {
        $response['status'] = 'error';
        $response['message'] = "Jam Mulai Tidak Boleh di Masa Lalu.";
    } else {
        // Ganti bagian pengecekan ketersediaan ruangan pada sekitar baris 40-43
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room = :room AND date = :date AND 
                            ((time_start <= :time_start AND time_end > :time_start) OR 
                            (time_start < :time_end AND time_end >= :time_end)) AND 
                            status != 'rejected'");
        $stmt->execute(['room' => $room, 'date' => $date, 'time_start' => $time_start, 'time_end' => $time_end]);
        if ($stmt->fetchColumn() > 0) {
            $response['status'] = 'error';
            $response['message'] = "Ruangan Sudah di Pesan Pada Waktu Yang di Pilih.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO bookings (room, date, time_start, time_end, field, user_id, activity, status) VALUES (:room, :date, :time_start, :time_end, :field, :user_id, :activity, 'pending')");
            if ($stmt->execute(['room' => $room, 'date' => $date, 'time_start' => $time_start, 'time_end' => $time_end, 'field' => $field, 'user_id' => $user_id, 'activity' => $activity])) {
                $response['status'] = 'success';
                $response['message'] = "Pemesanan Berhasil. Menunggu Persetujuan Admin.";
            } else {
                $response['status'] = 'error';
                $response['message'] = "Pemesanan Gagal.";
            }
        }
    }
    echo json_encode($response);
    exit;
}

// Check if ruangan table exists and get available rooms
try {
    // First check if the table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'ruangan'");
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        // If table exists, get room names
        $stmt = $pdo->prepare("SELECT nama FROM ruangan");
        $stmt->execute();
        $available_rooms = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        // Table doesn't exist, use default values
        $available_rooms = ["Offline 1", "Offline 2", "Online 1", "Online 2"];
    }
} catch (PDOException $e) {
    // If there's any database error, use default values
    $available_rooms = ["Offline 1", "Offline 2", "Online 1", "Online 2"];
}

// If there are no available rooms in the ruangan table, use default values
if (empty($available_rooms)) {
    $available_rooms = ["Offline 1", "Offline 2", "Online 1", "Online 2"];
}

// Get current date for display
$current_date = date('l, d F Y');

$stmt = $pdo->prepare("SELECT status FROM bookings WHERE user_id = :user_id ORDER BY id DESC LIMIT 1");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$last_booking_status = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Ruangan</title>
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

        .booking-form {
            background-color: var(--card-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            padding: 2rem;
            max-width: 700px;
            margin: 0 auto;
            animation: fadeIn 0.5s ease;
        }

        .booking-form h3 {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: border-color var(--transition-speed) ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .submit-btn {
            display: block;
            width: 100%;
            padding: 0.875rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 500;
            text-align: center;
            cursor: pointer;
            transition: background-color var(--transition-speed) ease;
        }

        .submit-btn:hover {
            background-color: var(--secondary-color);
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
            
            .booking-form {
                padding: 1.5rem;
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
        <a href="user_pesan.php" class="active">
            <i class="fas fa-calendar-plus"></i> <span>Pesan</span>
        </a>
        <a href="user_jadwal.php">
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

    <div class="main-admin">
        <div class="container-admin">
            <div class="page-header">
                <h2><i class="fas fa-calendar-plus"></i> Pesan Ruangan</h2>
                <span><?php echo $current_date; ?></span>
            </div>
            
            <form id="bookingForm" method="POST" action="user_booking.php">
                <div class="booking-form">
                    <h3>Reservasi Ruang Rapat Mitras DUDI</h3>
                    
                    <div class="form-group">
                        <label for="room">Pilih Ruangan:</label>
                        <select id="room" name="room" required>
                            <?php foreach($available_rooms as $room): ?>
                                <option value="<?php echo htmlspecialchars($room); ?>"><?php echo htmlspecialchars($room); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date">Tanggal:</label>
                        <input type="date" id="date" name="date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="time_start">Mulai Jam:</label>
                        <input type="time" id="time_start" name="time_start" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="time_end">Selesai Jam:</label>
                        <input type="time" id="time_end" name="time_end" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="field">Bidang:</label>
                        <select id="field" name="field" required>
                            <option value="Tata Usaha">Tata Usaha</option>
                            <option value="Kemitraan">Kemitraan</option>
                            <option value="Penyelarasan">Penyelarasan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="activity">Kegiatan:</label>
                        <textarea id="activity" name="activity" placeholder="Jelaskan Kegiatan Anda" required></textarea>
                    </div>
                    
                    <button type="submit" class="submit-btn">PESAN</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var bookingForm = document.getElementById('bookingForm');

            bookingForm.onsubmit = function(event) {
                event.preventDefault();

                var formData = new FormData(bookingForm);

                fetch('user_pesan.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.status === 'success') {
                        bookingForm.reset();
                    }
                })
                .catch(error => console.error('Error:', error));
            };

            // Set today as minimum date for the date picker
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('date').min = today;

            // Load previously selected values from localStorage if available
            if (localStorage.getItem('room')) {
                document.getElementById('room').value = localStorage.getItem('room');
            }
            if (localStorage.getItem('field')) {
                document.getElementById('field').value = localStorage.getItem('field');
            }

            // Save selected values to localStorage
            document.getElementById('room').onchange = function() {
                localStorage.setItem('room', this.value);
            };
            document.getElementById('field').onchange = function() {
                localStorage.setItem('field', this.value);
            };
        });

        function confirmLogout() {
            return confirm("Apakah Anda yakin ingin keluar?");
        }
    </script>
</body>
</html>