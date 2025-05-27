<?php
session_start();
require_once '../config/db.php';

$step = 1;
$email = '';
$question = '';
$user_id = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['step']) && $_POST['step'] == 1) {
        // Step 1: User enters email
        $email = trim($_POST['email']);
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $user_id = $user['id'];

            $stmt = $pdo->prepare("SELECT question FROM security WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $security = $stmt->fetch();

            if ($security) {
                $step = 2;
                $question = $security['question'];
                $_SESSION['reset_user_id'] = $user_id;
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_question'] = $question;
            } else {
                $errors[] = "No security question found for this user.";
            }
        } else {
            $errors[] = "Email not found.";
        }
    } elseif (isset($_POST['step']) && $_POST['step'] == 2) {
        // Step 2: Validate answer and reset password
        $answer = $_POST['answer'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        $user_id = $_SESSION['reset_user_id'];

        if ($new_password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }

        // Fetch hashed answer
        $stmt = $pdo->prepare("SELECT answer FROM security WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $security = $stmt->fetch();

        if (!$security || !password_verify($answer, $security['answer'])) {
            $errors[] = "Incorrect answer.";
        }

        if (empty($errors)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);

            session_unset();
            session_destroy();

            echo "
            <!DOCTYPE html>
            <html>
            <head>
                <title>Password Reset</title>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <link rel=\"stylesheet\" href=\"../assets/style1.css\">
                <link rel=\"stylesheet\" href=\"../assets/fonts/all.css\">
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Password reset successful!',
                        text: 'You can now log in with your new password.',
                        showConfirmButton: false,
                        timer: 2500
                    }).then(() => {
                        window.location.href = 'login.php';
                    });
                </script>
            </body>
            </html>
            ";
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
        
        <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/fonts/all.css">
    <style>
        .password-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    
    .password-wrapper input {
        flex: 1;
        padding-right: 30px; /* space for the icon */
    }
    
    .toggle-password {
        position: absolute;
        right: 10px;
        cursor: pointer;
        color: #555;
    }
    </style>

</head>
<body>
    <div class="forgot-password-page">
        <?php if (!empty($errors)): ?>
            <ul style="color:red;">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    
        <?php if ($step === 1): ?>
           <div>
<form class="forgot-password-form" method="POST" autocomplete="off">
    <h2>Forgot Password?</h2>
    <div class="form-container">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>
        <input type="hidden" name="step" value="1">
        <button class="btn" type="submit">Continue</button>

    </div>
            </form>
    
        <?php elseif ($step === 2): ?>
            <form method="POST" class="forgot-password-form">
                <h2>Forgot Password?</h2>
                <div class="form-container">
                    <p><strong>Security Question:</strong> <?= htmlspecialchars($_SESSION['reset_question']) ?></p>
                    <label>Your Answer:</label><br>
                    <input type="text" name="answer" required autocomplete="off"><br><br>
        
                    <label>New Password:</label><br>
<div class="password-wrapper">
    <input type="password" name="new_password" id="new_password" autocomplete="new-password" required>
    <i class="fas fa-eye toggle-password" toggle="#new_password"></i>
</div><br>

<label>Confirm New Password:</label><br>
<div class="password-wrapper">
    <input type="password" name="confirm_password" id="confirm_password" autocomplete="new-password" required>
    <i class="fas fa-eye toggle-password" toggle="#confirm_password"></i>
</div><br>

        
                    <input type="hidden" name="step" value="2">
                    <button class="btn" type="submit">Reset Password</button>

                </div>
            </form>
           </div> 
        <?php endif; ?>
    </div>

    <script>
document.querySelectorAll('.toggle-password').forEach(function(icon) {
    icon.addEventListener('click', function() {
        const input = document.querySelector(this.getAttribute('toggle'));
        if (input.type === 'password') {
            input.type = 'text';
            this.classList.remove('fa-eye');
            this.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            this.classList.remove('fa-eye-slash');
            this.classList.add('fa-eye');
        }
    });
});

// Password validation (client-side)
document.querySelector('form.forgot-password-form')?.addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    if (newPassword && confirmPassword) {
        const pwd = newPassword.value;
        const confirm = confirmPassword.value;
        // Example: at least 8 chars, 1 uppercase, 1 lowercase, 1 number
        const valid = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(pwd);
        if (!valid) {
            alert('Password must be at least 8 characters long and include uppercase, lowercase, and a number.');
            e.preventDefault();
        } else if (pwd !== confirm) {
            alert('Passwords do not match.');
            e.preventDefault();
        }
    }
});

document.querySelectorAll('input[name="new_password"], input[name="confirm_password"]').forEach(function(input) {
    input.addEventListener('input', function() {
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        let message = '';
        if (newPassword && confirmPassword) {
            const pwd = newPassword.value;
            const confirm = confirmPassword.value;
            const valid = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(pwd);
            if (!valid && pwd.length > 0) {
                message = 'Password must be at least 8 characters, include uppercase, lowercase, and a number.';
            } else if (pwd !== confirm && confirm.length > 0) {
                message = 'Passwords do not match.';
            }
        }
        let msgElem = document.getElementById('pwd-msg');
        if (!msgElem) {
            msgElem = document.createElement('div');
            msgElem.id = 'pwd-msg';
            msgElem.style.color = 'red';
            confirmPassword.parentNode.parentNode.appendChild(msgElem);
        }
        msgElem.textContent = message;
    });
});
</script>
</body>
</html>

