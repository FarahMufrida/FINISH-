<?php
// dev_mode = 1
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Fungsi Umum
function getNotifications($user_id) {
    require '../databases/database.php';
    
    $sql = "SELECT 
            p.payment_status,
            p.payment_date,
            w.title as workshop_title,
            CONCAT(u.first_name, ' ', u.last_name) as user_name,
            TIMESTAMPDIFF(MINUTE, p.payment_date, NOW()) as minutes_ago
        FROM payments p
        JOIN registrations r ON p.registration_id = r.registration_id
        JOIN workshops w ON r.workshop_id = w.workshop_id
        JOIN users u ON r.user_id = u.user_id
        WHERE p.payment_status = 'pending'
        ORDER BY p.payment_date DESC
        LIMIT 5";
        
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


// Fungsi Create - Menambahkan pengguna baru
function createUser($first_name, $last_name, $username, $password, $email, $role, $phone) {
    global $conn;
    
    // Check if email already exists
    $check_email = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($check_email);
    if($result && $result->num_rows > 0) {
        return "Email sudah terdaftar.";
    }
    
    // Check if username already exists and generate new username if needed
    $original_username = $username;
    $counter = 1;
    
    do {
        $check_username = "SELECT * FROM users WHERE username = '$username'";
        $result = $conn->query($check_username);
        if($result && $result->num_rows > 0) {
            $username = $original_username . $counter;
            $counter++;
        } else {
            break;
        }
    } while(true);

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Direct query without prepare
    $sql = "INSERT INTO users (first_name, last_name, username, password, email, role, phone) 
            VALUES ('$first_name', '$last_name', '$username', '$hashedPassword', '$email', '$role', '$phone')";
            
    if ($conn->query($sql)) {
        return "success";
    } else {
        return "Gagal menambahkan pengguna: " . $conn->error;
    }
}

// Fungsi Read - Mendapatkan semua pengguna
function getUsers() {
    require '../databases/database.php';
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

// Fungsi Read - Mendapatkan semua pengguna
function getUsersByRole($role) {
    require '../databases/database.php';
    $sql = "SELECT * FROM users WHERE role='$role'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

// Fungsi Read - Mendapatkan satu pengguna berdasarkan ID
function getUserById($user_id) {
    require '../databases/database.php';
    
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Fungsi Update - Memperbarui data pengguna berdasarkan ID
function updateUser($user_id, $first_name, $last_name, $username, $password, $email, $phone) {
    global $conn;

    // Check if a new password is provided, and hash it if so
    $hashedPassword = $password ? password_hash($password, PASSWORD_BCRYPT) : null;

    // Start building the SQL query
    $sql = "UPDATE users SET first_name = ?, last_name = ?, username = ?, email = ?, phone = ?";
    $params = [$first_name, $last_name, $username, $email, $phone];
    $types = "sssss";

    // Add password to SQL query if it is being updated
    if ($hashedPassword) {
        $sql .= ", password = ?";
        $params[] = $hashedPassword;
        $types .= "s";
    }

    // Finalize the query with the condition
    $sql .= " WHERE user_id = ?";
    $params[] = $user_id;
    $types .= "i";

    // Prepare and bind parameters dynamically
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    // Execute the query and return the result
    if ($stmt->execute()) {
        return "Pengguna berhasil diperbarui.";
    } else {
        return "Gagal memperbarui pengguna: " . $stmt->error;
    }
}



// Fungsi Delete - Menghapus pengguna berdasarkan ID
function deleteUser($user_id) {
    global $conn;
    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        return "Pengguna berhasil dihapus.";
    } else {
        return "Gagal menghapus pengguna: " . $stmt->error;
    }
}

// Fungsi untuk login
function login($username_email, $password, $role) {
    global $conn;
    $username_email = mysqli_real_escape_string($conn, $username_email);
    $password = mysqli_real_escape_string($conn, $password);
    $role = mysqli_real_escape_string($conn, $role);

    $query = "SELECT * FROM users WHERE (email = '$username_email' OR username = '$username_email') AND role = '$role'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            return ['status' => 'success', 'message' => 'Login successful'];
        }
        return ['status' => 'error', 'message' => 'Incorrect password'];
    }
    return ['status' => 'error', 'message' => 'No user found with this username/email and role'];
}



// ======= DASHBOARD DATA ======
// Rows Counter 
// Fungsi Count Row - Menghitung jumlah baris dalam tabel tertentu
function countRowsUsersByRole($role) {
    require '../databases/database.php';
    $sql = "SELECT COUNT(*) AS total FROM users WHERE role='$role'";
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    } else {
        return "Error: " . $conn->error;
    }
}

// Fungsi Count Row - Menghitung jumlah baris dalam tabel tertentu
function countWorkshops() {
    require '../databases/database.php';
    $sql = "SELECT COUNT(*) AS total FROM workshops";
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    } else {
        return "Error: " . $conn->error;
    }
}

// Fungsi Read - Mendapatkan workshop populer
function getPopularWorkshop() {
    require '../databases/database.php';

    $sql = "
        SELECT 
            workshops.*, 
            COUNT(registrations.user_id) AS totalpendaftar
        FROM 
            workshops
        LEFT JOIN 
            registrations ON workshops.workshop_id = registrations.workshop_id
        GROUP BY 
            workshops.workshop_id
        ORDER BY 
            totalpendaftar DESC
        LIMIT 9
    ";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

// Peserta bulanan
function getMonthlyParticipants() {
    require '../databases/database.php';
    $monthlyParticipants = [];

    for ($month = 1; $month <= 12; $month++) {
        $query = "SELECT COUNT(*) as total FROM users WHERE MONTH(created_at) = $month";
        $result = $conn->query($query);
        $data = $result->fetch_assoc();
        $monthlyParticipants[] = $data['total'];
    }

    return $monthlyParticipants;
}
// Fungsi untuk mengambil acara dari database
function getEvents() {
    require '../databases/database.php';
    
    // Get user role and ID from session
    $role = $_SESSION['role'];
    $user_id = $_SESSION['user_id'];
    
    // Base SQL query
    $sql = "SELECT title, start_date AS start, end_date AS end FROM workshops WHERE status = 'active'";
    
    // Add mitra filter if user is mitra
    if ($role === 'mitra') {
        $sql .= " AND mitra_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // For admin, fetch all workshops
        $result = $conn->query($sql);
    }

    $events = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $events[] = [
                'title' => $row['title'],
                'start' => $row['start'],
                'end' => $row['end']
            ];
        }
    }

    return $events;
}

// Rekap Data Keuangan
function getFinancialData() {
    require '../databases/database.php';
    
    $sql = "SELECT 
            r.registration_id,
            CONCAT(u.first_name, ' ', u.last_name) as nama_peserta,
            w.title as nama_workshop,
            w.price as harga_workshop,
            p.amount as jumlah_bayar,
            p.payment_method as metode_pembayaran,
            p.payment_status as status_pembayaran,
            DATE_FORMAT(p.payment_date, '%d/%m/%Y') as tanggal_pembayaran,
            r.status as status_registrasi,
            CONCAT(m.first_name, ' ', m.last_name) as nama_mitra  -- Menambahkan nama mitra
            FROM registrations r
            LEFT JOIN payments p ON r.registration_id = p.registration_id
            LEFT JOIN users u ON r.user_id = u.user_id AND u.role = 'user'  -- Filter role untuk peserta
            LEFT JOIN workshops w ON r.workshop_id = w.workshop_id
            LEFT JOIN users m ON w.mitra_id = m.user_id AND m.role = 'mitra'  -- Join dengan pengguna yang memiliki role mitra
            WHERE u.role = 'user'";  // Pastikan hanya peserta yang diambil

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

// Data Keuangan Admin
function getFinancialDataAdmin() {
    require '../databases/database.php';
    
    $sql = "SELECT 
            r.registration_id,
            CONCAT(u.first_name, ' ', u.last_name) as nama_peserta,
            w.title as nama_workshop,
            p.amount,
            p.payment_method,
            p.payment_status,
            p.payment_date,
            p.payment_receipt,
            p.payment_id,
            CONCAT(m.first_name, ' ', m.last_name) as nama_mitra
            FROM registrations r
            LEFT JOIN payments p ON r.registration_id = p.registration_id
            LEFT JOIN users u ON r.user_id = u.user_id
            LEFT JOIN workshops w ON r.workshop_id = w.workshop_id
            LEFT JOIN users m ON w.mitra_id = m.user_id
            WHERE p.payment_id IS NOT NULL
            ORDER BY p.payment_date DESC";

    $result = $conn->query($sql);
    return ($result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// // Fungsi untuk menghitung total penghasilan
function countTotalEarnings() {
    require '../databases/database.php';

    // Query untuk menghitung total penghasilan dari pembayaran yang statusnya 'successful'
    $sql = "SELECT SUM(p.amount) as total_penghasilan
            FROM payments p
            WHERE p.payment_status = 'successful'";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total_penghasilan'];
    } else {
        return 0; // Jika tidak ada data, kembalikan 0
    }
}

// Fungsi untuk mendapatkan data pengeluaran
function getExpensesData() {
    require '../databases/database.php'; // Pastikan koneksi database di-include di sini

    // Query untuk mengambil data pengeluaran dengan join ke tabel users dan memastikan role adalah 'mitra'
    $sql = "SELECT 
                e.expense_id,
                e.amount,
                e.description,
                e.category,
                DATE_FORMAT(e.expense_date, '%d/%m/%Y') as expense_date,
                CONCAT(u.first_name, ' ', u.last_name) as mitra_name  -- Menggabungkan first_name dan last_name untuk nama mitra
            FROM expenses e
            JOIN users u ON e.mitra_id = u.user_id  -- Join dengan tabel users berdasarkan mitra_id
            WHERE u.role = 'mitra'  -- Pastikan hanya mitra yang terambil
            ORDER BY e.expense_date DESC"; // Menampilkan pengeluaran berdasarkan tanggal

    // Menjalankan query dan mendapatkan hasilnya
    $result = $conn->query($sql);

    // Cek apakah ada hasil dari query
    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC); // Mengembalikan data dalam bentuk array asosiatif
    } else {
        return []; // Jika tidak ada data, mengembalikan array kosong
    }
}

// Fungsi untuk mendapatkan data pengeluaran
function getExpensesDataMitraId($mitra_id) {
    require '../databases/database.php'; // Pastikan koneksi database di-include di sini

    // Query untuk mengambil data pengeluaran dengan join ke tabel users dan memastikan role adalah 'mitra'
    $sql = "SELECT 
                e.expense_id,
                e.amount,
                e.description,
                e.category,
                DATE_FORMAT(e.expense_date, '%d/%m/%Y') as expense_date,
                CONCAT(u.first_name, ' ', u.last_name) as mitra_name  -- Menggabungkan first_name dan last_name untuk nama mitra
            FROM expenses e
            JOIN users u ON e.mitra_id = u.user_id  -- Join dengan tabel users berdasarkan mitra_id
            WHERE u.role = 'mitra' AND u.user_id = $mitra_id  -- Pastikan hanya mitra yang terambil
            ORDER BY e.expense_date DESC"; // Menampilkan pengeluaran berdasarkan tanggal

    // Menjalankan query dan mendapatkan hasilnya
    $result = $conn->query($sql);

    // Cek apakah ada hasil dari query
    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC); // Mengembalikan data dalam bentuk array asosiatif
    } else {
        return []; // Jika tidak ada data, mengembalikan array kosong
    }
}

// Fungsi untuk mendapatkan data pengeluaran berdasarkan ID
function getExpenseById($expense_id) {
    require '../databases/database.php';

    // Query untuk mengambil data pengeluaran berdasarkan expense_id
    $sql = "SELECT 
                e.expense_id,
                e.amount,
                e.description,
                e.category,
                DATE_FORMAT(e.expense_date, '%d/%m/%Y') as expense_date,
                CONCAT(u.first_name, ' ', u.last_name) as mitra_name
            FROM expenses e
            JOIN users u ON e.mitra_id = u.user_id
            WHERE e.expense_id = ?"; // Pastikan kita mencari pengeluaran dengan expense_id yang diberikan

    // Persiapkan dan eksekusi statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $expense_id); // Binding parameter
    $stmt->execute();
    $result = $stmt->get_result();

    // Mengembalikan data pengeluaran jika ditemukan
    if ($result->num_rows > 0) {
        return $result->fetch_assoc(); // Mengembalikan data pengeluaran
    } else {
        return null; // Jika tidak ditemukan
    }
}

// Fungsi untuk mendapatkan total pengeluaran berdasarkan mitra_id (user_id)
function getTotalPenghasilanByMitraId($user_id) {
    require '../databases/database.php';

    // Query untuk menghitung total pengeluaran berdasarkan user_id
    $sql = "SELECT 
                SUM(e.amount) as total_amount
            FROM expenses e
            JOIN users u ON e.mitra_id = u.user_id
            WHERE u.user_id = ?"; // Pastikan kita mencari pengeluaran dengan user_id yang diberikan

    // Persiapkan dan eksekusi statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id); // Binding parameter
    $stmt->execute();
    $result = $stmt->get_result();

    // Mengembalikan total pengeluaran jika ditemukan
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total_amount']; // Mengembalikan total pengeluaran
    } else {
        return 0; // Jika tidak ditemukan, kembalikan 0
    }
}

// Fungsi untuk mendapatkan data pengeluaran
function getExpenses() {
require '../databases/database.php';

$sql = "SELECT 
        expense_id,
        amount,
        description,
        category,
        DATE_FORMAT(expense_date, '%d/%m/%Y') as expense_date
        FROM expenses
        ORDER BY expense_date DESC";

$result = $conn->query($sql);

return ($result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Fungsi untuk menghitung total pengeluaran
function countTotalExpenses() {
    require '../databases/database.php';

    // Query untuk menghitung total pengeluaran
    $sql = "SELECT SUM(amount) as total_pengeluaran FROM expenses";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total_pengeluaran'] ?: 0; // Mengembalikan 0 jika null
    } else {
        return 0; // Jika tidak ada data, kembalikan 0
    }
}

// Fungsi untuk menghitung rekap keuangan (pendapatan - pengeluaran)
function getFinancialRecap() {
    $totalEarnings = countTotalEarnings(); // Pastikan fungsi ini ada dan benar
    $totalExpenses = countTotalExpenses();

    return [
        'total_earnings' => $totalEarnings,
        'total_expenses' => $totalExpenses,
        'balance' => $totalEarnings - $totalExpenses
    ];
}

// Fungsi untuk menambahkan pengeluaran
function addExpense($description, $category, $amount, $expense_date, $mitra_id) {
    global $conn;

    // Pastikan semua input valid
    $description = mysqli_real_escape_string($conn, $description);
    $category = mysqli_real_escape_string($conn, $category);
    $amount = mysqli_real_escape_string($conn, $amount);
    $expense_date = mysqli_real_escape_string($conn, $expense_date);
    $mitra_id = mysqli_real_escape_string($conn, $mitra_id);

    // Query untuk menambahkan pengeluaran
    $sql = "INSERT INTO expenses (description, category, amount, expense_date, mitra_id) 
            VALUES ('$description', '$category', '$amount', '$expense_date', '$mitra_id')";

    if ($conn->query($sql) === TRUE) {
        return "Pengeluaran berhasil ditambahkan!";
    } else {
        return "Terjadi kesalahan saat menambahkan pengeluaran: " . $conn->error;
    }
}

// Fungsi untuk menghapus pengeluaran
function deleteExpense($expense_id) {
    require '../databases/database.php';

    // Query untuk menghapus pengeluaran berdasarkan ID
    $sql = "DELETE FROM expenses WHERE expense_id = ?";

    // Persiapkan statement untuk menghindari SQL injection
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $expense_id);
    
    // Eksekusi query
    if ($stmt->execute()) {
        return "Pengeluaran berhasil dihapus.";
    } else {
        return "Gagal menghapus pengeluaran.";
    }
}

// Get Total Workshop User
function getTotalWorkshopsJoined($user_id) {
    require '../databases/database.php';
    
    $sql = "SELECT COUNT(DISTINCT r.workshop_id) as total_workshops
            FROM registrations r
            JOIN payments p ON r.registration_id = p.registration_id
            WHERE r.user_id = ? 
            AND p.payment_status = 'successful'";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total_workshops'];
}

// Get total Payments user
function getTotalPaymentsMade($user_id) {
    require '../databases/database.php';
    
    $sql = "SELECT COUNT(p.payment_id) as total_payments, 
            SUM(p.amount) as total_amount
            FROM payments p
            JOIN registrations r ON p.registration_id = r.registration_id
            WHERE r.user_id = ? 
            AND p.payment_status = 'successful'";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getWorkshopRatingsbyUser() {
    require '../databases/database.php';
    
    $sql = "SELECT f.*, 
            w.title as workshop_title,
            CONCAT(u.first_name, ' ', u.last_name) as user_name,
            w.mitra_id
            FROM feedback f
            JOIN workshops w ON f.workshop_id = w.workshop_id 
            JOIN users u ON f.user_id = u.user_id
            ORDER BY f.created_at DESC";
            
    $result = $conn->query($sql);
    return ($result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
}


// ========================================
//          LANDING PAGE FUNCTION
// ========================================
// Fetch Untuk Landing Page
function getWorkshopsWithMitra() {
    require '../databases/database.php';

    $query = "
        SELECT 
            w.workshop_id,
            w.title,
            w.description,
            w.banner,
            w.price,
            w.location,
            w.start_date,
            w.end_date,
            w.status,
            w.training_overview,
            w.trained_competencies,
            w.training_session,
            w.requirements,
            w.benefits,
            m.user_id AS mitra_id,
            m.first_name AS mitra_first_name,
            m.last_name AS mitra_last_name,
            m.email AS mitra_email,
            m.phone AS mitra_phone,
            DATEDIFF(w.end_date, w.start_date) + 1 AS duration_days,
            AVG(f.rating) as average_rating,
            COUNT(DISTINCT f.feedback_id) as total_reviews,
            COUNT(DISTINCT r.registration_id) as total_participants,
            GROUP_CONCAT(DISTINCT CONCAT(u.first_name, ' ', u.last_name)) as reviewer_names,
            GROUP_CONCAT(DISTINCT f.comment) as review_comments
        FROM workshops w
        LEFT JOIN users m ON w.mitra_id = m.user_id
        LEFT JOIN feedback f ON w.workshop_id = f.workshop_id
        LEFT JOIN users u ON f.user_id = u.user_id
        LEFT JOIN registrations r ON w.workshop_id = r.workshop_id
        WHERE m.role = 'mitra' AND w.status = 'active'
        GROUP BY w.workshop_id
        ORDER BY w.created_at DESC
    ";

    $result = mysqli_query($conn, $query);

    if ($result) {
        $workshops = mysqli_fetch_all($result, MYSQLI_ASSOC);
        return $workshops;
    } else {
        return "Error fetching workshops: " . mysqli_error($conn);
    }
}

function getWorkshopsWithFilter($sql) {
    require '../databases/database.php';
    
    $result = $conn->query($sql);
    if ($result) {
        $workshops = [];
        while($row = $result->fetch_assoc()) {
            $workshops[] = $row;
        }
        return $workshops;
    }
    return [];
}

function isPurchased($workshop_id, $user_id) {
    require '../databases/database.php';
    
    $sql = "SELECT 
            COUNT(r.registration_id) as purchase_count,
            MAX(p.payment_status) as payment_status
            FROM registrations r
            JOIN payments p ON r.registration_id = p.registration_id
            WHERE r.workshop_id = ? 
            AND r.user_id = ?
            AND p.payment_status = 'successful'";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $workshop_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    return [
        'is_purchased' => $result['purchase_count'] > 0,
        'purchase_count' => $result['purchase_count'],
        'payment_status' => $result['payment_status']
    ];
}


function getWorkshopsWithMitraPagination($page = 1, $limit = 6) {
    require '../databases/database.php';
    
    // Calculate offset
    $offset = ($page - 1) * $limit;
    
    // Get total records for pagination
    $count_query = "SELECT COUNT(*) as total FROM workshops w 
                   LEFT JOIN users m ON w.mitra_id = m.user_id 
                   WHERE m.role = 'mitra' AND w.status = 'active'";
    $count_result = mysqli_query($conn, $count_query);
    $total_records = mysqli_fetch_assoc($count_result)['total'];
    
    // Main query with LIMIT and OFFSET
    $query = "SELECT 
        w.workshop_id, w.title, w.description, w.banner, w.price,
        w.location, w.start_date, w.end_date, w.status,
        w.training_overview, w.trained_competencies, w.training_session,
        w.requirements, w.benefits, m.user_id AS mitra_id,
        m.first_name AS mitra_first_name, m.last_name AS mitra_last_name,
        m.email AS mitra_email, m.phone AS mitra_phone,
        DATEDIFF(w.end_date, w.start_date) + 1 AS duration_days,
        AVG(f.rating) as average_rating,
        COUNT(DISTINCT f.feedback_id) as total_reviews,
        COUNT(DISTINCT r.registration_id) as total_participants,
        GROUP_CONCAT(DISTINCT CONCAT(u.first_name, ' ', u.last_name)) as reviewer_names,
        GROUP_CONCAT(DISTINCT f.comment) as review_comments
        FROM workshops w
        LEFT JOIN users m ON w.mitra_id = m.user_id
        LEFT JOIN feedback f ON w.workshop_id = f.workshop_id
        LEFT JOIN users u ON f.user_id = u.user_id
        LEFT JOIN registrations r ON w.workshop_id = r.workshop_id
        WHERE m.role = 'mitra' AND w.status = 'active'
        GROUP BY w.workshop_id
        ORDER BY w.created_at DESC
        LIMIT ? OFFSET ?";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return [
        'workshops' => mysqli_fetch_all($result, MYSQLI_ASSOC),
        'total_pages' => ceil($total_records / $limit),
        'current_page' => $page
    ];
}


// ========================================
//          SESSION FUNCTION
// ========================================
function checkUserSession() {
    session_start();
    if(!isset($_SESSION['user_id'])) {
        return false;
    }
    return true;
}

function checkPeserta(){
    session_start();
    if(!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
        return false;
    }
    return true;
}

// Validasi sesi: Pastikan pengguna sudah login
function checkAuth() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Sesi anda telah habis. Silahkan login terlebih dahulu.');window.location='../pages/index.php';</script>";
        exit;
    }
}

// Validasi auth untuk input, update, dll
function checkInputAuth() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        $auth=false;
    }else{
        $auth=true;
    }
    return $auth;
}

// Validasi auth untuk input, update, dll
function checkMitraAuth() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mitra') {
        $auth=false;
    }else{
        $auth=true;
    }
    return $auth;
}

// Validasi ketika di halaman login
function checkAuthorized(){
    session_start();
    if (isset($_SESSION['user_id'])) {
        echo "<script>alert('Anda sudah login. Akan diarahkan ke dashboard.');window.location='dashboard.php';</script>";
    }
}
// Validasi peran admin untuk beberapa aksi (hanya admin yang bisa menambah, mengubah, atau menghapus pengguna)
function checkAdmin() {
    session_start();
    if ($_SESSION['role'] !== 'admin') {
        $_SESSION['error_message'] = "Akses ditolak. Hanya admin yang dapat melakukan aksi ini.";
        header('Location: dashboard.php');
        exit;
    }
}

// ========================================
//          WORKSHOP CRUD
// ========================================
// Buat workshop oleh mitra
function createWorkshop($mitra_id, $title, $description, $banner, $training_overview, $trained_competencies, 
                       $training_session, $requirements, $benefits, $price, $location, $start_date, $end_date, $status) {
    global $conn;

    $sql = "INSERT INTO workshops (mitra_id, title, description, banner, training_overview, 
            trained_competencies, training_session, requirements, benefits, price, location, 
            start_date, end_date, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssssdssss", $mitra_id, $title, $description, $banner, 
                      $training_overview, $trained_competencies, $training_session, 
                      $requirements, $benefits, $price, $location, $start_date, $end_date, $status);

    if ($stmt->execute()) {
        return "Workshop berhasil dibuat.";
    }
    return "Gagal membuat workshop: " . $stmt->error;
}

// Upload banner oleh mitra
function handleBannerUpload($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    // Check upload directory
    $upload_dir = "../pages/assets/img/workshops/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Validate file
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Format file harus JPG/PNG');
    }
    
    if ($file['size'] > $max_size) {
        throw new Exception('Ukuran file maksimal 2MB');
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "WS-" . time() . "." . $ext;
    $upload_path = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $filename;
    }
    
    throw new Exception('Gagal mengupload file');
}


// Get data workshop
function getAllWorkshops() {
    require '../databases/database.php';
    
    $sql = "SELECT 
            w.*,
            CONCAT(u.first_name, ' ', u.last_name) as mitra_name,
            AVG(f.rating) as average_rating,
            COUNT(DISTINCT f.feedback_id) as total_reviews,
            COUNT(DISTINCT r.registration_id) as total_participants
            FROM workshops w 
            LEFT JOIN users u ON w.mitra_id = u.user_id 
            LEFT JOIN feedback f ON w.workshop_id = f.workshop_id
            LEFT JOIN registrations r ON w.workshop_id = r.workshop_id
            GROUP BY w.workshop_id
            ORDER BY w.created_at DESC";
    
    $result = $conn->query($sql);
    return ($result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
}


function getWorkshopById($workshop_id) {
    require '../databases/database.php';
    
    $sql = "SELECT w.*, CONCAT(u.first_name, ' ', u.last_name) as mitra_name 
            FROM workshops w 
            LEFT JOIN users u ON w.mitra_id = u.user_id 
            WHERE w.workshop_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $workshop_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Get Workshop By Mitra ID
function getWorkshopByMitraId($mitra_id) {
    require '../databases/database.php';
    
    $sql = "SELECT w.*, CONCAT(u.first_name, ' ', u.last_name) as mitra_name 
            FROM workshops w 
            LEFT JOIN users u ON w.mitra_id = u.user_id 
            WHERE w.mitra_id = ?
            ORDER BY w.created_at DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $mitra_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}


// Update Function
function updateWorkshop($workshop_id, $title, $description, $banner, $training_overview, 
                       $trained_competencies, $training_session, $requirements, $benefits, 
                       $price, $location, $start_date, $end_date, $status) {
    global $conn;

    $sql = "UPDATE workshops 
            SET title = ?, description = ?, banner = ?, training_overview = ?, 
                trained_competencies = ?, training_session = ?, requirements = ?, 
                benefits = ?, price = ?, location = ?, start_date = ?, 
                end_date = ?, status = ? 
            WHERE workshop_id = ?";

    $stmt = $conn->prepare($sql);
    // Added 'i' at the end for workshop_id parameter
    $stmt->bind_param("ssssssssdssssi", $title, $description, $banner, $training_overview, 
                      $trained_competencies, $training_session, $requirements, $benefits, 
                      $price, $location, $start_date, $end_date, $status, $workshop_id);

    if ($stmt->execute()) {
        return "Workshop berhasil diperbarui.";
    }
    return "Gagal memperbarui workshop: " . $stmt->error;
}

// Hapus workshop
function deleteWorkshop($workshop_id) {
    global $conn;
    
    // First get the banner filename to delete the image file
    $sql = "SELECT banner FROM workshops WHERE workshop_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $workshop_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $workshop = $result->fetch_assoc();
    
    // Delete the banner image if it exists
    if($workshop && $workshop['banner'] != 'sample.jpg') {
        $banner_path = "../pages/assets/img/workshop/" . $workshop['banner'];
        if(file_exists($banner_path)) {
            unlink($banner_path);
        }
    }
    
    // Delete the workshop record
    $sql = "DELETE FROM workshops WHERE workshop_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $workshop_id);
    
    if ($stmt->execute()) {
        return "Workshop berhasil dihapus.";
    }
    return "Gagal menghapus workshop: " . $stmt->error;
}
// ==================
// Dashboard Mitra
// ==================
// Count total earnings for specific mitra
function countMitraEarnings($mitra_id) {
    require '../databases/database.php';
    $sql = "SELECT SUM(p.amount) as total_earnings
            FROM payments p
            JOIN registrations r ON p.registration_id = r.registration_id
            JOIN workshops w ON r.workshop_id = w.workshop_id
            WHERE w.mitra_id = ? AND p.payment_status = 'successful'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $mitra_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total_earnings'] ?? 0;
}

// Count participants for specific mitra's workshops
function countMitraParticipants($mitra_id) {
    require '../databases/database.php';
    $sql = "SELECT COUNT(DISTINCT r.user_id) as total_participants
            FROM registrations r
            JOIN workshops w ON r.workshop_id = w.workshop_id
            WHERE w.mitra_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $mitra_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total_participants'] ?? 0;
}

// Count workshops for specific mitra
function countMitraWorkshops($mitra_id) {
    require '../databases/database.php';
    $sql = "SELECT COUNT(*) as total_workshops FROM workshops WHERE mitra_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $mitra_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total_workshops'] ?? 0;
}

// Get monthly participants for mitra's workshops
function getMitraMonthlyParticipants($mitra_id) {
    require '../databases/database.php';
    $monthlyParticipants = array_fill(0, 12, 0);
    
    $sql = "SELECT MONTH(r.registration_date) as month, COUNT(*) as total
            FROM registrations r
            JOIN workshops w ON r.workshop_id = w.workshop_id
            WHERE w.mitra_id = ?
            GROUP BY MONTH(r.registration_date)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $mitra_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        $monthlyParticipants[$row['month']-1] = $row['total'];
    }
    
    return $monthlyParticipants;
}

// Get quick list of mitra's workshops
function getMitraWorkshopsList($mitra_id) {
    require '../databases/database.php';
    $sql = "SELECT workshop_id, title, status, 
            (SELECT COUNT(*) FROM registrations WHERE workshop_id = workshops.workshop_id) as participant_count
            FROM workshops 
            WHERE mitra_id = ?
            ORDER BY created_at DESC
            LIMIT 5";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $mitra_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


// Fungsi untuk mengambil data mitra dari database
function getDataMitra() {
    // Menghubungkan ke database
    require '../databases/database.php';

    // Query untuk mengambil data mitra dengan role 'mitra'
    $sql = "SELECT user_id, first_name, last_name, email, phone FROM users WHERE role = 'mitra' ORDER BY first_name ASC";
    
    // Menjalankan query
    $result = $conn->query($sql);

    // Mengambil hasil query dan menyimpannya dalam array
    $mitraData = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Menyimpan setiap data mitra ke dalam array
            $mitraData[] = [
                'mitra_id' => $row['user_id'],
                'name' => $row['first_name'] . ' ' . $row['last_name'],  // Menggabungkan nama depan dan belakang
                'email' => $row['email'],
                'phone' => $row['phone']
            ];
        }
    }

    // Mengembalikan data mitra
    return $mitraData;
}



// ====================================================================
//  PAYMENT FUNCTION
// ====================================================================
function getPaymentData($user_id) {
    require '../databases/database.php';

    $sql = "SELECT 
                p.*,
                r.registration_date,
                r.status as registration_status,
                w.title as workshop_title,
                w.price,
                w.status,
                w.tipe,
                w.media_pembelajaran,
                w.location,
                w.start_date,
                w.end_date,
                CONCAT(m.first_name, ' ', m.last_name) as mitra_name
            FROM payments p
            INNER JOIN registrations r ON p.registration_id = r.registration_id
            INNER JOIN workshops w ON r.workshop_id = w.workshop_id
            INNER JOIN users m ON w.mitra_id = m.user_id
            WHERE r.user_id = ?
            ORDER BY p.payment_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    return ($result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Create workshop registration
function createWorkshopRegistration($user_id, $workshop_id) {
    global $conn;
    $sql = "INSERT INTO registrations (user_id, workshop_id, status) 
            VALUES (?, ?, 'registered')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $workshop_id);
    
    if($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

// Handle payment receipt upload
function handlePaymentUpload($file, $registration_id) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    // Check upload directory
    $upload_dir = "../pages/assets/img/payment/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Validate file
    if (!in_array($file['type'], $allowed_types)) {
        return ['status' => false, 'message' => 'Format file harus JPG/PNG'];
    }
    
    if ($file['size'] > $max_size) {
        return ['status' => false, 'message' => 'Ukuran file maksimal 2MB'];
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "INV-" . $registration_id . "." . $ext;
    $upload_path = $upload_dir . $filename;
    
    // Check if directory is writable
    if (!is_writable($upload_dir)) {
        return ['status' => false, 'message' => 'Directory tidak dapat ditulis'];
    }
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['status' => true, 'filename' => $filename];
    }
    
    // Log error if upload fails
    error_log("Upload failed for file: " . $file['name'] . " to path: " . $upload_path);
    return ['status' => false, 'message' => 'Gagal mengupload file'];
}


// Create payment record
function createPaymentRecord($registration_id, $amount, $payment_receipt, $bank_id) {
    global $conn;
    $sql = "INSERT INTO payments (registration_id, amount, payment_method, payment_status, payment_receipt, bank_id, payment_date) 
            VALUES (?, ?, 'bank_transfer', 'pending', ?, ?, CURRENT_TIMESTAMP)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idsi", $registration_id, $amount, $payment_receipt, $bank_id);
    return $stmt->execute();
}
// ======================================
// FITUR CHAT
// ======================================
// Get all chat contacts for current user
function getChatContacts($user_id) {
    require '../databases/database.php';
    
    $sql = "SELECT DISTINCT 
            u.user_id,
            u.first_name,
            u.last_name,
            u.role,
            u.username,
            (SELECT message 
             FROM chats 
             WHERE (sender_id = u.user_id AND receiver_id = ?) 
                OR (sender_id = ? AND receiver_id = u.user_id)
             ORDER BY sent_at DESC 
             LIMIT 1) as last_message,
            (SELECT sent_at 
             FROM chats 
             WHERE (sender_id = u.user_id AND receiver_id = ?) 
                OR (sender_id = ? AND receiver_id = u.user_id)
             ORDER BY sent_at DESC 
             LIMIT 1) as last_message_time,
            (SELECT COUNT(*) 
             FROM chats 
             WHERE sender_id = u.user_id 
             AND receiver_id = ? 
             AND is_read = 0) as unread_count
            FROM users u
            JOIN chats c ON u.user_id = c.sender_id OR u.user_id = c.receiver_id
            WHERE (c.sender_id = ? OR c.receiver_id = ?)
            AND u.user_id != ?
            GROUP BY u.user_id
            ORDER BY last_message_time DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get chat history between two users
function getChatHistory($sender_id, $receiver_id) {
    require '../databases/database.php';
    
    $sql = "SELECT c.*, 
            CONCAT(s.first_name, ' ', s.last_name) as sender_name,
            CONCAT(r.first_name, ' ', r.last_name) as receiver_name
            FROM chats c
            JOIN users s ON c.sender_id = s.user_id
            JOIN users r ON c.receiver_id = r.user_id
            WHERE (c.sender_id = ? AND c.receiver_id = ?)
            OR (c.sender_id = ? AND c.receiver_id = ?)
            ORDER BY c.sent_at ASC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Send new message
function sendMessage($sender_id, $receiver_id, $message) {
    require '../databases/database.php';
    
    $sql = "INSERT INTO chats (sender_id, receiver_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
    return $stmt->execute();
}

// Mark messages as read
function markMessagesAsRead($sender_id, $receiver_id) {
    require '../databases/database.php';
    
    $sql = "UPDATE chats SET is_read = 1 
            WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $sender_id, $receiver_id);
    return $stmt->execute();
}

function searchUsers($search_term) {
    require '../databases/database.php';
    $sql = "SELECT user_id, username, email, first_name, last_name 
            FROM users 
            WHERE (email LIKE ? OR username LIKE ?) 
            AND user_id != ?
            LIMIT 5";
            
    $search_term = "%$search_term%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $search_term, $search_term, $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


// Get unread messages count and details
function getUnreadMessages($user_id) {
    require '../databases/database.php';
    
    $sql = "SELECT c.*, 
            CONCAT(s.first_name, ' ', s.last_name) as sender_name,
            s.user_id as sender_id
            FROM chats c
            JOIN users s ON c.sender_id = s.user_id
            WHERE c.receiver_id = ? 
            AND c.is_read = 0
            ORDER BY c.sent_at DESC 
            LIMIT 5";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get total unread count
    $sql = "SELECT COUNT(*) as total 
            FROM chats 
            WHERE receiver_id = ? 
            AND is_read = 0";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    
    return [
        'total_unread' => $total,
        'messages' => $messages
    ];
}


// Profil Function
function getUserProfile($user_id) {
    require '../databases/database.php';
    $sql = "SELECT user_id, username, first_name, last_name, email, phone,profile_photo, role, created_at 
            FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}


function updateUserProfile($user_id, $first_name, $last_name, $email, $phone, $profile_photo) {
    require '../databases/database.php';

    
    $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, profile_photo =?  WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone,$profile_photo, $user_id);
    return $stmt->execute();
}

function updateUserPassword($user_id, $current_password, $new_password) {
    require '../databases/database.php';
    
    // Verify current password
    $sql = "SELECT password FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if(!password_verify($current_password, $result['password'])) {
        return false;
    }
    
    // Update to new password
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    $sql = "UPDATE users SET password = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $hashed_password, $user_id);
    return $stmt->execute();
}

// ================================
//  RATING FUNCTION
// ================================
function getPurchasedWorkshops($user_id) {
    require '../databases/database.php';
    
    $sql = "SELECT 
            w.*,
            r.registration_id,
            p.payment_status,
            f.rating as user_rating,
            f.comment,
            f.feedback_id
            FROM workshops w
            JOIN registrations r ON w.workshop_id = r.workshop_id
            JOIN payments p ON r.registration_id = p.registration_id
            LEFT JOIN feedback f ON w.workshop_id = f.workshop_id AND f.user_id = ?
            WHERE r.user_id = ? 
            AND p.payment_status = 'successful'
            ORDER BY r.registration_date DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


// Add new rating
function addRating($user_id, $workshop_id, $rating, $comment) {
    require '../databases/database.php';
    
    $sql = "INSERT INTO feedback (user_id, workshop_id, rating, comment) 
            VALUES (?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $user_id, $workshop_id, $rating, $comment);
    return $stmt->execute();
}

// Update existing rating
function updateRating($feedback_id, $rating, $comment) {
    require '../databases/database.php';
    
    $sql = "UPDATE feedback 
            SET rating = ?, 
                comment = ?, 
                created_at = CURRENT_TIMESTAMP 
            WHERE feedback_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $rating, $comment, $feedback_id);
    return $stmt->execute();
}

function getWorkshopRatings() {
    require '../databases/database.php';
    
    $sql = "SELECT f.*, 
            w.title as workshop_title,
            CONCAT(u.first_name, ' ', u.last_name) as user_name,
            w.mitra_id
            FROM feedback f
            JOIN workshops w ON f.workshop_id = w.workshop_id 
            JOIN users u ON f.user_id = u.user_id
            ORDER BY f.created_at DESC";
            
    $result = $conn->query($sql);
    return ($result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
}





?>