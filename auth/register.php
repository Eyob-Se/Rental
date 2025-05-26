<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Register</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="../assets/fonts/all.css" />
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
        <input type="tel" placeholder="Enter your phone number" name="phone" id="phone" required autocomplete="off" />

        <label for="address"><b>Address</b></label>
        <input type="text" placeholder="Enter your address" name="address" id="address" required autocomplete="off" />

        <label for="password"><i class="fas fa-lock"></i> <b>Password</b></label>
        <input type="password" placeholder="Enter your password" name="password" id="password" required autocomplete="new-password" />

        <label for="confirm_password"><i class="fas fa-key"></i> <b>Confirm Password</b></label>
        <input type="password" placeholder="Confirm your password" name="confirm_password" id="confirm_password" required autocomplete="new-password" />

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
    // Close modal if clicked outside modal content
    window.onclick = function(event) {
      const modal = document.getElementById('termsModal');
      if (event.target === modal) {
        modal.style.display = "none";
      }
    }

  const form = document.querySelector('.register-form');

  form.addEventListener('submit', function(event) {
    // Clear previous errors (optional)
    const errors = form.querySelectorAll('.error-message');
    errors.forEach(e => e.remove());

    // Password validation
    const password = form.password.value.trim();
    const confirmPassword = form.confirm_password.value.trim();
    if (password.length < 8) {
      showError(form.password, 'Password must be at least 8 characters.');
      event.preventDefault();
      return;
    }
    if (password !== confirmPassword) {
      showError(form.confirm_password, 'Passwords do not match.');
      event.preventDefault();
      return;
    }

    // Email format validation
    const email = form.email.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      showError(form.email, 'Please enter a valid email address.');
      event.preventDefault();
      return;
    }

    // Phone validation (basic, allows digits, spaces, +, -, parentheses)
    const phone = form.phone.value.trim();
    const phoneRegex = /^[\d\s+\-()]{7,}$/;
    if (!phoneRegex.test(phone)) {
      showError(form.phone, 'Please enter a valid phone number.');
      event.preventDefault();
      return;
    }

    // Check if ID photo is selected and valid image type
    const fileInput = form.id_photo;
    if (!fileInput.files || fileInput.files.length === 0) {
      showError(fileInput, 'Please upload your ID photo.');
      event.preventDefault();
      return;
    } else {
      const file = fileInput.files[0];
      const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
      if (!allowedTypes.includes(file.type)) {
        showError(fileInput, 'ID photo must be an image (jpg, png, gif, webp).');
        event.preventDefault();
        return;
      }
    }

    // Checkbox is required, but form attribute `required` handles it,
    // still optionally check here:
    const agree = form.agree_terms.checked;
    if (!agree) {
      alert('You must agree to the terms and conditions.');
      event.preventDefault();
      return;
    }

    // If all validations pass, form submits normally
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
</body>
</html>
