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
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $deleteLogQuery = "DELETE FROM rejection_log WHERE booking_id = :id";
    $deleteLogStmt = $pdo->prepare($deleteLogQuery);
    $deleteLogStmt->bindParam(':id', $id);
    $deleteLogStmt->execute();
    $query = "DELETE FROM bookings WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id);
    if ($stmt->execute()) {
        header("Location: admin_jadwal.php");
        exit;
    } else {
        echo "Gagal menghapus data.";
    }
} else {
    echo "ID tidak ditemukan.";
}
?>
