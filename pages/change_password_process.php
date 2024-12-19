<?php
session_start();

// Mengecek apakah form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil email dan password baru dari form
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];

    // Koneksi ke database
    $conn = new mysqli('localhost', 'root', '', 'worksmart1');
    if ($conn->connect_error) {
        die('Could not connect to the database');
    }

    // Mengecek apakah email sudah ada di database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
       // Hash password baru
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // Debug: menampilkan hash password
        var_dump($new_password_hash); // Untuk memverifikasi apakah password ter-hash dengan benar

        // Update password di database
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update_stmt->bind_param("s", $new_password_hash, $email);;

        if ($update_stmt->execute()) {
            // Redirect ke halaman login setelah reset password sukses
            $_SESSION['reset_password_success'] = "Password successfully updated. Please login again.";
            header("Location: success.php");
            exit();
        } else {
            echo "Error updating password.";
        }

        $update_stmt->close();
    } else {
        echo "Email not found in the database.";
    }

    // Menutup koneksi
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h2>Reset Password</h2>
    <form method="POST" action="reset_password.php">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="new_password" class="form-label">New Password</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required>
        </div>
        <button type="submit" class="btn btn-primary">Reset Password</button>
    </form>
</div>
</body>
</html>