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

$startDate = '';
$endDate = '';
$roomFilter = '';

$query = "
    SELECT bookings.id, bookings.room, bookings.date, bookings.time_start, bookings.time_end, bookings.field, bookings.status, bookings.activity, users.nama 
    FROM bookings 
    JOIN users ON bookings.user_id = users.id
";

$conditions = [];

if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $startDate = $_GET['start_date'];
    $conditions[] = "bookings.date >= :start_date";
}
if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $endDate = $_GET['end_date'];
    $conditions[] = "bookings.date <= :end_date";
}
if (isset($_GET['room']) && !empty($_GET['room'])) {
    $roomFilter = $_GET['room'];
    $conditions[] = "bookings.room = :room";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY bookings.date, bookings.time_start";

$stmt = $pdo->prepare($query);
if ($startDate) {
    $stmt->bindParam(':start_date', $startDate);
}
if ($endDate) {
    $stmt->bindParam(':end_date', $endDate);
}
if ($roomFilter) {
    $stmt->bindParam(':room', $roomFilter);
}

$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all room options from database
$roomsQuery = $pdo->query("SELECT DISTINCT room FROM bookings ORDER BY room");
$roomOptions = $roomsQuery->fetchAll(PDO::FETCH_COLUMN);

// Get current date for display
$current_date = date('l, d F Y');

if (isset($_GET['generate_pdf'])) {
    require_once 'fpdf.php';
    class PDF extends FPDF {
        function Footer() {
            $this->SetY(-15); 
            $this->SetX(10);             
            $this->SetLineWidth(0.5); 
            $this->Line(10, $this->GetY(), 287, $this->GetY()); 
            $this->Ln(5); 
            $this->SetFont('Arial', '', 9); 
            $this->SetX(10); 
            $this->Cell(0, 5, 'Direktorat Kemitraan dan Penyelarasan Dunia Usaha dan Dunia Industri', 0, 1, 'L'); 
            $this->SetFont('Arial', 'I', 9); 
            $this->SetX(250); 
            $this->Cell(0, 5, 'Halaman ' . $this->PageNo() . ' dari {nb}', 0, 0, 'R'); 
        }
        
        // Menambahkan fungsi header untuk konsistensi
        function Header() {
            $this->Image('gambar/LOGO 1 MITRAS.jpg', 10, 10, 20); 
            $this->SetFont('Arial', 'B', 16);
            $this->SetX(40);
            $this->Cell(0, 10, 'DIREKTORAT KEMITRAAN DAN PENYELARASAN', 0, 1, 'C');
            $this->SetX(40);
            $this->Cell(0, 10, 'DUNIA USAHA DAN DUNIA INDUSTRI', 0, 1, 'C');
            $this->Ln(13);
            $this->SetLineWidth(0.5);
            $this->Line(10, $this->GetY(), 287, $this->GetY()); 
            $this->Ln(5);
        }
        
        // Menambahkan fungsi NbLines ke dalam kelas PDF
        function NbLines($w, $txt) {
            // Menghitung jumlah baris yang akan diambil oleh MultiCell dengan lebar w
            $cw = &$this->CurrentFont['cw'];
            if($w==0)
                $w = $this->w-$this->rMargin-$this->x;
            $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
            $s = str_replace("\r",'',$txt);
            $nb = strlen($s);
            if($nb>0 && $s[$nb-1]=="\n")
                $nb--;
            $sep = -1;
            $i = 0;
            $j = 0;
            $l = 0;
            $nl = 1;
            while($i<$nb) {
                $c = $s[$i];
                if($c=="\n") {
                    $i++;
                    $sep = -1;
                    $j = $i;
                    $l = 0;
                    $nl++;
                    continue;
                }
                if($c==' ')
                    $sep = $i;
                $l += $cw[ord($c)];
                if($l>$wmax) {
                    if($sep==-1) {
                        if($i==$j)
                            $i++;
                    }
                    else
                        $i = $sep+1;
                    $sep = -1;
                    $j = $i;
                    $l = 0;
                    $nl++;
                }
                else
                    $i++;
            }
            return $nl;
        }
    }
    
    $pdf = new PDF('L', 'mm', 'A4'); 
    $pdf->AliasNbPages(); 
    $pdf->AddPage();
    
    // Cetak stempel waktu
    date_default_timezone_set('Asia/Jakarta'); 
    $currentDateTime = date('d M Y, H:i');
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 10, 'Dicetak pada: ' . $currentDateTime, 0, 1, 'L');
    $pdf->Ln(5);
    
    // Judul laporan
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'LAPORAN REKAPITULASI PENGGUNAAN RUANG RAPAT MITRAS DUDI', 0, 1, 'C');
    $pdf->Ln(10);
    
    // Header tabel dengan lebar yang sudah ditingkatkan
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetFillColor(240, 240, 240);
    
    // Menghitung lebar kolom yang tepat
    $colWidth1 = 35;  // Tanggal
    $colWidth2 = 65;  // Ruang
    $colWidth3 = 35;  // Mulai Jam
    $colWidth4 = 35;  // Selesai Jam
    $colWidth5 = 55;  // Pemesan
    $colWidth6 = 52;  // Kegiatan
    
    // Menggambar header dengan warna latar
    $pdf->Cell($colWidth1, 10, 'Tanggal', 1, 0, 'C', true);
    $pdf->Cell($colWidth2, 10, 'Ruang', 1, 0, 'C', true);
    $pdf->Cell($colWidth3, 10, 'Mulai Jam', 1, 0, 'C', true);
    $pdf->Cell($colWidth4, 10, 'Selesai Jam', 1, 0, 'C', true);
    $pdf->Cell($colWidth5, 10, 'Pemesan', 1, 0, 'C', true);
    $pdf->Cell($colWidth6, 10, 'Kegiatan', 1, 1, 'C', true);
    
    // Isi tabel
    $pdf->SetFont('Arial', '', 11);
    
    foreach ($bookings as $booking) {
        // Alternatif tanpa menggunakan NbLines
        $cellHeight = 10;
        
        // Print cell dengan tinggi tetap
        $pdf->Cell($colWidth1, $cellHeight, $booking['date'], 1, 0, 'C');
        $pdf->Cell($colWidth2, $cellHeight, $booking['room'], 1, 0, 'C');
        $pdf->Cell($colWidth3, $cellHeight, $booking['time_start'], 1, 0, 'C');
        $pdf->Cell($colWidth4, $cellHeight, $booking['time_end'], 1, 0, 'C');
        $pdf->Cell($colWidth5, $cellHeight, $booking['nama'], 1, 0, 'C');
        $pdf->Cell($colWidth6, $cellHeight, $booking['activity'], 1, 1, 'C');
    }
    
    
    
    // Selesai mencetak tabel dan sebelum bagian tanda tangan
$pdf->Ln(5);

// Simpan posisi Y saat ini
$currentY = $pdf->GetY();

// Hitung total tinggi yang dibutuhkan untuk blok tanda tangan (dalam mm)
$ttdHeight = 55; // Perkiraan: 5 baris text + ruang tanda tangan + margin

// Cek apakah blok tanda tangan akan muat di halaman saat ini
$pageHeight = $pdf->GetPageHeight();
$bottomMargin = 15; // Footer height
$availableSpace = $pageHeight - $currentY - $bottomMargin;

if ($availableSpace < $ttdHeight) {
    // Jika tidak cukup ruang, buat halaman baru
    $pdf->AddPage();
}

// Sekarang tempatkan blok tanda tangan sebagai satu kesatuan
$pdf->SetFont('Arial', '', 11);
$rightColumnX = 210; // Posisi X untuk kolom tanda tangan

// Atur posisi untuk blok tanda tangan
$pdf->SetX($rightColumnX);
$pdf->Cell(77, 5, 'Bekasi, ' . date('d F Y'), 0, 1, 'C'); 
$pdf->SetX($rightColumnX);
$pdf->Cell(77, 5, 'Mengetahui,', 0, 1, 'C');
$pdf->SetX($rightColumnX);
$pdf->Cell(77, 5, 'Kasubbag Tata Usaha', 0, 1, 'C');
$pdf->Ln(20); // Ruang untuk tanda tangan
$pdf->SetX($rightColumnX);
$pdf->Cell(77, 5, '(Okto Maulana, S.T.)', 0, 1, 'C');
    
    $pdf->Output('I', 'Laporan_Jadwal.pdf');
    exit;    
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Ruangan - MITRAS DUDI</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
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

        .main-jadwal {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        .container-jadwal {
            max-width: 1600px;
            margin: 0 auto;
        }

        .dashboard {
            background-color: var(--card-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            animation: fadeIn 0.5s ease;
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

        /* Filter form styling */
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            background-color: var(--background-color);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 24px;
            align-items: center;
            border: 1px solid var(--border-color);
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            font-size: 0.875rem;
            color: var(--text-light);
            margin-bottom: 8px;
            font-weight: 500;
        }

        .filter-group input[type="date"], 
        .filter-group select {
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            color: var(--text-dark);
            transition: border-color 0.3s, box-shadow 0.3s;
            background-color: white;
        }

        .filter-group input[type="date"]:focus, 
        .filter-group select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            outline: none;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 24px;
            align-self: flex-end;
        }

        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 500;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            box-shadow: var(--shadow-md);
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #3cbe6e;
            box-shadow: var(--shadow-md);
        }

        .btn i {
            font-size: 1rem;
        }

        /* Table styling */
        .table-container {
            overflow-x: auto;
            margin-top: 24px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            background-color: white;
        }

        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: var(--background-color);
            color: var(--text-light);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            font-size: 0.95rem;
            color: var(--text-dark);
        }

        tr:hover {
            background-color: var(--background-color);
        }

        /* Status badges */
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-align: center;
        }

        .badge-success {
            background-color: rgba(74, 222, 128, 0.1);
            color: var(--success-color);
        }

        .badge-warning {
            background-color: rgba(250, 204, 21, 0.1);
            color: var(--warning-color);
        }

        .badge-danger {
            background-color: rgba(244, 63, 94, 0.1);
            color: var(--danger-color);
        }

        /* Actions */
        .action-button {
            padding: 6px 12px;
            border-radius: var(--border-radius);
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .delete-button {
            background-color: rgba(244, 63, 94, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(244, 63, 94, 0.2);
        }

        .delete-button:hover {
            background-color: var(--danger-color);
            color: white;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--text-light);
        }

        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Room tag */
        .room-tag {
            display: inline-block;
            padding: 4px 10px;
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .time-range {
            white-space: nowrap;
            font-family: monospace;
            font-size: 0.95rem;
            background-color: var(--background-color);
            padding: 4px 8px;
            border-radius: var(--border-radius);
        }

        /* Date formatting */
        .date-display {
            font-weight: 500;
        }

        /* Show highlighted date today */
        .is-today {
            background-color: rgba(74, 222, 128, 0.05);
        }
        
        /* No results state */
        .no-results {
            padding: 40px;
            text-align: center;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        
        .no-results i {
            font-size: 48px;
            color: var(--text-light);
            margin-bottom: 16px;
        }
        
        .no-results h3 {
            font-size: 1.5rem;
            color: var(--text-dark);
            margin-bottom: 8px;
        }
        
        .no-results p {
            color: var(--text-light);
            max-width: 500px;
            margin: 0 auto;
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
            
            .main-jadwal {
                margin-left: 80px;
            }
        }

        @media (max-width: 768px) {
            .main-jadwal {
                padding: 1rem;
            }
            
            .dashboard {
                padding: 1rem;
            }
            
            .filter-form {
                flex-direction: column;
                gap: 10px;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .button-group {
                width: 100%;
                justify-content: center;
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
        <a href="admin_jadwal.php" class="active">
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
                <i class="fas fa-user-shield"></i> <span>My Account</span>
            </a>
            <a href="logout.php" onclick="return confirmLogout();">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </div>
    </div>

    <div class="main-jadwal">
        <div class="container-jadwal">
            <div class="dashboard animate__animated animate__fadeIn">
                <div class="page-header">
                    <h2><i class="fas fa-calendar-check"></i> Jadwal Ruang</h2>
                    <span><?php echo $current_date; ?></span>
                </div>

                <!-- Filter Form -->
                <form method="GET" action="admin_jadwal.php" class="filter-form">
                    <div class="filter-group">
                        <label for="start_date">
                            <i class="fas fa-calendar-day"></i> Mulai Tanggal
                        </label>
                        <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
                    </div>

                    <div class="filter-group">
                        <label for="end_date">
                            <i class="fas fa-calendar-day"></i> Sampai Tanggal
                        </label>
                        <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
                    </div>

                    <div class="filter-group">
                        <label for="room">
                            <i class="fas fa-door-open"></i> Ruangan
                        </label>
                        <select name="room" id="room">
                            <option value="">-- Semua Ruangan --</option>
                            <?php foreach ($roomOptions as $room): ?>
                            <option value="<?= htmlspecialchars($room) ?>" <?= $roomFilter == $room ? 'selected' : '' ?>>
                                <?= htmlspecialchars($room) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button type="submit" name="generate_pdf" class="btn btn-success">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </button>
                    </div>
                </form>

                <?php if (count($bookings) > 0): ?>
                <!-- Table Container -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th><i class="fas fa-calendar-alt"></i> Tanggal</th>
                                <th><i class="fas fa-door-closed"></i> Ruang</th>
                                <th><i class="fas fa-clock"></i> Mulai Jam</th>
                                <th><i class="fas fa-clock"></i> Selesai Jam</th>
                                <th><i class="fas fa-user"></i> Pemesan</th>
                                <th><i class="fas fa-building"></i> Bidang</th>
                                <th><i class="fas fa-check-circle"></i> Status</th>
                                <th><i class="fas fa-clipboard-list"></i> Kegiatan</th>
                                <th><i class="fas fa-cog"></i> Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): 
                                // Check if date is today
                                $isToday = (date('Y-m-d') === $booking['date']) ? ' is-today' : '';
                            ?>
                                <tr class="<?= $isToday ?>">
                                    <td class="date-display"><?= htmlspecialchars($booking['date']) ?></td>
                                    <td><span class="room-tag"><?= htmlspecialchars($booking['room']) ?></span></td>
                                    <td class="time-range"><?= htmlspecialchars($booking['time_start']) ?></td>
                                    <td class="time-range"><?= htmlspecialchars($booking['time_end']) ?></td>
                                    <td><?= htmlspecialchars($booking['nama']) ?></td>
                                    <td><?= htmlspecialchars($booking['field']) ?></td>
                                    <td>
                                        <?php 
                                        $statusClass = '';
                                        switch(strtolower($booking['status'])) {
                                            case 'approved':
                                                $statusClass = 'badge-success';
                                                break;
                                            case 'pending':
                                                $statusClass = 'badge-warning';
                                                break;
                                            case 'rejected':
                                                $statusClass = 'badge-danger';
                                                break;
                                            default:
                                                $statusClass = '';
                                        }
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($booking['status']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($booking['activity']) ?></td>
                                    <td>
                                        <a href="delete_pemesanan.php?id=<?= $booking['id'] ?>" 
                                           onclick="return confirm('Are you sure you want to delete this schedule?');" 
                                           class="action-button delete-button">
                                            <i class="fas fa-trash-alt"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <!-- No results -->
                <div class="no-results">
                    <i class="fas fa-calendar-times"></i>
                    <h3>Tidak ada jadwal yang ditemukan</h3>
                    <p>Tidak ada jadwal kamar yang sesuai dengan filter yang dipilih. Coba ubah filter atau lihat semua jadwal.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function confirmLogout() {
            return confirm("Apakah Anda yakin ingin keluar?");
        }
        
        // Highlight current date rows
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const dateCell = row.querySelector('td:first-child');
                if (dateCell && dateCell.textContent === today) {
                    row.classList.add('is-today');
                }
            });
        });
        
        // Add some animation when filtering
        const filterForm = document.querySelector('.filter-form');
        filterForm.addEventListener('submit', function() {
            document.querySelector('.dashboard').classList.add('animate__animated', 'animate__fadeOut');
            setTimeout(() => {
                document.querySelector('.dashboard').classList.remove('animate__fadeOut');
            }, 500);
        });
    </script>
</body>
</html>