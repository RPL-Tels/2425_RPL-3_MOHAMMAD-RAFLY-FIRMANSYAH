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
$id = $_GET['id'];
try {
    $pdo->beginTransaction();
    
    // 1. Hapus dulu data di tabel rejection_log yang merujuk ke booking
    $stmt = $pdo->prepare("DELETE FROM rejection_log WHERE booking_id IN (SELECT id FROM bookings WHERE user_id = :id)");
    $stmt->execute(['id' => $id]);
    
    // 2. Kemudian hapus data bookings
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE user_id = :id");
    $stmt->execute(['id' => $id]);
    
    // 3. Terakhir hapus user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    
    $pdo->commit();
    $_SESSION['success'] = "Data user berhasil dihapus";
    header("Location: admin_datauser.php");
    exit;
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Gagal menghapus data: " . $e->getMessage();
    header("Location: admin_datauser.php");
    exit;
}
?>