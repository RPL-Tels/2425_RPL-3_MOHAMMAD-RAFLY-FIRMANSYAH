<?php
session_start();

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

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nip = $_POST['nip'];
    $password = $_POST['password'];

    // Mengambil data pengguna berdasarkan NIP
    $stmt = $pdo->prepare("SELECT * FROM users WHERE nip = :nip");
    $stmt->execute(['nip' => $nip]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debugging: Memeriksa jika pengguna ditemukan
    if ($user) {
        // Kemungkinan 1: Password disimpan sebagai plain text
        if ($password === $user['password']) {
            // Login berhasil dengan plain text
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nip'] = $user['nip'];
            $_SESSION['role'] = $user['role']; 

            // Redirect berdasarkan role pengguna
            if ($user['role'] === 'Admin') {
                header("Location: admin_home.php"); 
            } else {
                header("Location: user_home.php"); 
            }
            exit;
        }
        // Kemungkinan 2: Password disimpan dengan password_hash()
        else if (password_verify($password, $user['password'])) {
            // Login berhasil dengan password_hash
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nip'] = $user['nip'];
            $_SESSION['role'] = $user['role']; 

            // Redirect berdasarkan role pengguna
            if ($user['role'] === 'admin') {
                header("Location: admin_home.php"); 
            } else {
                header("Location: user_home.php"); 
            }
            exit;
        }
        // Kemungkinan 3: Password disimpan dengan md5
        else if (md5($password) === $user['password']) {
            // Login berhasil dengan md5
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nip'] = $user['nip'];
            $_SESSION['role'] = $user['role']; 

            // Redirect berdasarkan role pengguna
            if ($user['role'] === 'Admin') {
                header("Location: admin_home.php"); 
            } else {
                header("Location: user_home.php"); 
            }
            exit;
        }
        else {
            echo "<script>alert('upsss! Password anda salah.')</script>";
        }
        } 
        else {
        echo "<script>alert('upsss! NIP anda salah.')</script>";
        }
    }

?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Sistem Booking Ruangan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        
        html, body {
            height: 100%;
            width: 100%;
            overflow: hidden;
        }
        
        body {
            display: flex;
            background-color: #f5f5f5;
        }
        
        .login-container {
            display: flex;
            width: 100%;
            height: 100%;
        }
        
        .login-left {
            flex: 1;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 2rem;
        }
        
        .login-right {
            flex: 1;
            background-color: #0066cc;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            padding: 2rem;
        }
        
        /* Pattern overlay for the blue background */
        .login-right::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 20%, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.1) 10%, transparent 10%),
                radial-gradient(circle at 80% 30%, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0.08) 15%, transparent 15%),
                radial-gradient(circle at 40% 70%, rgba(255,255,255,0.06) 0%, rgba(255,255,255,0.06) 12%, transparent 12%),
                radial-gradient(circle at 10% 90%, rgba(255,255,255,0.07) 0%, rgba(255,255,255,0.07) 8%, transparent 8%),
                radial-gradient(circle at 85% 85%, rgba(255,255,255,0.09) 0%, rgba(255,255,255,0.09) 18%, transparent 18%);
            background-size: 100% 100%;
            z-index: 1;
        }
        
        .logo-container {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .logo {
            height: 80px;
            margin: 0 10px;
        }
        
        .ministry-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            max-width: 80%;
            line-height: 1.3;
        }
        
        .directorate-title {
            font-size: 1.1rem;
            font-weight: 500;
            color: #555;
        }
        
        .form-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 400px;
        }
        
        .booking-title {
            color: white;
            font-size: 1.8rem;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 600;
        }
        
        input {
            width: 100%;
            padding: 1rem;
            margin-bottom: 1rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            background-color: rgba(255, 255, 255, 0.9);
        }
        
        input:focus {
            outline: none;
            background-color: white;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.5);
        }
        
        button {
            width: 100%;
            padding: 1rem;
            background-color: #004080;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0.5rem;
        }
        
        button:hover {
            background-color: #00336b;
        }
        
        .error-message {
            color: white;
            background-color: rgba(255, 0, 0, 0.7);
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                overflow-y: auto;
            }
            
            .login-left, .login-right {
                flex: none;
                width: 100%;
                height: auto;
                min-height: 50vh;
                padding: 2rem 1rem;
            }
            
            .ministry-title {
                font-size: 1.5rem;
            }
            
            .form-container {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-left">
        <div class="logo-container">
            <img src="gambar/Kemen.png" alt="Logo Kemendikbud" class="logo">
            <img src="gambar/LOGO 1 MITRAS.jpg" alt="Logo Mitras" class="logo">
        </div>
        <h1 class="ministry-title">DIREKTORAT KEMITRAAN DAN PENYELARASAN DUNIA USAHA DAN DUNIA INDUSTRI</h1>
        <h2 class="directorate-title">KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET DAN TEKNOLOGI</h2>
    </div>
    <div class="login-right">
        <div class="form-container">
            <h1 class="booking-title">Pemesanan Ruang Rapat</h1>
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="post" action="login.php">
                <input type="text" id="nip" name="nip" placeholder="NIP" required>
                <input type="password" id="password" name="password" placeholder="Password" required>
                <button type="submit">Masuk</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>