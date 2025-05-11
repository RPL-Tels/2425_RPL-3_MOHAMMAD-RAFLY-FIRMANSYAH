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
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room = :room AND date = :date AND ((time_start <= :time_start AND time_end > :time_start) OR (time_start < :time_end AND time_end >= :time_end))");
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
$stmt = $pdo->prepare("SELECT status FROM bookings WHERE user_id = :user_id ORDER BY id DESC LIMIT 1");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$last_booking_status = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" type="text/css" href="styleee.css">
    <link rel="stylesheet" type="text/css" href="modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <style>
    .booking-form-container {
        display: flex;
        justify-content: flex-start; 
        padding-left: 230px; 
    }

    .booking-form {
        width: 70%; 
        max-width: 1100px; 
        padding: 20px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .booking-form h2 {
        text-align: center;
        color: #333;
        margin-bottom: 20px;
    }

    .booking-form label {
        font-weight: bold;
        color: #555;
        margin-bottom: 5px;
    }

    .booking-form input,
    .booking-form select,
    .booking-form textarea {
        width: 100%; 
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
        margin-bottom: 15px; 
    }

    .booking-form textarea {
        resize: none; 
    }

    .booking-form button {
        padding: 10px;
        background-color: #28a745;
        color: white;
        border: none;
        font-weight: bold;
        cursor: pointer;
        border-radius: 5px;
        width: 100%; 
    }

    .booking-form button:hover {
        background-color: #218838;
    }
    </style>
    <header>
        <nav>
            <div class="logo"></div>
        </nav>
    </header>

    <div class="sidebar">
        <a href="user_home.php"><i class="fas fa-home"></i> Home</a>
        <a href="user_booking.php" class="active"><i class="fas fa-calendar-plus"></i> Pemesanan</a>
        <a href="user_schedule.php"><i class="fas fa-calendar"></i> Jadwal</a>
        <div class="bottom-links">
            <a href="user_akun.php"><i class="fas fa-user-shield"></i> Akun Saya</a>
            <a href="logout.php" onclick="return confirmLogout();"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <form id="bookingForm" method="POST" action="user_booking.php">
    <div class="booking-form-container">
    <div class="booking-form">
        <h2>Booking Room Meeting Mitras DUDI</h2>
        <label for="room">Select a Room:</label>
        <select id="room" name="room" required>
            <option value="Offline 1">Offline 1</option>
            <option value="Offline 2">Offline 2</option>
            <option value="Online (Zoom) 1">Online (Zoom) 1</option>
            <option value="Online (Zoom) 2">Online (Zoom) 2</option>
        </select>

        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required>

        <label for="time_start">Start At:</label>
        <input type="time" id="time_start" name="time_start" required>

        <label for="time_end">End At:</label>
        <input type="time" id="time_end" name="time_end" required>

        <label for="field">Field:</label>
        <select id="field" name="field" required>
            <option value="Tata Usaha">Tata Usaha</option>
            <option value="Kemitraan">Kemitraan</option>
            <option value="Penyelarasan">Penyelarasan</option>
        </select>

        <label for="activity">Kegiatan:</label>
        <textarea id="activity" name="activity" rows="3" placeholder="Deskripsikan kegiatan Anda" required></textarea>

        <button type="submit">BOOKING</button>
    </div>
</div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var bookingForm = document.getElementById('bookingForm');

            bookingForm.onsubmit = function(event) {
                event.preventDefault();

                var formData = new FormData(bookingForm);

                fetch('user_booking.php', {
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

            if (localStorage.getItem('room')) {
                document.getElementById('room').value = localStorage.getItem('room');
            }
            if (localStorage.getItem('field')) {
                document.getElementById('field').value = localStorage.getItem('field');
            }

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
