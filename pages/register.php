<?php
require '../controllers/function.php';
checkAuthorized();
?>
<html>
  <head>
    <title class="brand-color">Daftar Sekarang</title>
    <link href="assets/img/logo-worksmart.png" rel="icon" />
    <link href="assets/img/logo-worksmart.png" rel="apple-touch-icon" />
    <link
      crossorigin="anonymous"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
      rel="stylesheet"
    />
    <link href="assets/css/brand.css" rel="stylesheet" />
    <style>
      body {
        background-color: #02396f;
        font-family: "Arial", sans-serif;
      }
      .container {
        background-color: white;
        padding: 30px;
        max-width: 85%;
        margin: 50px auto;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      }
      .form-control {
        border-radius: 8px;
        margin-bottom: 15px;
        padding: 12px 15px;
      }
      .btn-primary {
        background-color: #cccccc;
        border: none;
        color: #666666;
        padding: 10px 20px;
        border-radius: 5px;
      }
      .btn-primary:hover {
        background-color: #bbbbbb;
      }
      .password-toggle {
        border-radius: 0 8px 8px 0;
        height: 100%;
        padding: 12px 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border: 1px solid #ced4da;
        cursor: pointer;
      }
    </style>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const togglePassword = document.querySelector('#togglePassword');
        const passwordField = document.querySelector('#password');
        togglePassword.addEventListener('click', function () {
          const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
          passwordField.setAttribute('type', type);
          this.textContent = type === 'password' ? 'Show' : 'Hide';
        });
      });

      function validateForm(event) {
        const passwordField = document.querySelector('#password');
        const passwordValue = passwordField.value;

        // Validasi kombinasi huruf dan angka minimal 8 karakter
        const passwordRegex = /^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
        if (!passwordRegex.test(passwordValue)) {
          event.preventDefault(); // Mencegah pengiriman form
          alert('Password harus terdiri dari kombinasi huruf dan angka dengan minimal 8 karakter.');
        }
      }

      function formatPhoneNumber(input) {
        let value = input.value;
        if (value.startsWith("0")) {
          input.value = "62" + value.substring(1);
        }
      }
    </script>
  </head>
  <body>
    <div id="loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 9999;">
      <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    </div>
    <div class="container rounded-4">
      <div class="row">
        <div class="col-md-6">
          <h2 class="brand-color">Daftar Sekarang</h2>
          <form method="POST" action="../controllers/controller.php" onsubmit="validateForm(event)">
            <?php if(isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
              <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
              ?>
            </div>
            <?php endif; ?>
            <div class="row">
              <div class="col-md-6">
                <label for="firstname" class="form-label brand-color">First name</label>
                <input class="form-control" id="firstname" name="first_name" type="text" required />
              </div>
              <div class="col-md-6">
                <label for="lastname" class="form-label brand-color">Last name</label>
                <input class="form-control" id="lastname" name="last_name" type="text" required />
              </div>
            </div>
            <label for="email" class="form-label brand-color">Alamat Email</label>
            <input class="form-control" id="email" name="email" type="email" required />
            <label for="phone" class="form-label brand-color">Nomor Telepon</label>
            <input class="form-control" id="phone" name="phone" type="number" required oninput="formatPhoneNumber(this)" />
            <div class="mb-3">
              <label for="password" class="form-label brand-color">Password</label>
              <div class="input-group">
                <input type="password" class="form-control password-input" id="password" name="password" required />
                <button class="btn btn-outline-secondary password-toggle" id="togglePassword" type="button">Show</button>
              </div>
              <small class="brand-color">Gunakan minimal 8 karakter terdiri dari huruf dan angka</small>
            </div>
            <div class="row mt-3 mb-4">
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" id="peserta" name="role" type="radio" value="user" required />
                  <label class="form-check-label brand-color" for="peserta">Peserta</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" id="mitra" name="role" type="radio" value="mitra" required />
                  <label class="form-check-label brand-color" for="mitra">Mitra</label>
                </div>
              </div>
            </div>
            <div class="form-check">
              <input class="form-check-input" id="shareData" name="shareData" type="checkbox" required />
              <label class="form-check-label brand-color" for="shareData">
                Share my registration data with our content providers for marketing purposes.
              </label>
            </div> <br>
            <div class="row align-items-center">
              <div class="col-md-6">
                <button class="btn btn-primary rounded-pill btn-lg" name="createUser" type="submit">Sign up</button>
              </div>
              <div class="col-md-6">
                <p class="brand-color mb-0">
                  Already have an account?
                  <a href="index.php" class="brand-color"> Log in </a>
                </p>
              </div>
            </div>
          </form>
        </div>
        <div class="col-md-6">
          <div class="d-flex align-items-center justify-content-center h-100">
            <img src="assets/img/logo-worksmart.png" class="img-fluid" />
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
