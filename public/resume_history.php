<?php
require_once '../config/db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user's generated resumes history
$stmt = $db->prepare('SELECT * FROM generated_resumes WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$generated_resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Resume History - Resume Creator</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            background: #fafbfc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            letter-spacing: -0.01em;
        }
        
        .top-nav {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logo {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .nav-separator {
            width: 1px;
            height: 20px;
            background: rgba(0, 0, 0, 0.1);
        }
        
        .page-title {
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-medium);
        }
        
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .btn-nav {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .btn-secondary {
            background: white;
            color: var(--accent-color);
            border: 1.5px solid rgba(124, 58, 237, 0.2);
        }
        
        .btn-secondary:hover {
            background: var(--bg-purple-light);
            border-color: var(--accent-color);
        }
        
        .btn-primary {
            background: var(--gradient-purple);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(124, 58, 237, 0.25);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .page-title-main {
            font-size: clamp(2rem, 4vw, 2.5rem);
            font-weight: 600;
            color: var(--primary-color);
            margin: 0 0 1rem 0;
            line-height: 1.2;
        }
        
        .page-subtitle {
            font-size: 1.1rem;
            color: var(--text-medium);
            margin: 0;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.5;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            border: 1px solid rgba(0, 0, 0, 0.04);
        }
        
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.5;
        }
        
        .empty-state-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .empty-state-description {
            font-size: 1rem;
            color: var(--text-medium);
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .history-table {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            border: 1px solid rgba(0, 0, 0, 0.04);
        }
        
        .table-header {
            background: var(--bg-purple-light);
            padding: 1.5rem 2rem;
            border-bottom: 1px solid rgba(124, 58, 237, 0.1);
        }
        
        .table-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }
        
        .table-content {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }
        
        td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            font-size: 0.95rem;
            color: var(--text-dark);
        }
        
        tr:hover {
            background: rgba(124, 58, 237, 0.02);
        }
        
        .resume-name {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .template-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .template-classic {
            background: rgba(52, 152, 219, 0.1);
            color: #2980b9;
        }
        
        .template-modern {
            background: rgba(155, 89, 182, 0.1);
            color: #8e44ad;
        }
        
        .template-minimal {
            background: rgba(46, 204, 113, 0.1);
            color: #27ae60;
        }
        
        .template-creative {
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.1), rgba(78, 84, 200, 0.1));
            color: #6b46c1;
        }
        
        .template-corporate {
            background: rgba(44, 62, 80, 0.1);
            color: #2c3e50;
        }
        
        .template-tech {
            background: rgba(45, 52, 54, 0.1);
            color: #2d3436;
        }
        
        .date-text {
            color: var(--text-medium);
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        
        .btn-view {
            background: var(--gradient-purple);
            color: white;
        }
        
        .btn-view:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.25);
        }
        
        .create-new-section {
            margin-top: 3rem;
            text-align: center;
            padding: 2rem;
            background: var(--bg-purple-light);
            border-radius: 20px;
            border: 1px solid rgba(124, 58, 237, 0.1);
        }
        
        .create-new-section h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0 0 1rem 0;
        }
        
        .create-new-section p {
            color: var(--text-medium);
            margin: 0 0 1.5rem 0;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .nav-left {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .nav-separator {
                display: none;
            }
            
            .container {
                padding: 1rem;
            }
            
            .table-content {
                margin: -1rem;
                padding: 1rem;
            }
            
            table {
                font-size: 0.85rem;
            }
            
            th, td {
                padding: 0.75rem 1rem;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn-action {
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .page-title-main {
                font-size: 1.75rem;
            }
            
            .history-table {
                margin: 0 -0.5rem;
            }
            
            .table-header {
                padding: 1rem 1.5rem;
            }
            
            th, td {
                padding: 0.75rem 0.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- Modern Navigation -->
    <nav class="top-nav">
        <div class="nav-content">
            <div class="nav-left">
                <a href="dashboard.php" class="logo">ResumeBuilder</a>
                <div class="nav-separator"></div>
                <div class="page-title">Resume History</div>
            </div>
            <div class="nav-actions">
                <a href="dashboard.php" class="btn-nav btn-secondary">← Dashboard</a>
                <a href="my_resumes.php" class="btn-nav btn-primary">Create New</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title-main">Your Resume History</h1>
            <p class="page-subtitle">View and download your previously generated resumes</p>
        </div>
        
        <?php if (empty($generated_resumes)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-state-icon">📄</div>
                <h3 class="empty-state-title">No Resumes Yet</h3>
                <p class="empty-state-description">
                    You haven't generated any resumes yet. Start by creating your first professional resume with our easy-to-use templates.
                </p>
                <a href="resume_form.php" class="btn-nav btn-primary">Create Your First Resume</a>
            </div>
        <?php else: ?>
            <!-- Resume History Table -->
            <div class="history-table">
                <div class="table-header">
                    <h3 class="table-title">Generated Resumes (<?php echo count($generated_resumes); ?>)</h3>
                </div>
                
                <div class="table-content">
                    <table>
                        <thead>
                            <tr>
                                <th>Resume Name</th>
                                <th>Template</th>
                                <th>Generated Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($generated_resumes as $resume): ?>
                                <tr>
                                    <td>
                                        <div class="resume-name"><?php echo htmlspecialchars($resume['full_name']); ?></div>
                                    </td>
                                    <td>
                                        <span class="template-badge template-<?php echo strtolower($resume['template']); ?>">
                                            <?php echo htmlspecialchars($resume['template']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="date-text">
                                            <?php echo date('M j, Y', strtotime($resume['created_at'])); ?>
                                            <br>
                                            <small style="opacity: 0.7;"><?php echo date('g:i A', strtotime($resume['created_at'])); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="generate_pdf.php?template=<?php echo urlencode($resume['template']); ?>" class="btn-action btn-view">
                                                📄 View/Download
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Create New Section -->
            <div class="create-new-section">
                <h3>Ready to Create Another Resume?</h3>
                <p>Choose from our professional templates and create a new resume tailored for different opportunities.</p>
                <a href="my_resumes.php" class="btn-nav btn-primary">Create New Resume</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>