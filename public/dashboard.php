<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user info for personalization
require_once '../config/db.php';
$stmt = $db->prepare('SELECT email FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get resume count
$stmt = $db->prepare('SELECT COUNT(*) as count FROM generated_resumes WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$resume_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Check if user has resume data
$stmt = $db->prepare('SELECT COUNT(*) as count FROM resume_data WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$has_data = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Resume Creator</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            background: #fafbfc;
            min-height: 100vh;
            overflow-x: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            letter-spacing: -0.01em;
            margin: 0;
            padding: 0;
        }
        
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0;
            width: 100%;
            box-sizing: border-box;
        }
        
        .top-nav {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
            width: 100%;
            box-sizing: border-box;
        }
        
        .nav-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
        }
        
        .user-profile {
            position: relative;
            cursor: pointer;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--gradient-purple);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
        }
        
        .profile-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 0.5rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(0, 0, 0, 0.08);
            padding: 1rem 0;
            min-width: 220px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px) scale(0.95);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            backdrop-filter: blur(20px);
        }
        
        .profile-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }
        
        .dropdown-header {
            padding: 0 1.5rem 1rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            margin-bottom: 0.5rem;
        }
        
        .dropdown-email {
            font-size: 0.9rem;
            color: var(--text-medium);
            margin: 0;
        }
        
        .dropdown-name {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0 0 0.25rem 0;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: var(--text-dark);
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }
        
        .dropdown-item:hover {
            background: var(--bg-purple-light);
            color: var(--accent-color);
        }
        
        .dropdown-item.logout {
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            margin-top: 0.5rem;
            color: #e74c3c;
        }
        
        .dropdown-item.logout:hover {
            background: rgba(231, 76, 60, 0.1);
            color: #c0392b;
        }
        
        .dropdown-icon {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            padding: 3rem 2rem;
        }
        
        .hero-section {
            text-align: center;
            margin-bottom: 4rem;
            padding: 2rem 0;
        }
        
        .hero-title {
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            font-weight: 300;
            color: var(--primary-color);
            margin: 0 0 1rem 0;
            line-height: 1.1;
            letter-spacing: -0.02em;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            color: var(--text-medium);
            margin: 0 0 2rem 0;
            font-weight: 400;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }
        
        .stats-row {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-bottom: 3rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--accent-color);
            margin: 0;
            line-height: 1;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--text-medium);
            margin: 0.5rem 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }
        
        .action-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            border: 1px solid rgba(0, 0, 0, 0.04);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-purple);
            transform: scaleX(0);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .action-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border-color: rgba(124, 58, 237, 0.1);
        }
        
        .action-card:hover::before {
            transform: scaleX(1);
        }
        
        .card-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .card-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: var(--bg-purple-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        
        .card-content {
            flex: 1;
        }
        
        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0 0 0.5rem 0;
            line-height: 1.3;
        }
        
        .card-description {
            color: var(--text-medium);
            line-height: 1.6;
            font-size: 1rem;
            margin: 0;
        }
        
        .card-footer {
            margin-top: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .status-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }
        
        .status-ready .status-dot {
            background: var(--success-color);
        }
        
        .status-pending .status-dot {
            background: var(--warning-color);
        }
        
        .status-ready {
            color: var(--success-color);
        }
        
        .status-pending {
            color: var(--warning-color);
        }
        
        .btn-modern {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
            letter-spacing: -0.01em;
        }
        
        .btn-primary {
            background: var(--gradient-purple);
            color: white;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.15);
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(124, 58, 237, 0.25);
        }
        
        .btn-secondary {
            background: white;
            color: var(--accent-color);
            border: 1.5px solid rgba(124, 58, 237, 0.2);
        }
        
        .btn-secondary:hover {
            background: var(--bg-purple-light);
            border-color: var(--accent-color);
            transform: translateY(-1px);
        }
        
        .quick-actions {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            border: 1px solid rgba(0, 0, 0, 0.04);
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0 0 2rem 0;
        }
        
        .quick-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .empty-state {
            text-align: center;
            color: var(--text-medium);
            font-style: italic;
            padding: 2rem;
            background: var(--bg-light);
            border-radius: 16px;
            border: 2px dashed rgba(0, 0, 0, 0.1);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .top-nav {
                padding: 1rem;
            }
            
            .main-content {
                padding: 2rem 1rem;
            }
            
            .hero-section {
                margin-bottom: 3rem;
            }
            
            .stats-row {
                gap: 2rem;
                margin-bottom: 2rem;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .action-card {
                padding: 2rem;
            }
            
            .quick-buttons {
                flex-direction: column;
                align-items: stretch;
            }
        }
        
        @media (max-width: 480px) {
            .stats-row {
                flex-direction: column;
                gap: 1rem;
            }
            
            .card-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            
            .card-footer {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <!-- Modern Navigation -->
    <nav class="top-nav">
        <div class="nav-content">
            <a href="#" class="logo">ResumeBuilder</a>
            <div class="user-menu">
                <div class="user-profile" onclick="toggleProfileDropdown()">
                    <div class="user-avatar"><?php echo strtoupper(substr($user['email'], 0, 1)); ?></div>
                    <div class="profile-dropdown" id="profileDropdown">
                        <div class="dropdown-header">
                            <div class="dropdown-name">Welcome!</div>
                            <div class="dropdown-email"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        <a href="dashboard.php" class="dropdown-item">
                            <span class="dropdown-icon">🏠</span>
                            Dashboard
                        </a>
                        <a href="resume_form.php" class="dropdown-item">
                            <span class="dropdown-icon">📝</span>
                            Edit Resume Data
                        </a>
                        <a href="my_resumes.php" class="dropdown-item">
                            <span class="dropdown-icon">🎨</span>
                            Browse Templates
                        </a>
                        <a href="resume_history.php" class="dropdown-item">
                            <span class="dropdown-icon">📄</span>
                            Resume Library
                        </a>
                        <a href="logout.php" class="dropdown-item logout">
                            <span class="dropdown-icon">🚪</span>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="dashboard-container">
        <!-- Main Content -->
        <div class="main-content">
            <!-- Hero Section -->
            <div class="hero-section">
                <h1 class="hero-title">Build Your Future</h1>
                <p class="hero-subtitle">Create professional resumes that get you noticed. Modern tools for modern careers.</p>
                
                <!-- Stats -->
                <div class="stats-row">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $resume_count; ?></div>
                        <div class="stat-label">Resumes Created</div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-number">6</div>
                        <div class="stat-label">Templates Available</div>
                    </div>
                </div>
            </div>
            
            <!-- Action Cards -->
            <div class="actions-grid">
                <!-- Create Resume Card -->
                <div class="action-card">
                    <div class="card-header">
                        <div class="card-icon">📝</div>
                        <div class="card-content">
                            <h2 class="card-title">Create Resume</h2>
                            <p class="card-description">Build your professional profile with our structured form. Add experience, education, and skills to create a comprehensive resume.</p>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="status-indicator <?php echo $has_data ? 'status-ready' : 'status-pending'; ?>">
                            <div class="status-dot"></div>
                            <?php echo $has_data ? 'Data ready' : 'No data yet'; ?>
                        </div>
                        <a href="resume_form.php" class="btn-modern btn-primary">Start Creating</a>
                    </div>
                </div>
                
                <!-- Choose Template Card -->
                <div class="action-card">
                    <div class="card-header">
                        <div class="card-icon">🎨</div>
                        <div class="card-content">
                            <h2 class="card-title">Choose Template</h2>
                            <p class="card-description">Select from our curated collection of professional resume templates. Each design is optimized for modern hiring practices.</p>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="status-indicator <?php echo $has_data ? 'status-ready' : 'status-pending'; ?>">
                            <div class="status-dot"></div>
                            <?php echo $has_data ? 'Ready to preview' : 'Add data first'; ?>
                        </div>
                        <a href="<?php echo $has_data ? 'my_resumes.php' : 'resume_form.php'; ?>" 
                           class="btn-modern <?php echo $has_data ? 'btn-primary' : 'btn-secondary'; ?>">
                           <?php echo $has_data ? 'Browse Templates' : 'Add Data First'; ?>
                        </a>
                    </div>
                </div>
                
                <!-- Resume Library Card -->
                <div class="action-card">
                    <div class="card-header">
                        <div class="card-icon">📄</div>
                        <div class="card-content">
                            <h2 class="card-title">Resume Library</h2>
                            <p class="card-description">Access and manage all your previously generated resumes. Download or share them whenever you need.</p>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="status-indicator status-ready">
                            <div class="status-dot"></div>
                            Always available
                        </div>
                        <a href="resume_history.php" class="btn-modern btn-secondary">View Library</a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3 class="section-title">Quick Actions</h3>
                <div class="quick-buttons">
                    <?php if ($has_data): ?>
                        <a href="my_resumes.php?template=classic" class="btn-modern btn-secondary">Classic Template</a>
                        <a href="my_resumes.php?template=modern" class="btn-modern btn-secondary">Modern Template</a>
                        <a href="my_resumes.php?template=minimal" class="btn-modern btn-secondary">Minimal Template</a>
                        <a href="my_resumes.php?template=creative" class="btn-modern btn-secondary">Creative Template</a>
                        <a href="my_resumes.php?template=corporate" class="btn-modern btn-secondary">Corporate Template</a>
                        <a href="my_resumes.php?template=tech" class="btn-modern btn-secondary">Tech Template</a>
                    <?php else: ?>
                        <div class="empty-state">Add your resume data to unlock quick template generation</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userProfile = document.querySelector('.user-profile');
            const dropdown = document.getElementById('profileDropdown');
            
            if (!userProfile.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });
        
        // Prevent dropdown from closing when clicking inside it
        document.getElementById('profileDropdown').addEventListener('click', function(event) {
            event.stopPropagation();
        });
    </script>
</body>
</html>
