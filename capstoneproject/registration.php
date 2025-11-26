<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register</title>
  <link rel="stylesheet" href="css/form.css" />
  
  <style>
      /* Specific styles for the navigation buttons */
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
<body>
  <div class="form-container">
    
    <a href="login.php" class="close-x" title="Cancel Registration">&times;</a>

    <h2>Register</h2>
    <form id="registrationForm" action="php/register_user.php" method="POST" novalidate>
      
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required autocomplete="off" />
        <small id="usernameError" class="error-message"></small>
      </div>
      
      <div class="form-group" style="position:relative;">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required />
        <small id="passwordError" class="error-message"></small>
      </div>
      
      <div class="form-group" style="position:relative;">
        <label for="confirmPassword">Confirm Password</label>
        <input type="password" id="confirmPassword" name="confirmPassword" required />
        <small id="confirmPasswordError" class="error-message"></small>
      </div>
      
      <div class="form-group">
        <label for="firstName">First Name</label>
        <input type="text" id="firstName" name="firstName" required autocomplete="off" />
        <small id="firstNameError" class="error-message"></small>
      </div>
      
      <div class="form-group">
        <label for="midName">Middle Name</label>
        <input type="text" id="midName" name="midName" required autocomplete="off" />
        <small id="midNameError" class="error-message"></small>
      </div>
      
      <div class="form-group">
        <label for="lastName">Last Name</label>
        <input type="text" id="lastName" name="lastName" required autocomplete="off" />
        <small id="lastNameError" class="error-message"></small>
      </div>
      
      <div class="form-group">
        <label for="address">Address</label>
        <input type="text" id="address" name="address" required autocomplete="off" />
        <small id="addressError" class="error-message"></small>
      </div>
      
      <div class="form-group">
        <label for="contactNumber">Contact Number</label>
        <input type="text" id="contactNumber" name="contactNumber" maxlength="11" required autocomplete="off"/>
        <small id="contactNumberError" class="error-message"></small>
      </div>
      
      <div class="form-group">
        <label for="permitNumber">Business Permit Number</label>
        <input type="text" id="permitNumber" name="permitNumber" required autocomplete="off" />
        <small id="permitNumberError" class="error-message"></small>
      </div>
      
      <div class="form-group">
        <label for="bhName">Boarding House Name</label>
        <input type="text" id="bhName" name="bhName" required autocomplete="off" />
        <small id="bhNameError" class="error-message"></small>
      </div>
      
      <div class="form-group">
        <label for="bhAddress">Boarding House Address</label>
        <input type="text" id="bhAddress" name="bhAddress" required autocomplete="off" />
        <small id="bhAddressError" class="error-message"></small>
      </div>
      
      <div class="policy-container">
        <h3>Policies</h3>
        <?php
          include 'php/connection.php';
          $sql = "SELECT pol_id, pol_desc FROM pol_table ORDER BY pol_id ASC";
          $result = $conn->query($sql);
          if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              $id = (int)$row['pol_id'];
              $desc = htmlspecialchars($row['pol_desc']);
              echo '<div class="policy-item">';
              echo "<label class='policy-label'>{$id}. {$desc}</label>";
              echo "<label><input type='radio' name='policy_{$id}' value='yes' required /> Followed</label> ";
              echo "<label><input type='radio' name='policy_{$id}' value='no' required /> Not Followed</label>";
              echo "</div>";
            }
          } else {
            echo "<p>No policies found.</p>";
          }
          $conn->close();
        ?>
      </div>
      
      <button type="submit">Register</button>
    </form>

    <div class="back-link-container">
        <a href="login.php" class="back-link">
            &larr; Go back
        </a>
    </div>

  </div>
  
  <script src="js/registration.js"></script>
</body>
</html>