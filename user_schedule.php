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
    ?>

    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Schedule</title>
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel="stylesheet" type="text/css" href="style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <style>
    .sidebar {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: white;
        width: 220px;
        position: fixed;
        top: 70px;
        height: calc(100vh - 60px);
        display: flex;
        flex-direction: column;
        padding: 20px 10px;
        box-sizing: border-box;
        z-index: 900;
    }

    .sidebar a {
        padding: 10px 15px;
        margin: 5px 0;
        color: white;
        text-decoration: none;
        display: flex;
        align-items: center;
        font-size: 18px;
        white-space: nowrap;
    }

    .sidebar a:hover {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 5px;
    }

    .sidebar a i {
        margin-right: 10px;
        font-size: 20px;
    }
    .sidebar a.active {
        background: rgba(255, 255, 255, 0.1);
        padding-left: 30px;
        font-weight: bold;
    }

    .sidebar .bottom-links {
        display: flex;
        flex-direction: column;
        gap: 0px;
        margin-top: auto;
    }

    .main-jadwal {
        margin-left: 250px;
        padding: 20px;
    }

    .container-jadwal {
        background-color: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    }

    .table-container {
        margin-top: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 12px 15px;
        border: 1px solid #ddd;
        text-align: center;
    }

    th {
        background-color: #f4f4f4;
        font-weight: bold;
    }

    form {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }

    form label {
        margin-right: 10px;
        line-height: 2.5em;
    }

    form input[type="date"] {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin-right: 10px;
        font-size: 16px;
    }

    form button {
        padding: 8px 16px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    form button:hover {
        background-color: #45a049;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        padding-top: 100px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: white;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 600px;
        border-radius: 10px;
    }

    .modal-content h2 {
        margin-top: 0;
    }

    .modal-content p {
        margin: 10px 0;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover, .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
    </style>
</head>
<body>
        <header>
            <nav>
                <div class="logo"></div>
            </nav>
        </header>
        <div class="sidebar">
            <a href="user_home.php">
                <i class="fas fa-home"></i> Home</a>
            <a href="user_booking.php">
                <i class="fas fa-calendar-plus"></i> Pemesanan</a>
            <a href="user_schedule.php" class="active">
                <i class="fas fa-calendar"></i> Jadwal</a>
            <div class="bottom-links">
                <a href="user_akun.php">
                    <i class="fas fa-user-shield"></i> Akun Saya</a>
                <a href="logout.php" onclick="return confirmLogout();">
                    <i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="main-jadwal">
            <div class="container-jadwal">
                <h2>Schedule Room Meeting Mitras DUDI</h2>
                <form method="GET" action="user_schedule.php">
                    <label for="date">Filter by Date:</label>
                    <input type="date" id="date" name="date" placeholder="yyyy-mm-dd" value="<?php echo htmlspecialchars($dateFilter); ?>">
                    <button type="submit">Search</button>
                </form>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Room</th>
                                <th>Date</th>
                                <th>Field</th>
                                <th>Action</th>
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
                                            <button onclick="openModal(<?php echo htmlspecialchars(json_encode($booking)); ?>)">View Details</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align:center;">No bookings found for the selected date.</td>
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
                <h2>Booking Details</h2>
                <p id="modal-room"></p>
                <p id="modal-date"></p>
                <p id="modal-time"></p>
                <p id="modal-field"></p>
                <p id="modal-status"></p>
                <p id="modal-orderer"></p>
                <p id="modal-activity"></p>
                <p id="modal-reason"></p>
            </div>
        </div>
        <script>
        function openModal(booking) {
            document.getElementById("modal-room").innerText = "Room: " + booking.room;
            document.getElementById("modal-date").innerText = "Date: " + booking.date;
            document.getElementById("modal-time").innerText = "Time: " + booking.time_start + " - " + booking.time_end;
            document.getElementById("modal-field").innerText = "Field: " + booking.field;
            document.getElementById("modal-status").innerText = "Status: " + booking.status;
            document.getElementById("modal-orderer").innerText = "Orderer: " + booking.nama;
            document.getElementById("modal-activity").innerText = "Kegiatan: " + booking.activity;

            if (booking.reason) {
                document.getElementById("modal-reason").innerText = "Rejection Reason: " + booking.reason;
                document.getElementById("modal-reason").classList.add("rejection-reason");
            } else {
                document.getElementById("modal-reason").innerText = "";
                document.getElementById("modal-reason").classList.remove("rejection-reason");
            }

            document.getElementById("bookingModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("bookingModal").style.display = "none";
        }

        function confirmLogout() {
            return confirm("Apakah Anda Yakin Untuk Logout?");
        }
        </script>
    </body>
    </html>
