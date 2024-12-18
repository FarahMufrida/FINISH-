<?php
require '../controllers/function.php';

// Memastikan user sudah terautentikasi
checkAuth();

if (isset($_GET['expense_id'])) {
    $expense_id = $_GET['expense_id'];

    // Ambil data pengeluaran berdasarkan ID
    $expense = getExpenseById($expense_id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $expense['expense_id'] ?> - WorkSmart</title>
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .invoice-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 2px solid #ddd;
            border-radius: 10px;
            background: #fff;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #5271ff;
        }

        .logo {
            width: 150px;
        }

        .invoice-title {
            text-align: right;
            color: #5271ff;
        }

        .invoice-details {
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .expense-details {
            margin: 20px 0;
            padding: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .payment-info {
            margin-top: 20px;
            text-align: right;
        }

        .total-amount {
            font-size: 24px;
            color: #5271ff;
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
        }

        .print-button {
            background: #5271ff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 20px 0;
        }
    </style>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <img src="assets/img/logo-worksmart.png" alt="WorkSmart Logo" class="logo">
            <div class="invoice-title">
                <h1>INVOICE</h1>
                <h3>#<?= $expense['expense_id'] ?></h3>
            </div>
        </div>

        <div class="invoice-details">
            <table width="100%">
                <tr>
                    <td width="50%">
                        <strong>Tanggal Transfer:</strong><br>
                        <?= date('d F Y', strtotime($expense['expense_date'])) ?>
                    </td>
                    <td width="50%" style="text-align: right;">
                        <strong>Jumlah:</strong><br>
                        <span class="total-amount">Rp <?= number_format($expense['amount'], 0, ',', '.') ?></span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="expense-details">
            <h3>Detail Transfer</h3>
            <table width="100%">
                <tr>
                    <td width="30%"><strong>Deskripsi</strong></td>
                    <td>: <?= $expense['description'] ?></td>
                </tr>
                <tr>
                    <td><strong>Kategori</strong></td>
                    <td>: <?= $expense['category'] ?></td>
                </tr>
                <tr>
                    <td><strong>Mitra</strong></td>
                    <td>: <?= $expense['mitra_name'] ?></td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Terima kasih telah menggunakan layanan WorkSmart</p>
            <p>Untuk informasi lebih lanjut hubungi support@worksmart.id</p>
        </div>

        <button class="print-button no-print" onclick="window.print()">Print Invoice</button>
    </div>
</body>
</html>
