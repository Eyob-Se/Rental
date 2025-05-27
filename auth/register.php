<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Register</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="../assets/fonts/all.css" />
  <style>
    .password-container {
      position: relative;
      display: flex;
      align-items: center;
    }

    .password-container input {
      width: 100%;
      padding-right: 35px;
    }

    .password-container .toggle-password {
      position: absolute;
      right: 10px;
      cursor: pointer;
      color: #555;
    }
  </style>
</head>
<body>
  <div class="register-page">
    <form action="register_process.php" method="post" enctype="multipart/form-data" class="register-form">
      <h2>Register</h2>

      <div class="form-container">

        <label for="role"><b>Describe yourself</b></label>
        <select name="role" id="role" required>
          <option value="tenant">Tenant</option>
          <option value="owner">Owner</option>
        </select>

        <label for="name"><b>Full Name</b></label>
        <input type="text" placeholder="Enter your full name" name="name" id="name" required autocomplete="off" />

        <label for="email"><i class="fas fa-envelope"></i> <b>Email</b></label>
        <input type="email" placeholder="Enter your email" name="email" id="email" required autocomplete="off" />

        <label for="phone"><i class="fas fa-phone"></i> <b>Phone</b></label>
        <input type="tel" placeholder="09/07-XX-XX-XX-XX" name="phone" id="phone" required autocomplete="off" />

        <label for="address"><b>Address</b></label>
        <input type="text" placeholder="Enter your address" name="address" id="address" required autocomplete="off" />

        <label for="password"><i class="fas fa-lock"></i> <b>Password</b></label>
        <div class="password-container">
          <input type="password" placeholder="Enter your password" name="password" id="password" required autocomplete="new-password" />
          <i class="fas fa-eye toggle-password" onclick="togglePassword('password', this)"></i>
        </div>

        <label for="confirm_password"><i class="fas fa-key"></i> <b>Confirm Password</b></label>
        <div class="password-container">
          <input type="password" placeholder="Confirm your password" name="confirm_password" id="confirm_password" required autocomplete="new-password" />
          <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password', this)"></i>
        </div>

        <!-- Owner payment details container -->
        <div id="ownerPaymentDetails" style="display: none; justify-content: space-between; align-items: center;">
          <label for="preferred_payment_method"><b>Preferred Payment Method</b></label>
          <input type="text" name="preferred_payment_method" id="preferred_payment_method" placeholder="Enter preferred payment method (e.g., Bank Name)" autocomplete="off" />

          
          <input type="text" placeholder="Enter your bank account" name="bank_account" id="bank_account" autocomplete="off" />
        </div>

        <label for="id_photo"><b>Upload ID Photo</b></label>
        <input type="file" name="id_photo" id="id_photo" accept="image/*" required />

        <label>
          <input type="checkbox" name="agree_terms" id="agree_terms" required />
          I agree to the <a href="#" onclick="openTerms(event)">terms and conditions</a>.
        </label>
      </div>

      <button class="btn" type="submit">Register</button>
      <button class="btn" type="button" class="cancelbtn" onclick="window.location.href='../index.php'">Cancel</button>

      <div class="form-links">
        <a href="login.php">Already have an account? Login</a>
      </div>
    </form>
  </div>

  <!-- Terms and Conditions Modal -->
  <div id="termsModal" class="modal2" style="display:none;">
    <div class="modal2-content">
      <span class="close" onclick="closeTerms()">&times;</span>
      <h2>Terms and Conditions</h2>
      <p>
        By registering on our platform, you agree to provide valid information, comply with the rental system's policies, and respect the rights of other users...
      </p>
    </div>
  </div>

<script>
  function openTerms(event) {
    event.preventDefault();
    document.getElementById('termsModal').style.display = 'block';
  }

  function closeTerms() {
    document.getElementById('termsModal').style.display = 'none';
  }

  window.onclick = function(event) {
    const modal = document.getElementById('termsModal');
    if (event.target === modal) {
      modal.style.display = "none";
    }
  }

  function togglePassword(fieldId, icon) {
    const input = document.getElementById(fieldId);
    if (input.type === "password") {
      input.type = "text";
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    } else {
      input.type = "password";
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  }

  document.getElementById('role').addEventListener('change', function () {
    const ownerPaymentDetails = document.getElementById('ownerPaymentDetails');
    if (this.value === 'owner') {
      ownerPaymentDetails.style.display = 'block';
      document.getElementById('preferred_payment_method').required = true;
      document.getElementById('bank_account').required = true;
    } else {
      ownerPaymentDetails.style.display = 'none';
      document.getElementById('preferred_payment_method').required = false;
      document.getElementById('bank_account').required = false;
    }
  });

  window.addEventListener('DOMContentLoaded', () => {
    document.getElementById('role').dispatchEvent(new Event('change'));
  });

  const form = document.querySelector('.register-form');

  form.addEventListener('submit', function(event) {
    const errors = form.querySelectorAll('.error-message');
    errors.forEach(e => e.remove());

    const name = form.name.value.trim();
    const phone = form.phone.value.trim();
    const email = form.email.value.trim();
    const password = form.password.value.trim();
    const confirmPassword = form.confirm_password.value.trim();
    const fileInput = form.id_photo;

    // Name: Only letters and spaces
    const nameRegex = /^[A-Za-z\s]+$/;
    if (!nameRegex.test(name)) {
      showError(form.name, 'Name should contain letters and spaces only.');
      event.preventDefault();
    }

    // Phone: Only digits
    const phoneRegex = /^\d{10}$/;
    if (!phoneRegex.test(phone)) {
      showError(form.phone, 'Phone number must contain digits only.');
      event.preventDefault();
    }

    // Email: Valid format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      showError(form.email, 'Please enter a valid email address.');
      event.preventDefault();
    }

    // Password strength: 8+ chars, 1 upper, 1 lower, 1 number, 1 symbol
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
    if (!passwordRegex.test(password)) {
      showError(form.password, 'Password must be 8+ chars, include upper, lower, number & symbol.');
      event.preventDefault();
    }

    // Confirm password match
    if (password !== confirmPassword) {
      showError(form.confirm_password, 'Passwords do not match.');
      event.preventDefault();
    }

    // File: Must be image/pdf
    if (!fileInput.files || fileInput.files.length === 0) {
      showError(fileInput, 'Please upload your ID photo.');
      event.preventDefault();
    } else {
      const file = fileInput.files[0];
      const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'image/jpg'];
      if (!allowedTypes.includes(file.type)) {
        showError(fileInput, 'File must be jpg, jpeg, png, gif, webp or pdf.');
        event.preventDefault();
      }
    }

    // Terms checkbox
    if (!form.agree_terms.checked) {
      alert('You must agree to the terms and conditions.');
      event.preventDefault();
    }

    // Owner-specific
    if (form.role.value === 'owner') {
      const paymentMethod = form.preferred_payment_method.value.trim();
      const bankAccount = form.bank_account.value.trim();

      const textOnlyRegex = /^[A-Za-z\s]+$/;
      const numbersOnlyRegex = /^\d+$/;

      if (!textOnlyRegex.test(paymentMethod)) {
        showError(form.preferred_payment_method, 'Payment method should contain letters only.');
        event.preventDefault();
      }

      if (!numbersOnlyRegex.test(bankAccount)) {
        showError(form.bank_account, 'Bank account should contain numbers only.');
        event.preventDefault();
      }
    }
  });

  function showError(element, message) {
    const error = document.createElement('div');
    error.className = 'error-message';
    error.style.color = 'red';
    error.style.fontSize = '0.9em';
    error.style.marginTop = '4px';
    error.textContent = message;
    element.parentNode.insertBefore(error, element.nextSibling);
  }
</script>

<script>
  const validators = {
    name: {
      regex: /^[A-Za-z\s]+$/,
      message: 'Name should contain letters and spaces only.'
    },
    phone: {
      regex: /^\d{10}$/,
      message: 'Phone number must contain digits only and min and max 10.'
    },
    email: {
      regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
      message: 'Please enter a valid email address.'
    },
    password: {
      regex: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/,
      message: 'Password must be 8+ chars, include upper, lower, number & symbol.'
    },
    confirm_password: {
      matchField: 'password',
      message: 'Passwords do not match.'
    },
    preferred_payment_method: {
      regex: /^[A-Za-z\s]+$/,
      message: 'Payment method should contain letters only.'
    },
    bank_account: {
      regex: /^\d+$/,
      message: 'Bank account should contain numbers only.'
    }
  };

  Object.keys(validators).forEach(fieldName => {
    const field = document.forms[0][fieldName];
    if (!field) return;

    field.addEventListener('input', () => {
      const value = field.value.trim();
      removeError(field);

      const { regex, matchField, message } = validators[fieldName];

      if (regex && !regex.test(value)) {
        showError(field, message);
      }

      if (matchField) {
        const matchValue = document.forms[0][matchField].value.trim();
        if (value !== matchValue) {
          showError(field, message);
        }
      }
    });
  });

  // Real-time file type validation
  const fileInput = document.forms[0]['id_photo'];
  fileInput.addEventListener('change', () => {
    removeError(fileInput);
    if (fileInput.files.length === 0) return;

    const file = fileInput.files[0];
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'image/jpg'];
    if (!allowedTypes.includes(file.type)) {
      showError(fileInput, 'File must be jpg, jpeg, png, gif, webp or pdf.');
    }
  });

  function showError(element, message) {
    removeError(element);
    const error = document.createElement('div');
    error.className = 'error-message';
    error.style.color = 'red';
    error.style.fontSize = '0.9em';
    error.style.marginTop = '4px';
    error.textContent = message;
    element.parentNode.insertBefore(error, element.nextSibling);
  }

  function removeError(element) {
    const next = element.nextSibling;
    if (next && next.classList && next.classList.contains('error-message')) {
      next.remove();
    }
  }
</script>

</body>
</html>
