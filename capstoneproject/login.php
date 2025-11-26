<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link rel="stylesheet" href="css/form.css" />
    
    <style>
        /* Specific styles for the X and Back buttons */
        .form-container {
            position: relative; /* Essential for absolute positioning the X */
        }

        .close-x {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            color: #aaa;
            text-decoration: none;
            font-weight: bold;
            line-height: 1;
            transition: color 0.2s;
            cursor: pointer;
        }
        .close-x:hover {
            color: #e74a3b; /* Red on hover */
        }

        .back-link-container {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .back-link {
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: #4e73df;
        }
    </style>
</head>

<script>
window.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const error = params.get('error');
  const registered = params.get('registered');

  const usernameInput = document.querySelector('input[name="username"]');
  const usernameErrorDiv = document.getElementById('usernameError');
  const passwordInput = document.querySelector('input[name="password"]');
  const passwordErrorDiv = document.getElementById('passwordError');

  // Clean previous states
  usernameInput.classList.remove('invalid');
  usernameErrorDiv.textContent = "";
  passwordInput.classList.remove('invalid');
  passwordErrorDiv.textContent = "";

  // Success Message (Registration)
  if (registered) {
      alert("Registration successful! Please log in.");
  }

  // Error Handling
  if (error === 'username') {
    usernameInput.classList.add('invalid');
    usernameErrorDiv.textContent = "Username does not exist.";
  } else if (error === 'password') {
    passwordInput.classList.add('invalid');
    passwordErrorDiv.textContent = "Incorrect password.";
  } else if (error === 'fields') {
    usernameErrorDiv.textContent = "Please fill in all fields.";
  } else if (error === 'server') {
    alert("Server error. Please try again later.");
  } else if (error === 'access') {
    alert("Access denied.");
  }
});
</script>

<body>
    <div class="form-container">
        
        <a href="viewer.php" class="close-x" title="Close">&times;</a>

        <h2>Login</h2>
        
        <form action="php/phplogin.php" method="POST" id="loginForm" novalidate>
            <div class="form-group">
                <label>Username</label>
                <input type="text" id="username" name="username" required autocomplete="username" />
                <div class="error-message" id="usernameError"></div>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password" />
                <span class="password-toggle" onclick="togglePassword()">👁</span>
                <div class="error-message" id="passwordError"></div>
            </div>
            
            <button type="submit">Login</button>
        </form>
        
        <p style="text-align:center; margin-top:1rem;">
            Don't have an account? <a href="registration.php" style="color:#4e73df; font-weight:bold;">Create Account</a>
        </p>

        <div class="back-link-container">
            <a href="viewer.php" class="back-link">
                &larr; Go back
            </a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            if (pwd.type === 'password') {
                pwd.type = 'text';
            } else {
                pwd.type = 'password';
            }
        }
    </script>
</body>
</html>