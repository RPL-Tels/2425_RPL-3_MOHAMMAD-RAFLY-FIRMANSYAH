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

// Process form submission
// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip = $_POST['nip'];
    $nama = $_POST['nama'];
    $jabatan = $_POST['jabatan']; // Add this line to get jabatan from form
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    
    // Set role to 'admin' if is_admin is checked
    if ($is_admin) {
        $role = 'admin';
    }
    
    // Simple validation
    $errors = [];
    
    if (empty($nip)) {
        $errors[] = "NIP tidak boleh kosong";
    }
    
    if (empty($nama)) {
        $errors[] = "Nama tidak boleh kosong";
    }
    
    if (empty($jabatan)) {
        $errors[] = "Jabatan tidak boleh kosong";
    }
    
    if (empty($password)) {
        $errors[] = "Password tidak boleh kosong";
    } else if (strlen($password) < 8) {
        $errors[] = "Password minimal 8 karakter";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak sama";
    }
    
    // Check if NIP already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE nip = :nip");
    $stmt->execute(['nip' => $nip]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "NIP sudah digunakan";
    }
    
    if (empty($errors)) {
        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (nip, nama, jabatan, password, role, is_admin) VALUES (:nip, :nama, :jabatan, :password, :role, :is_admin)");
        $result = $stmt->execute([
            'nip' => $nip,
            'nama' => $nama,
            'jabatan' => $jabatan, // Add this line
            'password' => $password,
            'role' => $role,
            'is_admin' => $is_admin
        ]);
        
        if ($result) {
            $_SESSION['message'] = "User berhasil ditambahkan";
            $_SESSION['message_type'] = "success";
            header("Location: admin_datauser.php");
            exit;
        } else {
            $errors[] = "Gagal menambahkan user";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User - Admin Panel</title>
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

        .form-container {
            max-width: 650px;
            margin: 0 auto;
            animation: fadeIn 0.5s ease;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.9375rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            transition: all var(--transition-speed) ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .form-check-input {
            margin-right: 0.5rem;
            width: 1rem;
            height: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 0.9375rem;
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

        .btn-secondary {
            background-color: #e5e7eb;
            color: var(--text-dark);
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        .form-hint {
            font-size: 0.875rem;
            color: var(--text-light);
            margin-top: 0.375rem;
        }

        .error-list {
            background-color: rgba(244, 63, 94, 0.1);
            color: #e11d48;
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
        }

        .error-list ul {
            margin: 0.5rem 0 0 1.25rem;
        }

        .error-list li {
            margin-bottom: 0.25rem;
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
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
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
        <a href="admin_home.php">
            <i class="fas fa-home"></i> <span>Home</span>
        </a>
        <a href="admin_approve.php">
            <i class="fas fa-check-circle"></i> <span>Approve</span>
        </a>
        <a href="admin_datauser.php" class="active">
            <i class="fas fa-id-card"></i> <span>Data User</span>
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
                <i class="fas fa-user-shield"></i> <span>Akun Saya</span>
            </a>
            <a href="logout.php" onclick="return confirmLogout();">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </div>
    </div>

    <div class="main-admin">
        <div class="container-admin">
            <div class="page-header">
                <h2><i class="fas fa-user-plus"></i> Tambah User</h2>
                <span><?php echo $current_date; ?></span>
            </div>
            
            <?php if(isset($errors) && !empty($errors)): ?>
                <div class="error-list">
                    <i class="fas fa-exclamation-circle"></i> Ada beberapa kesalahan:
                    <ul>
                        <?php foreach($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="form-container">
                <form action="pegawai_tambah.php" method="POST">
                    <div class="form-group">
                        <label for="nip">NIP *</label>
                        <input type="text" id="nip" name="nip" class="form-control" value="<?php echo isset($_POST['nip']) ? htmlspecialchars($_POST['nip']) : ''; ?>" required>
                        <div class="form-hint">Masukkan NIP (Nomor Induk Pegawai)</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="nama">Nama Lengkap *</label>
                        <input type="text" id="nama" name="nama" class="form-control" value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="jabatan">Jabatan:</label>
                        <input type="text" id="jabatan" name="jabatan" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <div class="form-hint">Password minimal 8 karakter</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Jabatan/Role</label>
                        <input type="text" id="role" name="role" class="form-control" value="<?php echo isset($_POST['role']) ? htmlspecialchars($_POST['role']) : 'Staff'; ?>">
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" id="is_admin" name="is_admin" class="form-check-input" <?php echo isset($_POST['is_admin']) ? 'checked' : ''; ?>>
                            <label for="is_admin">Jadikan sebagai Admin</label>
                        </div>
                        <div class="form-hint">Admin memiliki akses penuh ke sistem</div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="admin_datauser.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
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