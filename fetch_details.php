<?php
$host = 'localhost';
$db = 'projek_ruangan';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (isset($_GET['status'])) {
        $status = $_GET['status'];
        $stmt = $pdo->prepare("SELECT room, date, time_start, time_end, field, orderer status FROM bookings WHERE status = ?");
        $stmt->execute([$status]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($data);
    }
} catch (PDOException $e) {
    echo json_encode([]);
}
?>