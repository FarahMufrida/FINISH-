<?php
require '../controllers/function.php';
checkAuth();
$data = getFinancialDataAdmin();
$pengeluaran = countTotalExpenses();
$penghasilan = getFinancialRecap();
$expenses = getExpensesData();
$mitradata =  getDataMitra();
$expensesMitra = getExpensesDataMitraId($_SESSION['user_id']);

$isAdmin = ($_SESSION['role'] == 'admin');
$isMitra = ($_SESSION['role'] == 'mitra');

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Dashboard - WorkSmart</title>
  <meta content="WorkSmart" name="description">
  <meta content="WorkSmart" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/logo-worksmart.png" rel="icon">
  <link href="assets/img/logo-worksmart.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  
  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">
  <link href="assets/css/brand.css" rel="stylesheet">
  
</head>

<body>

  <?php require 'header.php'; ?>
  <?php require 'sidebar.php'; ?>

  <main id="main" class="main brand-bg-color">

    <div class="pagetitle">
      <h1 class="text-light">Data Keuangan</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Data Keuangan</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <?php require 'alert.php'; ?>
    
    <?php if($isAdmin){ ?>

    <section class="section dashboard">
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-12">
          <div class="row">
        

          <!-- Penghasilan Card -->
          <div class="col-xxl-12 col-md-12">
            <div class="card info-card revenue-card">
              <div class="card-body">
                <h5 class="card-title">Est. Penghasilan (Semua Workshop)</h5>
                <div class="d-flex align-items-center">
                  <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-currency-dollar"></i>
                  </div>
                  <div class="ps-3">
                      <h6>Rp. <?php echo number_format($penghasilan['total_earnings'] ?? 0); ?></h6>
                      <span class="text-muted small pt-2">Total Penghasilan</span>
                  </div>

                </div>
              </div>
            </div>
          </div>

            <!-- Penghasilan Card -->
            <div class="col-xxl-6 col-md-6">
              <div class="card info-card revenue-card">
                <div class="card-body">
                  <h5 class="card-title">Total Penghasilan</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="ps-3">
                      <h6>Rp. <?php echo number_format($penghasilan['balance']); ?></h6>
                      <span class="text-muted small pt-2">Total Penghasilan</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        
            <!-- Pengeluaran Card -->
            <div class="col-xxl-6 col-md-6">
              <div class="card info-card sales-card">
                  <div class="card-body">
                      <h5 class="card-title">Pengeluaran</h5>
                      <div class="d-flex align-items-center">
                          <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                              <i class="bi bi-cart"></i>
                          </div>
                          <div class="ps-3">
                              <h6>Rp. <?php echo number_format($pengeluaran); ?></h6>
                              <span class="text-muted small pt-2">Total Pengeluaran</span>
                          </div>
                      </div>
                  </div>
              </div>
          </div>

        
          </div>
        </div>
        
          <!-- Full side columns -->
          <div class="col-lg-12">
          <div class="card">
              <div class="card-body">
                <h5 class="card-title">Data Keuangan</h5>
                <p class="text-dark">Berikut adalah data keuangan.</p>
                <a href="laporan.php?kategori=Keuangan" class="brand-btn btn mt-2 mb-4 rounded-pill"><i class="bi bi-cash me-2"></i>Export Data</a>
                <a href="#" onclick="location.reload();" class="brand-btn btn mt-2 mb-4 rounded-pill"><i class="bi bi-arrow-clockwise me-2"></i>Refresh</a>              
                
                <!-- Fetch Data Keuangan dari db -->
                <div class="table-responsive">
                <table class="table table-striped table-hover dt-responsive nowrap" id="participantTable" style="width:100%">
                    <thead>
                      <tr>
                        <th>No</th>
                        <th>Nama Workshop</th>
                        <th>Nama Mitra</th>
                        <th>Nama Pendaftar</th>
                        <th>Status Bayar</th>
                        <th>Metode Bayar</th>
                        <th>Tanggal Bayar</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $no = 1;
                      foreach($data as $datas) {
                      ?>
                        <tr>
                          <td><?= $no++ ?></td>
                          <td><?= $datas['nama_workshop'] ?></td>
                          <td><?= $datas['nama_mitra'] ?></td>
                          <td><?= $datas['nama_peserta'] ?></td>
                          <td>
                            <?php if($datas['payment_status'] == 'pending'): ?>
                              <span class="badge bg-warning">Pending</span>
                            <?php elseif($datas['payment_status'] == 'successful'): ?>
                              <span class="badge bg-success">Sukses</span>
                            <?php else: ?>
                              <span class="badge bg-danger">Gagal</span>
                            <?php endif; ?>
                          </td>
                          <td><?= $datas['payment_method'] ?></td>
                          <td><?= date('d/m/Y H:i', strtotime($datas['payment_date'])) ?></td>
                          <td>Rp. <?= number_format($datas['amount'], 0, ',', '.') ?></td>
                          <td>
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detailModal<?= $datas['payment_id'] ?>">
                              <i class="bi bi-eye"></i>
                            </button>
                            <?php if($datas['payment_status'] != 'successful'): ?>
                              <button id="verifyBtn<?= $datas['payment_id'] ?>" class="btn btn-sm btn-success" onclick="verifyPayment(<?= $datas['payment_id'] ?>)">
                                <i class="bi bi-check-circle"></i>
                              </button>
                            <?php endif; ?>
                          </td>
                        </tr>

                        <!-- Detail Modal -->
                        <div class="modal fade" id="detailModal<?= $datas['payment_id'] ?>" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel<?= $datas['payment_id'] ?>" aria-hidden="false">
                          <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="detailModalLabel<?= $datas['payment_id'] ?>">Detail Transaksi</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                <div class="row mb-3">
                                  <div class="col-12">
                                    <img src="assets/img/payment/<?= $datas['payment_receipt'] ?>" class="img-fluid" alt="Bukti Transfer">
                                  </div>
                                </div>
                                <div class="row">
                                  <div class="col-6"><strong>Workshop:</strong></div>
                                  <div class="col-6"><?= $datas['nama_workshop'] ?></div>
                                </div>
                                <div class="row">
                                  <div class="col-6"><strong>Peserta:</strong></div>
                                  <div class="col-6"><?= $datas['nama_peserta'] ?></div>
                                </div>
                                <div class="row">
                                  <div class="col-6"><strong>Jumlah:</strong></div>
                                  <div class="col-6">Rp. <?= number_format($datas['amount']) ?></div>
                                </div>
                                <div class="row">
                                  <div class="col-6"><strong>Tanggal:</strong></div>
                                  <div class="col-6"><?= $datas['payment_date'] ?></div>
                                </div>
                                <div class="row">
                                  <div class="col-6"><strong>Status:</strong></div>
                                  <div class="col-6"><?= $datas['payment_status'] ?></div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      <?php } ?>
                    </tbody>
                  </table>

                </div>

              </div>
            </div>
          </div>
          <!-- Full side columns -->

          <!-- Data Pengeluaran -->
          <div class="col-lg-12">
              <div class="card">
                  <div class="card-body">
                      <h5 class="card-title">Data Pengeluaran</h5>
                      <p class="text-dark">Berikut adalah data pengeluaran yang tercatat.</p>
                      
                      <!-- Ganti tombol Export dengan Tambah Pengeluaran -->
                      <button class="brand-btn btn mt-2 mb-4 rounded-pill" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                          <i class="bi bi-send me-2"></i>Transfer Mitra
                      </button>
                      <a href="#" onclick="location.reload();" class="brand-btn btn mt-2 mb-4 rounded-pill">
                          <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                      </a>

                      <!-- Fetch Data Pengeluaran dari db -->
                      <div class="table-responsive">
                          <table class="table table-striped table-hover dt-responsive nowrap" id="expenseTable" style="width:100%">
                              <thead>
                                  <tr>
                                      <th>No</th>
                                      <th>Deskripsi</th>
                                      <th>Kategori</th>
                                      <th>Tanggal</th>
                                      <th>Jumlah</th>
                                      <th>Mitra</th> <!-- Kolom Mitra ditambahkan -->
                                      <th>Aksi</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  <?php
                                  $no = 1; // Fungsi ini diambil dari controllers
                                  foreach ($expenses as $expense) {
                                  ?>
                                      <tr>
                                          <td><?= $no++ ?></td>
                                          <td><?= $expense['description'] ?></td>
                                          <td><?= $expense['category'] ?></td>
                                          <td><?= date('d/m/Y', strtotime($expense['expense_date'])) ?></td>
                                          <td>Rp. <?= number_format($expense['amount'], 0, ',', '.') ?></td>
                                          <td><?= $expense['mitra_name'] ?></td> <!-- Tampilkan nama mitra yang ada di data $expenses -->
                                          <td>
                                              <!-- View Details Button -->
                                              <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detailExpenseModal<?= $expense['expense_id'] ?>">
                                                  <i class="bi bi-eye"></i>
                                              </button>
                                              <!-- Print Invoice Button -->
                                              <button class="btn btn-sm btn-primary" onclick="printInvoice(<?= $expense['expense_id'] ?>)">
                                                  <i class="bi bi-printer"></i> Print Invoice
                                              </button>
                                              <!-- Delete Button -->
                                              <button class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus pengeluaran ini?') ? window.location.href='../controllers/controller.php?deleteExpense=<?= $expense['expense_id'] ?>' : false;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                          </td>
                                      </tr>

                                      <!-- Detail Modal -->
                                      <div class="modal fade" id="detailExpenseModal<?= $expense['expense_id'] ?>" tabindex="-1" role="dialog" aria-labelledby="detailExpenseModalLabel<?= $expense['expense_id'] ?>" aria-hidden="true">
                                          <div class="modal-dialog modal-dialog-centered" role="document">
                                              <div class="modal-content">
                                                  <div class="modal-header">
                                                      <h5 class="modal-title" id="detailExpenseModalLabel<?= $expense['expense_id'] ?>">Detail Pengeluaran</h5>
                                                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                  </div>
                                                  <div class="modal-body">
                                                      <div class="row">
                                                          <div class="col-6"><strong>Deskripsi:</strong></div>
                                                          <div class="col-6"><?= $expense['description'] ?></div>
                                                      </div>
                                                      <div class="row">
                                                          <div class="col-6"><strong>Kategori:</strong></div>
                                                          <div class="col-6"><?= $expense['category'] ?></div>
                                                      </div>
                                                      <div class="row">
                                                          <div class="col-6"><strong>Tanggal:</strong></div>
                                                          <div class="col-6"><?= date('d/m/Y', strtotime($expense['expense_date'])) ?></div>
                                                      </div>
                                                      <div class="row">
                                                          <div class="col-6"><strong>Jumlah:</strong></div>
                                                          <div class="col-6">Rp. <?= number_format($expense['amount']) ?></div>
                                                      </div>
                                                      <div class="row">
                                                          <div class="col-6"><strong>Mitra:</strong></div>
                                                          <div class="col-6"><?= $expense['mitra_name'] ?></div> <!-- Tampilkan nama mitra yang ada di data $expenses -->
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                  <?php } ?>
                              </tbody>
                          </table>
                      </div>
                  </div>
              </div>
          </div>

          <!-- Modal untuk Tambah Pengeluaran -->
          <div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                      <div class="modal-header">
                          <h5 class="modal-title" id="addExpenseModalLabel">Tambah Pengeluaran</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                          <form action="../controllers/controller.php" method="POST">
                              <div class="mb-3">
                                  <label for="description" class="form-label">Deskripsi Pengeluaran</label>
                                  <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                              </div>
                              <div class="mb-3">
                                  <label for="category" class="form-label">Kategori</label>
                                  <input type="text" class="form-control" id="category" name="category" required>
                              </div>
                              <div class="mb-3">
                                  <label for="amount" class="form-label">Jumlah Transfer</label>
                                  <input type="number" class="form-control" id="amount" name="amount" required>
                              </div>
                              <div class="mb-3">
                                  <label for="expense_date" class="form-label">Tanggal Pengeluaran</label>
                                  <input type="date" class="form-control" id="expense_date" name="expense_date" required>
                              </div>
                              <div class="mb-3">
                                  <label for="mitra_id" class="form-label">Pilih Mitra</label>
                                  <select class="form-select" id="mitra_id" name="mitra_id" required>
                                      <option value="" disabled selected>Pilih Mitra</option>
                                      <?php
                                      // Menampilkan data mitra dari variabel $mitradata
                                      foreach ($mitradata as $mitra) {
                                          echo "<option value='{$mitra['mitra_id']}'>{$mitra['name']}</option>";
                                      }
                                      ?>
                                  </select>
                              </div>
                              <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                  <button type="submit" class="btn btn-primary" name="addExpense">Simpan Pengeluaran</button>
                              </div>
                          </form>
                      </div>
                  </div>
              </div>
          </div>


      </div>
    </section>  

    <?php }else if($isMitra){
      ?>

      <section class="section dashboard">
                  <!-- Data Pengeluaran -->
                  <div class="col-lg-12">
              <div class="card">
                  <div class="card-body">
                      <h5 class="card-title">Data Transaksi</h5>
                      <p class="text-dark">Berikut adalah data transaksi yang tercatat.</p>

                      <a href="#" onclick="location.reload();" class="brand-btn btn mt-2 mb-4 rounded-pill">
                          <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                      </a>

                      <!-- Fetch Data Pengeluaran dari db -->
                      <div class="table-responsive">
                          <table class="table table-striped table-hover dt-responsive nowrap" id="expenseTable" style="width:100%">
                              <thead>
                                  <tr>
                                      <th>No</th>
                                      <th>Deskripsi</th>
                                      <th>Kategori</th>
                                      <th>Tanggal</th>
                                      <th>Jumlah</th>
                                      <th>Mitra</th> <!-- Kolom Mitra ditambahkan -->
                                      <th>Aksi</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  <?php
                                  $no = 1; // Fungsi ini diambil dari controllers
                                  foreach ($expensesMitra as $expense) {
                                  ?>
                                      <tr>
                                          <td><?= $no++ ?></td>
                                          <td><?= $expense['description'] ?></td>
                                          <td><?= $expense['category'] ?></td>
                                          <td><?= date('d/m/Y', strtotime($expense['expense_date'])) ?></td>
                                          <td>Rp. <?= number_format($expense['amount'], 0, ',', '.') ?></td>
                                          <td><?= $expense['mitra_name'] ?></td> <!-- Tampilkan nama mitra yang ada di data $expenses -->
                                          <td>
                                              <!-- View Details Button -->
                                              <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detailExpenseModal<?= $expense['expense_id'] ?>">
                                                  <i class="bi bi-eye"></i>
                                              </button>
                                              <!-- Print Invoice Button -->
                                              <button class="btn btn-sm btn-primary" onclick="printInvoice(<?= $expense['expense_id'] ?>)">
                                                  <i class="bi bi-printer"></i> Print Invoice
                                              </button>
                                          </td>
                                      </tr>

                                      <!-- Detail Modal -->
                                      <div class="modal fade" id="detailExpenseModal<?= $expense['expense_id'] ?>" tabindex="-1" role="dialog" aria-labelledby="detailExpenseModalLabel<?= $expense['expense_id'] ?>" aria-hidden="true">
                                          <div class="modal-dialog modal-dialog-centered" role="document">
                                              <div class="modal-content">
                                                  <div class="modal-header">
                                                      <h5 class="modal-title" id="detailExpenseModalLabel<?= $expense['expense_id'] ?>">Detail Pengeluaran</h5>
                                                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                  </div>
                                                  <div class="modal-body">
                                                      <div class="row">
                                                          <div class="col-6"><strong>Deskripsi:</strong></div>
                                                          <div class="col-6"><?= $expense['description'] ?></div>
                                                      </div>
                                                      <div class="row">
                                                          <div class="col-6"><strong>Kategori:</strong></div>
                                                          <div class="col-6"><?= $expense['category'] ?></div>
                                                      </div>
                                                      <div class="row">
                                                          <div class="col-6"><strong>Tanggal:</strong></div>
                                                          <div class="col-6"><?= date('d/m/Y', strtotime($expense['expense_date'])) ?></div>
                                                      </div>
                                                      <div class="row">
                                                          <div class="col-6"><strong>Jumlah:</strong></div>
                                                          <div class="col-6">Rp. <?= number_format($expense['amount']) ?></div>
                                                      </div>
                                                      <div class="row">
                                                          <div class="col-6"><strong>Mitra:</strong></div>
                                                          <div class="col-6"><?= $expense['mitra_name'] ?></div> <!-- Tampilkan nama mitra yang ada di data $expenses -->
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                  <?php } ?>
                              </tbody>
                          </table>
                      </div>
                  </div>
              </div>
          </div>
      </section>

    <?php }else{
        echo "<script>alert('Anda tidak memiliki akses ke halaman ini!'); window.location.href = 'index.php';</script>";
    } ?>
  </main>
  </main><!-- End #main -->

  <?php require "modals.php";?>

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer brand-bg-color">
    <div class="copyright text-light">
      © Copyright <strong><span>WorkSmart</span></strong>. All Rights Reserved
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.js"></script>
  
  <!-- DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>
  <script src="assets/js/autohide.js"></script>

  <script>
      // Fungsi untuk mencetak invoice
      function printInvoice(expenseId) {
          // Menyiapkan link untuk cetak invoice, bisa ditambahkan URL khusus untuk mencetak invoice
          const invoiceUrl = 'print_invoice.php?expense_id=' + expenseId;
          // Membuka URL di jendela baru untuk mencetak
          window.open(invoiceUrl, '_blank');
      }
  </script>

  <script>
      $(document).ready(function() {
          // Inisialisasi DataTable pada tabel dengan ID "expenseTable"
          $('#expenseTable').DataTable({
              "responsive": true,  // Responsif, untuk tampilan layar kecil
              "language": {
                  "lengthMenu": "Tampilkan _MENU_ data per halaman",
                  "zeroRecords": "Tidak ada data yang ditemukan",
                  "info": "Menampilkan _PAGE_ dari _PAGES_",
                  "infoEmpty": "Tidak ada data",
                  "infoFiltered": "(disaring dari _MAX_ total data)",
                  "search": "Cari:"
              },
              "ordering": true, // Mengaktifkan pengurutan kolom
              "pageLength": 10, // Menampilkan 10 baris per halaman
              "lengthChange": true, // Menyediakan pilihan untuk memilih jumlah baris per halaman
              "columnDefs": [
                  { "orderable": false, "targets": [6] } // Membuat kolom aksi (kolom ke-7) tidak bisa diurutkan
              ]
          });
      });
  </script>


  <!-- Initialize DataTable -->
  <script>
    $(document).ready(function() {
      $('#participantTable').DataTable({
        responsive: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
          {
            extend: 'copy',
            className: 'btn btn-sm btn-primary'
          },
          {
            extend: 'excel',
            className: 'btn btn-sm btn-success'
          },
          {
            extend: 'pdf',
            className: 'btn btn-sm btn-danger'
          },
          {
            extend: 'print',
            className: 'btn btn-sm btn-info'
          }
        ],  
        language: {
          search: "_INPUT_",
          searchPlaceholder: "Search records...",
          lengthMenu: "_MENU_ records per page",
          info: "Showing _START_ to _END_ of _TOTAL_ entries",
          paginate: {
            first: '<i class="bi bi-chevron-double-left"></i>',
            previous: '<i class="bi bi-chevron-left"></i>',
            next: '<i class="bi bi-chevron-right"></i>',
            last: '<i class="bi bi-chevron-double-right"></i>'
          }
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        order: [[0, 'asc']],
        drawCallback: function() {
          $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        }
      });
    });

    function verifyPayment(paymentId) {
    if(confirm('Verifikasi pembayaran ini?')) {
        $.ajax({
            url: '../controllers/controller.php',
            type: 'POST',
            data: {
                verifyPayment: true,
                payment_id: paymentId
            },
            success: function(response) {
                alert('Pembayaran berhasil diverifikasi');
                location.reload();
            },
            error: function() {
                alert('Terjadi kesalahan');
            }
        });
    }
}


  </script>

</body>

</html>