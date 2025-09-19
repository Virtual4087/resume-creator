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
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            letter-spacing: -0.01em;
        }
        
        .auth-container {
            width: 100%;
            max-width: 520px;
            padding: 2rem;
            box-sizing: border-box;
        }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 3.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .logo {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .logo-tagline {
            color: var(--text-medium);
            font-size: 1rem;
            font-weight: 400;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .auth-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0 0 0.5rem 0;
            line-height: 1.3;
        }
        
        .auth-subtitle {
            color: var(--text-medium);
            font-size: 1rem;
            margin: 0;
            font-weight: 400;
        }
        
        .form-modern {
            margin-bottom: 2rem;
        }
        
        .form-group-modern {
            margin-bottom: 1.5rem;
        }
        
        .form-label-modern {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--primary-color);
            font-size: 0.95rem;
        }
        
        .form-input-modern {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
            box-sizing: border-box;
        }
        
        .form-input-modern:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
            transform: translateY(-1px);
        }
        
        .form-input-modern::placeholder {
            color: var(--text-light);
            font-weight: 400;
        }
        
        .btn-auth {
            width: 100%;
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--gradient-success);
            color: white;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.2);
            margin-bottom: 1.5rem;
        }
        
        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.3);
        }
        
        .btn-auth:active {
            transform: translateY(-1px);
        }
        
        .alert-modern {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        .alert-success {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(39, 174, 96, 0.2);
        }
        
        .alert-error {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.2);
        }
        
        .auth-footer {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .auth-link {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .auth-link:hover {
            color: var(--primary-color);
        }
        
        .required {
            color: #e74c3c;
        }
        
        .password-hint {
            font-size: 0.85rem;
            color: var(--text-medium);
            margin-top: 0.5rem;
            line-height: 1.4;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .auth-container {
                padding: 1rem;
                max-width: 440px;
            }
            
            .auth-card {
                padding: 2rem;
            }
            
            .logo {
                font-size: 1.75rem;
            }
            
            .auth-title {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .auth-container {
                max-width: 100%;
                padding: 0.5rem;
            }
            
            .auth-card {
                padding: 1.5rem;
                border-radius: 16px;
            }
            
            .logo {
                font-size: 1.5rem;
            }
            
            .auth-title {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo">ResumeBuilder</div>
                <div class="logo-tagline">Build Your Future</div>
            </div>
            
            <!-- Header -->
            <div class="auth-header">
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Join thousands building professional resumes</p>
            </div>
            
            <!-- Alert Messages -->
            <?php if ($message): ?>
                <div class="alert-modern <?php echo strpos($message, 'successful') !== false ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Registration Form -->
            <form method="post" action="register.php" class="form-modern">
                <div class="form-group-modern">
                    <label class="form-label-modern">Email Address <span class="required">*</span></label>
                    <input type="email" 
                           name="email" 
                           class="form-input-modern" 
                           required 
                           placeholder="Enter your email address"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group-modern">
                    <label class="form-label-modern">Password <span class="required">*</span></label>
                    <input type="password" 
                           name="password" 
                           class="form-input-modern" 
                           required 
                           placeholder="Create a strong password"
                           minlength="6">
                    <div class="password-hint">
                        Password should be at least 6 characters long
                    </div>
                </div>
                
                <button type="submit" class="btn-auth">Create Account</button>
            </form>
            
            <!-- Footer -->
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php" class="auth-link">Sign in here</a></p>
            </div>
        </div>
    </div>
</body>
</html>