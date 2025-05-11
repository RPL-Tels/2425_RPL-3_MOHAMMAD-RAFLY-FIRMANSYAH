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

// Get current date for display
$current_date = date('l, d F Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruangan - MITRAS DUDI</title>
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

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        .container-rooms {
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

        .room-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .room-card {
            background-color: var(--card-color);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .room-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .room-info {
            padding: 1.25rem;
            flex-grow: 1;
        }

        .room-name {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--primary-color);
        }

        .room-detail {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            color: var(--text-light);
        }

        .room-detail i {
            width: 20px;
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        .room-buttons {
            padding: 1rem;
            background-color: rgba(67, 97, 238, 0.05);
            display: flex;
            justify-content: flex-end;
            border-top: 1px solid var(--border-color);
        }

        .detail-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color var(--transition-speed) ease;
        }

        .detail-btn:hover {
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
            margin: 5% auto;
            width: 90%;
            max-width: 600px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            animation: modalFadeIn 0.3s ease;
            overflow: hidden;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.5rem;
            background-color: rgba(67, 97, 238, 0.05);
            border-bottom: 1px solid var(--border-color);
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .close {
            color: var(--text-light);
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
            transition: color var(--transition-speed) ease;
        }

        .close:hover {
            color: var(--danger-color);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .facility-tag {
            display: inline-block;
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            padding: 0.375rem 0.75rem;
            border-radius: 2rem;
            margin: 0.25rem;
            font-size: 0.875rem;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
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
            
            .main-content {
                margin-left: 80px;
            }

            .room-container {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .container-rooms {
                padding: 1rem;
            }
            
            .room-container {
                grid-template-columns: 1fr;
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
        <a href="user_jadwal.php">
            <i class="fas fa-calendar"></i> <span>Jadwal</span>
        </a>
        <a href="user_ruang.php" class="active">
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

    <div class="main-content">
        <div class="container-rooms">
            <div class="page-header">
                <h2><i class="fas fa-door-open"></i> Daftar Ruangan </h2>
                <span><?php echo $current_date; ?></span>
            </div>
            
            <div class="room-container">
                <?php
                $stmt = $pdo->query("SELECT * FROM ruangan");
                while ($row = $stmt->fetch()) {
                    // Default image path if no image is available
                    $imagePath = 'uploads/rooms/' . ($row['gambar'] ?? 'default-room.jpg');
                    if (!file_exists($imagePath)) {
                        $imagePath = 'img/default-room.jpg';
                    }
                    
                    echo '<div class="room-card">
                        <img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($row['nama']) . '" class="room-image">
                        <div class="room-info">
                            <div class="room-name">' . htmlspecialchars($row['nama']) . '</div>
                            <div class="room-detail">
                                <i class="fas fa-tag"></i> ' . htmlspecialchars($row['jenis']) . '
                            </div>
                            <div class="room-detail">
                                <i class="fas fa-users"></i> Kapasitas: ' . htmlspecialchars($row['kapasitas']) . ' orang
                            </div>
                        </div>
                        <div class="room-buttons">
                            <button class="detail-btn" onclick="showRoomDetail(' . $row['id'] . ', \'' . htmlspecialchars(addslashes($row['nama'])) . '\', \'' . htmlspecialchars(addslashes($row['jenis'])) . '\', ' . htmlspecialchars($row['kapasitas']) . ', \'' . htmlspecialchars(addslashes($row['fasilitas'])) . '\', \'' . htmlspecialchars($imagePath) . '\')">
                                <i class="fas fa-info-circle"></i> Detail
                            </button>
                        </div>
                    </div>';
                }
                ?>
            </div>
        </div>
    </div>
    
    <div id="roomDetailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="roomDetailTitle"></h3>
                <span class="close" onclick="closeModal('roomDetailModal')">&times;</span>
            </div>
            
            <div class="modal-body">
                <!-- Room details content will be added dynamically -->
            </div>
        </div>
    </div>
    
    <script>
        // Show room detail modal
        function showRoomDetail(id, nama, jenis, kapasitas, fasilitas, imagePath) {
            document.getElementById('roomDetailTitle').textContent = nama;
            
            // Build modal body content
            const modalBody = document.querySelector('#roomDetailModal .modal-body');
            
            // Format facilities as tags
            let facilitiesHtml = '';
            if(fasilitas && fasilitas.trim() !== '') {
                const facilities = fasilitas.split(',');
                facilities.forEach(facility => {
                    if(facility.trim() !== '') {
                        facilitiesHtml += `<span class="facility-tag"><i class="fas fa-check-circle"></i> ${facility.trim()}</span>`;
                    }
                });
            } else {
                facilitiesHtml = '<span style="color: var(--text-light); font-style: italic;">No facilities listed</span>';
            }
            
            // Construct the modal body HTML
            modalBody.innerHTML = `
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <img src="${imagePath}" alt="${nama}" style="max-height: 200px; max-width: 100%; border-radius: var(--border-radius); box-shadow: var(--shadow);">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="background-color: rgba(67, 97, 238, 0.05); padding: 1rem; border-radius: var(--border-radius); border: 1px solid var(--border-color);">
                        <div style="font-size: 0.875rem; color: var(--text-light); margin-bottom: 0.25rem;">Jenis Ruangan</div>
                        <div style="font-size: 1rem; font-weight: 500; color: var(--text-dark);"><i class="fas fa-tag" style="color: var(--primary-color); margin-right: 0.5rem;"></i>${jenis}</div>
                    </div>
                    
                    <div style="background-color: rgba(67, 97, 238, 0.05); padding: 1rem; border-radius: var(--border-radius); border: 1px solid var(--border-color);">
                        <div style="font-size: 0.875rem; color: var(--text-light); margin-bottom: 0.25rem;">Kapasitas</div>
                        <div style="font-size: 1rem; font-weight: 500; color: var(--text-dark);"><i class="fas fa-users" style="color: var(--primary-color); margin-right: 0.5rem;"></i>${kapasitas} orang</div>
                    </div>
                </div>
                
                <div style="background-color: rgba(67, 97, 238, 0.05); padding: 1rem; border-radius: var(--border-radius); border: 1px solid var(--border-color);">
                    <div style="font-size: 0.875rem; color: var(--text-light); margin-bottom: 0.75rem;">Fasilitas</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.25rem;">${facilitiesHtml}</div>
                </div>
            `;
            
            document.getElementById('roomDetailModal').style.display = 'block';
        }
        
        // Close modal function
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
        
        function confirmLogout() {
            return confirm("Apakah Anda yakin ingin keluar?");
        }
    </script>
</body>
</html>