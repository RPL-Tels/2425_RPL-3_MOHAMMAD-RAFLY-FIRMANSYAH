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
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $_SESSION['user_id'] ?? 0]); // Use 0 or some invalid ID if user_id doesn't exist
$is_admin = $stmt->fetchColumn();
if (isset($_POST['Approve'])) {
    $booking_id = $_POST['booking_id'];
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'Approved' WHERE id = :booking_id");
    $stmt->execute(['booking_id' => $booking_id]);
    
    // Set success message
    $_SESSION['message'] = "Pemesanan telah disetujui!";
    $_SESSION['message_type'] = "success";
    
    // Redirect to avoid form resubmission
    header("Location: admin_approve.php");
    exit();
} elseif (isset($_POST['Reject'])) {
    $booking_id = $_POST['booking_id'];
    $reason = $_POST['reason'];
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'Rejected' WHERE id = :booking_id");
    $stmt->execute(['booking_id' => $booking_id]);
    $stmt = $pdo->prepare("INSERT INTO rejection_log (booking_id, reason) VALUES (:booking_id, :reason)");
    $stmt->execute(['booking_id' => $booking_id, 'reason' => $reason]);
    
    // Set success message
    $_SESSION['message'] = "Pemesanan telah ditolak!";
    $_SESSION['message_type'] = "error";
    
    // Redirect to avoid form resubmission
    header("Location: admin_approve.php");
    exit();
}
$stmt = $pdo->query("SELECT b.id, b.room, b.date, b.time_start, b.time_end, b.field, b.activity, u.nama AS full_name 
                     FROM bookings b 
                     JOIN users u ON b.user_id = u.id 
                     WHERE b.status = 'Pending'
                     ORDER BY b.date ASC, b.time_start ASC");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Approve Pemesanan</title>
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

        .booking-count {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
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

        .booking-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            table-layout: fixed; /* Fixed table layout to control column widths */
        }

        /* Define specific widths for each column */
        .booking-table th:nth-child(1), .booking-table td:nth-child(1) { width: 15%; } /* Name */
        .booking-table th:nth-child(2), .booking-table td:nth-child(2) { width: 12%; } /* Room */
        .booking-table th:nth-child(3), .booking-table td:nth-child(3) { width: 12%; } /* Date */
        .booking-table th:nth-child(4), .booking-table td:nth-child(4) { width: 15%; } /* Time */
        .booking-table th:nth-child(5), .booking-table td:nth-child(5) { width: 10%; } /* Field */
        .booking-table th:nth-child(6), .booking-table td:nth-child(6) { width: 20%; } /* Activity */
        .booking-table th:nth-child(7), .booking-table td:nth-child(7) { width: 16%; } /* Action */

        .booking-table thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            text-align: left;
            padding: 1rem;
            font-size: 0.875rem;
            position: relative;
        }

        .booking-table th:after {
            content: '';
            position: absolute;
            right: 0;
            top: 25%;
            height: 50%;
            width: 1px;
            background-color: rgba(255, 255, 255, 0.2);
        }

        .booking-table th:last-child:after {
            display: none;
        }

        .booking-table tbody tr {
            background-color: var(--card-color);
            transition: all var(--transition-speed) ease;
        }

        .booking-table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .booking-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.9375rem;
            color: var(--text-dark);
        }

        .booking-table tr:last-child td {
            border-bottom: none;
        }

        .booking-table tbody tr:nth-child(even) {
            background-color: rgba(241, 245, 249, 0.5);
        }

        .date-cell {
            white-space: nowrap;
            font-weight: 500;
        }

        .time-cell {
            white-space: nowrap;
            color: var(--text-light);
        }

        .field-badge {
            background-color: rgba(96, 165, 250, 0.1);
            color: #3b82f6;
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            display: inline-block;
        }

        .activity-cell {
            max-width: 200px;
            white-space: normal !important; /* Force text wrapping */
            word-break: break-word;
            overflow-wrap: break-word;
            line-height: 1.4;
            text-overflow: initial; /* Remove ellipsis if any */
        }

        .action-cell {
            width: 200px;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            border: none;
            width: 100%;
        }

        .btn-approve {
            background-color: var(--success-color);
            color: white;
        }

        .btn-approve:hover {
            background-color: #22c55e;
        }

        .btn-reject {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-reject:hover {
            background-color: #e11d48;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 0;
            text-align: center;
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--text-light);
            max-width: 400px;
        }
        
        /* Modal styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }
        
        .modal {
            background-color: var(--card-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            width: 90%;
            max-width: 500px;
            padding: 1.5rem;
            position: relative;
            animation: slideUp 0.3s ease;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .modal-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .modal-header h3 i {
            color: var(--danger-color);
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--text-light);
            cursor: pointer;
            transition: color var(--transition-speed) ease;
        }
        
        .modal-close:hover {
            color: var(--danger-color);
        }
        
        .modal-body {
            margin-bottom: 1.5rem;
        }
        
        .modal-body p {
            margin-bottom: 1rem;
            color: var(--text-light);
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 0.9375rem;
            transition: all var(--transition-speed) ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }
        
        .btn-cancel {
            background-color: #e5e7eb;
            color: var(--text-dark);
        }
        
        .btn-cancel:hover {
            background-color: #d1d5db;
        }
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
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
            
            .main-admin {
                margin-left: 80px;
            }
            
            .booking-table th:nth-child(5),
            .booking-table td:nth-child(5),
            .booking-table th:nth-child(6),
            .booking-table td:nth-child(6) {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .main-admin {
                padding: 1rem;
            }
            
            .container-admin {
                padding: 1rem;
            }
            
            .booking-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .modal {
                width: 95%;
                padding: 1rem;
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
        <a href="admin_approve.php" class="active">
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

    <div class="main-admin">
        <div class="container-admin">
            <div class="page-header">
                <h2><i class="fas fa-clipboard-check"></i> Admin Approve </h2>
                <span class="booking-count"><?php echo count($bookings); ?> Menunggu</span>
            </div>
            
            <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                    <i class="fas fa-<?php echo $_SESSION['message_type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $_SESSION['message']; ?>
                </div>
                <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
            <?php endif; ?>
            
            <?php if(count($bookings) > 0): ?>
                <table class="booking-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Ruang</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Bidang</th>
                            <th>Kegiatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['room']); ?></td>
                                <td class="date-cell"><?php echo date('d M Y', strtotime($booking['date'])); ?></td>
                                <td class="time-cell"><?php echo htmlspecialchars($booking['time_start']); ?> - <?php echo htmlspecialchars($booking['time_end']); ?></td>
                                <td><span class="field-badge"><?php echo htmlspecialchars($booking['field']); ?></span></td>
                                <td class="activity-cell" title="<?php echo htmlspecialchars($booking['activity']); ?>"><?php echo htmlspecialchars($booking['activity']); ?></td>
                                <td class="action-cell">
                                    <div class="action-buttons">
                                        <form method="post" action="admin_approve.php">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <button type="submit" name="Approve" class="btn btn-approve">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        
                                        <!-- Just the reject button - modal will handle the form -->
                                        <button type="button" class="btn btn-reject show-reject-modal" data-booking="<?php echo $booking['id']; ?>" data-name="<?php echo htmlspecialchars($booking['full_name']); ?>" data-room="<?php echo htmlspecialchars($booking['room']); ?>" data-date="<?php echo date('d M Y', strtotime($booking['date'])); ?>" data-time="<?php echo htmlspecialchars($booking['time_start']); ?> - <?php echo htmlspecialchars($booking['time_end']); ?>">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-check"></i>
                    <h3>Tidak Ada Permintaan</h3>
                    <p>Tidak ada permintaan pemesanan kamar yang menunggu persetujuan saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3><i class="fas fa-times-circle"></i> Reject Pemesanan</h3>
                <button type="button" class="modal-close" id="closeModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Anda akan menolak permintaan pemesanan berikut:</p>
                <div class="booking-details">
                    <p><strong>Nama:</strong> <span id="modalBookingName"></span></p>
                    <p><strong>Ruang:</strong> <span id="modalBookingRoom"></span></p>
                    <p><strong>Tanggal & Waktu:</strong> <span id="modalBookingDateTime"></span></p>
                </div>
                <form id="rejectForm" method="post" action="admin_approve.php">
                    <input type="hidden" name="booking_id" id="modalBookingId">
                    <div class="form-group">
                        <label for="reason" class="form-label">Alasan Rejection:</label>
                        <textarea name="reason" id="reason" class="form-control" rows="3" placeholder="Please provide a reason for rejection" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-cancel" id="cancelReject">Cancel</button>
                        <button type="submit" name="Reject" class="btn btn-sm btn-reject">
                            <i class="fas fa-paper-plane"></i> Submit Rejection
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
        
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('rejectModal');
            const rejectButtons = document.querySelectorAll('.show-reject-modal');
            const closeModal = document.getElementById('closeModal');
            const cancelReject = document.getElementById('cancelReject');
            
            // Open modal when reject button is clicked
            rejectButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const bookingId = this.getAttribute('data-booking');
                    const name = this.getAttribute('data-name');
                    const room = this.getAttribute('data-room');
                    const date = this.getAttribute('data-date');
                    const time = this.getAttribute('data-time');
                    
                    // Set modal content
                    document.getElementById('modalBookingId').value = bookingId;
                    document.getElementById('modalBookingName').textContent = name;
                    document.getElementById('modalBookingRoom').textContent = room;
                    document.getElementById('modalBookingDateTime').textContent = date + ' | ' + time;
                    
                    // Show modal
                    modal.style.display = 'flex';
                    
                    // Focus on reason textarea
                    document.getElementById('reason').focus();
                });
            });
            
            // Close modal functions
            function closeModalFunction() {
                modal.style.display = 'none';
                document.getElementById('reason').value = ''; // Clear the form
            }
            
            closeModal.addEventListener('click', closeModalFunction);
            cancelReject.addEventListener('click', closeModalFunction);
            
            // Close modal when clicking outside of it
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeModalFunction();
                }
            });
            
            // Prevent form submission if reason is empty
            document.getElementById('rejectForm').addEventListener('submit', function(e) {
                const reason = document.getElementById('reason').value.trim();
                if (!reason) {
                    e.preventDefault();
                    alert("Please provide a reason for rejection.");
                }
            });
        });
    </script>
</body>
</html>
