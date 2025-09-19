
<?php
require_once '../config/db.php';
session_start();

// Redirect to dashboard if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $db->prepare('INSERT INTO users (email, password) VALUES (?, ?)');
            $stmt->execute([$email, $hashed]);
            $message = "Registration successful. <a href='login.php'>Login here</a>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "Email already registered.";
            } else {
                $message = "Error: " . $e->getMessage();
            }
        }
    } else {
        $message = "Please fill all fields.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Resume Creator</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container-small">
        <div class="card">
            <div class="card-header text-center">
                <h2 class="card-title">Create Account</h2>
                <p>Join Resume Creator to build professional resumes</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert <?php echo strpos($message, 'successful') !== false ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="register.php">
                <div class="form-group">
                    <label class="form-label">Email Address <span class="required">*</span></label>
                    <input type="email" name="email" class="form-input" required placeholder="Enter your email">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password <span class="required">*</span></label>
                    <input type="password" name="password" class="form-input" required placeholder="Create a password">
                </div>
                
                <button type="submit" class="btn btn-success" style="width: 100%;">Create Account</button>
            </form>
            
            <div class="text-center mt-2">
                <p>Already have an account? <a href="login.php" class="nav-link" style="display: inline;">Sign in here</a></p>
            </div>
        </div>
    </div>
</body>
</html>
