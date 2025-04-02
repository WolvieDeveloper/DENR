<?php 
// session_start();
// if (!isset($_SESSION['id'])) {
//   header("location:admin-login.php?notif=SessionExpired");
//   exit;
// }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DENR Admin Registration</title>
    <!-- Bootstrap CSS -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <!-- Custom Styles -->
    <style>
      :root {
        --denr-green-dark: #1b5e20;
        --denr-green-medium: #2E7D32;
        --denr-green-light: #4CAF50;
        --denr-green-background: #E8F5E9;
      }

      body {
        background: linear-gradient(135deg, var(--denr-green-background) 0%, #c8e6c9 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Arial', sans-serif;
      }

      .register-container {
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        border-radius: 15px;
        overflow: hidden;
        border: 2px solid var(--denr-green-medium);
      }

      .register-header {
        background: linear-gradient(to right, var(--denr-green-dark), var(--denr-green-medium));
        color: white;
        padding: 20px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .register-header::before {
        content: '🍃'; /* Leaf emoji as a nature-themed icon */
        margin-right: 15px;
        font-size: 1.5em;
      }

      .form-control {
        border-radius: 8px;
        transition: all 0.3s ease;
        border-color: var(--denr-green-light);
      }

      .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(46, 125, 50, 0.25);
        border-color: var(--denr-green-medium);
      }

      .btn-primary {
        background: linear-gradient(to right, var(--denr-green-dark), var(--denr-green-medium));
        border: none;
        transition: all 0.3s ease;
      }

      .btn-primary:hover {
        transform: translateY(-3px);
        background: linear-gradient(to right, var(--denr-green-medium), var(--denr-green-light));
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      }

      .form-check-input:checked {
        background-color: var(--denr-green-medium);
        border-color: var(--denr-green-dark);
      }

      .input-group-text {
        background-color: var(--denr-green-light);
        color: white;
        border: none;
      }

      @media (max-width: 576px) {
        .register-container {
          width: 95%;
          margin: 0 auto;
        }
      }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card register-container">
            <div class="register-header">
              <h2 class="mb-0">DENR Admin Registration</h2>
            </div>
            <div class="card-body p-4">
              <form action="config.php" method="post" id="registrationForm">
                <div class="mb-3">
                  <label for="email" class="form-label">Email Address</label>
                  <div class="input-group">
                    <span class="input-group-text">@</span>
                    <input
                      type="email"
                      class="form-control"
                      name="email"
                      id="email"
                      placeholder="Enter your DENR email"
                      required
                    />
                  </div>
                </div>
                <div class="mb-3">
                  <label for="pword" class="form-label">Password</label>
                  <div class="input-group">
                    <span class="input-group-text">🔒</span>
                    <input
                      type="password"
                      class="form-control"
                      name="pword"
                      id="pword"
                      placeholder="Create a secure password"
                      required
                      minlength="8"
                    />
                  </div>
                  <small class="form-text text-muted">
                    Password must be at least 8 characters long
                  </small>
                </div>
                <div class="mb-3">
                  <label for="repword" class="form-label">Confirm Password</label>
                  <div class="input-group">
                    <span class="input-group-text">🔒</span>
                    <input
                      type="password"
                      class="form-control"
                      name="repword"
                      id="repword"
                      placeholder="Repeat your password"
                      required
                      minlength="8"
                    />
                  </div>
                </div>
                <div class="mb-3 form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="showPassword"
                    onclick="togglePasswords()"
                  />
                  <label class="form-check-label" for="showPassword">
                    Show Passwords
                  </label>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary btn-lg w-100" name="register">
                    Create DENR Account
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
      function togglePasswords() {
        let passwordFields = document.querySelectorAll("#pword, #repword");
        passwordFields.forEach((field) => {
          field.type = document.getElementById("showPassword").checked
            ? "text"
            : "password";
        });
      }

      // Password match and validation
      document.getElementById('registrationForm').addEventListener('submit', function(e) {
        let password = document.getElementById('pword');
        let confirmPassword = document.getElementById('repword');
        
        if (password.value !== confirmPassword.value) {
          e.preventDefault();
          alert('Passwords do not match!');
          confirmPassword.classList.add('is-invalid');
        }

        // Basic password strength check
        if (password.value.length < 8) {
          e.preventDefault();
          alert('Password must be at least 8 characters long!');
        }
      });
    </script>
  </body>
</html>