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

// Proses tambah ruangan
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_ruangan'])) {
    $nama = $_POST['nama'];
    $jenis = $_POST['jenis'];
    $kapasitas = $_POST['kapasitas'];
    $fasilitas = $_POST['fasilitas'];
    $gambar = 'default-room.jpg'; // Default image
    
    // Handle file upload if available
    if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "uploads/rooms/";
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Check if file is an actual image
        $valid_extensions = array("jpg", "jpeg", "png", "gif");
        if(in_array($file_extension, $valid_extensions)) {
            if(move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                $gambar = $new_filename;
            }
        }
    }

    $stmt = $pdo->prepare("INSERT INTO ruangan (nama, jenis, kapasitas, fasilitas, gambar) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nama, $jenis, $kapasitas, $fasilitas, $gambar]);
    
    // Redirect to avoid form resubmission
    header("Location: admin_ruang.php");
    exit();
}

// Proses edit ruangan
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_ruangan'])) {
    $room_id = $_POST['edit_room_id'];
    $nama = $_POST['edit_nama'];
    $jenis = $_POST['edit_jenis'];
    $kapasitas = $_POST['edit_kapasitas'];
    $fasilitas = $_POST['edit_fasilitas'];
    
    // Get current image name
    $stmt = $pdo->prepare("SELECT gambar FROM ruangan WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();
    $gambar = $room['gambar'];
    
    // Handle file upload if available
    if(isset($_FILES['edit_gambar']) && $_FILES['edit_gambar']['error'] == 0) {
        $target_dir = "uploads/rooms/";
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['edit_gambar']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Check if file is an actual image
        $valid_extensions = array("jpg", "jpeg", "png", "gif");
        if(in_array($file_extension, $valid_extensions)) {
            if(move_uploaded_file($_FILES['edit_gambar']['tmp_name'], $target_file)) {
                // Delete old image file if not default
                if ($gambar != 'default-room.jpg') {
                    $old_file_path = "uploads/rooms/" . $gambar;
                    if (file_exists($old_file_path)) {
                        unlink($old_file_path);
                    }
                }
                $gambar = $new_filename;
            }
        }
    }
    
    // Update room data
    $stmt = $pdo->prepare("UPDATE ruangan SET nama = ?, jenis = ?, kapasitas = ?, fasilitas = ?, gambar = ? WHERE id = ?");
    $stmt->execute([$nama, $jenis, $kapasitas, $fasilitas, $gambar, $room_id]);
    
    // Set success message
    $_SESSION['message'] = "Ruangan berhasil diperbarui!";
    $_SESSION['message_type'] = "success";
    
    // Redirect to avoid form resubmission
    header("Location: admin_ruang.php");
    exit();
}

// Proses hapus ruangan - FIXED VERSION
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['hapus_ruangan'])) {
    $room_id = $_POST['room_id'];
    
    // Get image filename before deleting record
    $stmt = $pdo->prepare("SELECT gambar FROM ruangan WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();
    
    // Delete the record
    $stmt = $pdo->prepare("DELETE FROM ruangan WHERE id = ?");
    $stmt->execute([$room_id]);
    
    // Delete the image file if not default
    if ($room && $room['gambar'] != 'default-room.jpg') {
        $file_path = "uploads/rooms/" . $room['gambar'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Set success message
    $_SESSION['message'] = "Ruangan berhasil dihapus!";
    $_SESSION['message_type'] = "success";
    
    // Redirect after deletion
    header("Location: admin_ruang.php");
    exit();
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

        /* Room card styles */
        .room-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .room-card {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: white;
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
            border-bottom: 1px solid var(--border-color);
        }
        
        .room-info {
            padding: 1rem;
            flex: 1;
        }
        
        .room-name {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
        
        .room-detail {
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            color: var(--text-light);
        }
        
        .room-buttons {
            display: flex;
            justify-content: flex-end;
            padding: 0.75rem 1rem;
            background-color: var(--background-color);
            border-top: 1px solid var(--border-color);
        }
        
        .detail-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.2s;
        }
        
        .detail-btn:hover {
            background-color: var(--secondary-color);
        }

        /* Add Room Button */
        .add-room-btn {
            display: inline-flex;
            align-items: center;
            background-color: var(--primary-color);
            color: white;
            padding: 0.75rem 1.25rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 1.5rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: var(--shadow-sm);
            gap: 0.5rem;
        }
        
        .add-room-btn:hover {
            background-color: var(--secondary-color);
            box-shadow: var(--shadow);
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: var(--card-color);
            margin: 4% auto;
            padding: 0;
            border: none;
            border-radius: var(--border-radius);
            width: 600px;
            max-width: 95%;
            box-shadow: var(--shadow-lg);
            animation: modalFadeIn 0.3s ease;
            overflow: hidden;
        }

        @keyframes modalFadeIn {
            from {opacity: 0; transform: translateY(-30px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--background-color);
        }

        .modal-title {
            color: var(--primary-color);
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        .close {
            color: var(--text-light);
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
            line-height: 1;
        }

        .close:hover {
            color: var(--text-dark);
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.625rem;
            padding: 1rem 1.5rem;
            background-color: var(--background-color);
            border-top: 1px solid var(--border-color);
        }

        /* Form Styling */
        .form-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }

        .form-section {
            padding: 0;
        }

        .full-width {
            grid-column: span 2;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-dark);
            font-size: 0.875rem;
        }

        .form-control {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            color: var(--text-dark);
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.12);
            outline: none;
        }

        textarea.form-control {
            min-height: 110px;
            resize: vertical;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 40px;
        }

        /* File upload styling */
        .file-upload-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            background-color: var(--background-color);
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .file-upload-area:hover {
            border-color: var(--primary-color);
            background-color: rgba(67, 97, 238, 0.05);
        }

        .upload-icon {
            font-size: 1.75rem;
            color: var(--primary-color);
            margin-bottom: 0.75rem;
        }

        .upload-text {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
        }

        .upload-info {
            font-size: 0.75rem;
            color: var(--text-light);
            text-align: center;
            margin-bottom: 0.75rem;
        }

        .selected-file-name {
            margin-top: 0.5rem;
            font-size: 0.8125rem;
            color: var(--primary-color);
            text-align: center;
            font-weight: 500;
        }

        /* Image preview */
        .image-preview {
            margin-top: 0.75rem;
            display: flex;
            justify-content: center;
        }

        .image-preview img {
            max-height: 120px;
            max-width: 100%;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1.25rem;
            border: none;
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            gap: 0.5rem;
        }

        .btn-cancel {
            background-color: var(--background-color);
            color: var(--text-light);
        }

        .btn-cancel:hover {
            background-color: #e2e8f0;
            color: var(--text-dark);
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        /* Delete confirmation */
        .delete-confirmation {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            border-radius: var(--border-radius);
            padding: 1rem;
            text-align: center;
            margin-top: 1rem;
        }

        .delete-confirmation p {
            color: #b91c1c;
            margin: 0 0 1rem;
        }

        .delete-actions {
            display: flex;
            justify-content: center;
            gap: 0.625rem;
        }

        /* Alert messages */
        .alert {
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: var(--border-radius);
        }
        
        .alert-success {
            color: #0f5132;
            background-color: #d1e7dd;
            border-color: #badbcc;
        }

        /* Responsive adjustments */
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
            
            .room-container {
                grid-template-columns: 1fr;
            }
            
            .form-container {
                grid-template-columns: 1fr;
            }
            
            .full-width {
                grid-column: span 1;
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
        <a href="admin_ruang.php" class="active">
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
                <h2><i class="fas fa-door-closed"></i> Daftar Ruangan</h2>
                <span><?php echo $current_date; ?></span>
            </div>
            
            <!-- Display success message if exists -->
            <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                    <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                    ?>
                </div>
            <?php endif; ?>
            
            <!-- Add Room Button -->
            <button class="add-room-btn" id="showAddRoomModal">
                <i class="fas fa-plus"></i> Tambah Ruang Baru
            </button>
            
            <!-- Room Cards Container -->
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
                        <img src="' . $imagePath . '" alt="' . $row['nama'] . '" class="room-image">
                        <div class="room-info">
                            <div class="room-name">' . $row['nama'] . '</div>
                            <div class="room-detail"><strong>Tipe:</strong> ' . $row['jenis'] . '</div>
                            <div class="room-detail"><strong>Kapasitas:</strong> ' . $row['kapasitas'] . ' orang</div>
                        </div>
                        <div class="room-buttons">
                            <button class="detail-btn" onclick="showRoomDetail(' . $row['id'] . ', \'' . $row['nama'] . '\', \'' . $row['jenis'] . '\', ' . $row['kapasitas'] . ', \'' . $row['fasilitas'] . '\', \'' . $imagePath . '\')">
                                <i class="fas fa-info-circle"></i> Detail
                            </button>
                        </div>
                    </div>';
                }
                ?>
            </div>
        </div>
    </div>
    
    <!-- Room Detail Modal -->
    <div id="roomDetailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="roomDetailTitle"></h3>
                <span class="close" onclick="closeModal('roomDetailModal')">&times;</span>
            </div>
            
            <div class="modal-body">
                <!-- Room details content will be added dynamically -->
            </div>
            
            <div class="modal-footer">
                <button id="editRoomBtn" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button id="deleteRoomBtn" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Hapus
                </button>
            </div>
            
            <div id="deleteConfirmation" style="display: none; padding: 0 1.5rem 1.25rem;">
                <div class="delete-confirmation">
                    <p><i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i> Apakah Anda yakin ingin menghapus ruangan ini? Tindakan ini tidak dapat dibatalkan.</p>
                    <div class="delete-actions">
                        <button id="cancelDeleteBtn" class="btn btn-cancel">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <form id="deleteRoomForm" method="POST" action="">
                            <input type="hidden" id="roomIdToDelete" name="room_id">
                            <button type="submit" name="hapus_ruangan" class="btn btn-danger">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Room Modal -->
    <div id="addRoomModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Ruangan Baru</h3>
                <span class="close" onclick="closeModal('addRoomModal')">&times;</span>
            </div>
            
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" id="addRoomForm">
                    <div class="form-container">
                        <div class="form-section">
                            <div class="form-group">
                                <label class="form-label" for="nama">Nama Ruangan</label>
                                <input type="text" id="nama" name="nama" class="form-control" placeholder="Exampel: Ruang Meeting A" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="kapasitas">Kapasitas (orang)</label>
                                <input type="number" id="kapasitas" name="kapasitas" class="form-control" min="1" placeholder="Example: 30" required>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <div class="form-group">
                                <label class="form-label" for="jenis">Tipe Ruangan</label>
                                <select id="jenis" name="jenis" class="form-control" required>
                                    <option value="">-- Pilih Tipe Ruangan --</option>
                                    <option value="Online">Online</option>
                                    <option value="Offline">Offline</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="gambar">Foto Ruangan</label>
                                <div class="file-upload-area">
                                    <input type="file" id="gambar" name="gambar" onchange="updateFilePreview(this)" accept="image/jpeg,image/png,image/gif" style="display: none;">
                                    <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                    <div class="upload-text" id="uploadText">Select Image File</div>
                                    <div class="upload-info">Format: JPG, JPEG, PNG, GIF. Maximum 2MB</div>
                                    <button type="button" class="btn btn-primary" style="margin-top: 0.625rem;" onclick="document.getElementById('gambar').click()">
                                        <i class="fas fa-folder-open"></i> Browse Files
                                    </button>
                                    <div id="selectedFileName" class="selected-file-name"></div>
                                </div>
                                <div id="imagePreview" class="image-preview"></div>
                            </div>
                        </div>
                        
                        <div class="form-section full-width">
                            <div class="form-group">
                                <label class="form-label" for="fasilitas">Fasilitas</label>
                                <textarea id="fasilitas" name="fasilitas" class="form-control" placeholder="Exampel: Proyektor, AC, WiFi, Meja Konferensi, Soundsystem" required></textarea>
                                <div class="form-text">Catatan: Masukkan fasilitas yang tersedia di ruangan, dipisahkan dengan koma.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-cancel" onclick="closeModal('addRoomModal')">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" name="tambah_ruangan" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Room Modal -->
    <div id="editRoomModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Ruangan</h3>
                <span class="close" onclick="closeModal('editRoomModal')">&times;</span>
            </div>
            
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" id="editRoomForm">
                    <input type="hidden" id="edit_room_id" name="edit_room_id">
                    <div class="form-container">
                        <div class="form-section">
                            <div class="form-group">
                                <label class="form-label" for="edit_nama">Nama Ruangan</label>
                                <input type="text" id="edit_nama" name="edit_nama" class="form-control" placeholder="Exampel: Ruang Meeting A" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="edit_kapasitas">Kapasitas (orang)</label>
                                <input type="number" id="edit_kapasitas" name="edit_kapasitas" class="form-control" min="1" placeholder="Example: 30" required>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <div class="form-group">
                                <label class="form-label" for="edit_jenis">Tipe Ruangan</label>
                                <select id="edit_jenis" name="edit_jenis" class="form-control" required>
                                    <option value="">-- Pilih Tipe Ruangan --</option>
                                    <option value="Online">Online</option>
                                    <option value="Offline">Offline</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="edit_gambar">Foto Ruangan</label>
                                <div class="file-upload-area">
                                    <input type="file" id="edit_gambar" name="edit_gambar" onchange="updateEditFilePreview(this)" accept="image/jpeg,image/png,image/gif" style="display: none;">
                                    <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                    <div class="upload-text" id="editUploadText">Select Image File</div>
                                    <div class="upload-info">Format: JPG, JPEG, PNG, GIF. Maximum 2MB</div>
                                    <button type="button" class="btn btn-primary" style="margin-top: 0.625rem;" onclick="document.getElementById('edit_gambar').click()">
                                        <i class="fas fa-folder-open"></i> Browse Files
                                    </button>
                                    <div id="editSelectedFileName" class="selected-file-name"></div>
                                </div>
                                <div id="editImagePreview" class="image-preview"></div>
                            </div>
                        </div>
                        
                        <div class="form-section full-width">
                            <div class="form-group">
                                <label class="form-label" for="edit_fasilitas">Fasilitas</label>
                                <textarea id="edit_fasilitas" name="edit_fasilitas" class="form-control" placeholder="Exampel: Proyektor, AC, WiFi, Meja Konferensi, Soundsystem" required></textarea>
                                <div class="form-text">Catatan: Masukkan fasilitas yang tersedia di ruangan, dipisahkan dengan koma.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-cancel" onclick="closeModal('editRoomModal')">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" name="edit_ruangan" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        // Show modals
        document.getElementById('showAddRoomModal').addEventListener('click', function() {
            document.getElementById('addRoomModal').style.display = 'block';
        });
        
        // Close modals
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            if (modalId === 'roomDetailModal') {
                document.getElementById('deleteConfirmation').style.display = 'none';
            }
        }
        
        // Show room details
        function showRoomDetail(id, nama, jenis, kapasitas, fasilitas, imagePath) {
            document.getElementById('roomDetailTitle').textContent = nama;
            
            // Prepare modal body content
            let modalBody = document.querySelector('#roomDetailModal .modal-body');
            modalBody.innerHTML = `
                <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <div style="text-align: center;">
                        <img src="${imagePath}" alt="${nama}" style="max-height: 200px; border-radius: var(--border-radius); margin: 0 auto;">
                    </div>
                    <div>
                        <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.875rem; color: var(--primary-color);">Detail Ruangan</h4>
                        <div style="display: grid; grid-template-columns: 120px 1fr; gap: 0.5rem; font-size: 0.875rem;">
                            <div style="font-weight: 500;">Nama:</div>
                            <div>${nama}</div>
                            <div style="font-weight: 500;">Jenis:</div>
                            <div>${jenis}</div>
                            <div style="font-weight: 500;">Kapasitas:</div>
                            <div>${kapasitas} orang</div>
                            <div style="font-weight: 500;">Fasilitas:</div>
                            <div>${fasilitas}</div>
                        </div>
                    </div>
                </div>
            `;
            
            // Set room ID for delete action
            document.getElementById('roomIdToDelete').value = id;
            
            // Show modal
            document.getElementById('roomDetailModal').style.display = 'block';
        }
        
        // Handle delete button click
        document.getElementById('deleteRoomBtn').addEventListener('click', function() {
            document.getElementById('deleteConfirmation').style.display = 'block';
        });
        
        // Handle cancel delete
        document.getElementById('cancelDeleteBtn').addEventListener('click', function() {
            document.getElementById('deleteConfirmation').style.display = 'none';
        });

        // Handle edit button click
        document.getElementById('editRoomBtn').addEventListener('click', function() {
            // Get room ID from the delete form
            const roomId = document.getElementById('roomIdToDelete').value;
            
            // Close detail modal
            closeModal('roomDetailModal');
            
            // Fetch room data and populate edit form
            populateEditForm(roomId);
            
            // Show edit modal
            document.getElementById('editRoomModal').style.display = 'block';
        });

        // Populate edit form with room data
        function populateEditForm(roomId) {
            // Set room ID in edit form
            document.getElementById('edit_room_id').value = roomId;
            
            // Get room details from the detail modal
            const roomName = document.getElementById('roomDetailTitle').textContent;
            const detailElements = document.querySelectorAll('#roomDetailModal .modal-body div[style="display: grid; grid-template-columns: 120px 1fr; gap: 0.5rem; font-size: 0.875rem;"] div');
            
            // Populate form fields
            document.getElementById('edit_nama').value = roomName;
            
            // Find jenis value (3rd div after a label)
            const jenisValue = detailElements[3].textContent;
            document.getElementById('edit_jenis').value = jenisValue;
            
            // Find kapasitas value (5th div after a label)
            const kapasitasText = detailElements[5].textContent;
            const kapasitasValue = parseInt(kapasitasText.split(' ')[0]);
            document.getElementById('edit_kapasitas').value = kapasitasValue;
            
            // Find fasilitas value (7th div after a label)
            const fasilitasValue = detailElements[7].textContent;
            document.getElementById('edit_fasilitas').value = fasilitasValue;
            
            // Set image preview if available
            const imageElement = document.querySelector('#roomDetailModal .modal-body img');
            if (imageElement) {
                const imageSrc = imageElement.getAttribute('src');
                document.getElementById('editImagePreview').innerHTML = `<img src="${imageSrc}" alt="Preview">`;
            }
        }

        // Update file preview for edit form
        function updateEditFilePreview(input) {
            const fileName = input.files[0]?.name;
            if (fileName) {
                document.getElementById('editSelectedFileName').textContent = fileName;
                document.getElementById('editUploadText').textContent = "File Selected";
                
                // Create image preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imagePreview = document.getElementById('editImagePreview');
                    imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        // Update file preview
        function updateFilePreview(input) {
            const fileName = input.files[0]?.name;
            if (fileName) {
                document.getElementById('selectedFileName').textContent = fileName;
                document.getElementById('uploadText').textContent = "File Selected";
                
                // Create image preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imagePreview = document.getElementById('imagePreview');
                    imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Confirm logout
        function confirmLogout() {
            return confirm("Apakah Anda yakin ingin keluar dari sistem?");
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
                if (event.target.id === 'roomDetailModal') {
                    document.getElementById('deleteConfirmation').style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>