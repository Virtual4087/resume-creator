<?php
require_once '../config/db.php';
require_once '../formats/templates.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user's resume data
$stmt = $db->prepare('SELECT * FROM resume_data WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
$stmt->execute([$_SESSION['user_id']]);
$resume_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resume_data) {
    header('Location: resume_form.php');
    exit;
}

$selected_template = $_GET['template'] ?? 'classic';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Choose Resume Template - Resume Creator</title>
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
        
        .template-selector {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 3rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            border: 1px solid rgba(0, 0, 0, 0.04);
        }
        
        .selector-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0 0 1.5rem 0;
        }
        
        .template-options {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .template-option {
            flex: 1;
            min-width: 200px;
            max-width: 250px;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            border: 2px solid rgba(0, 0, 0, 0.08);
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            color: inherit;
        }
        
        .template-option:hover {
            border-color: var(--accent-color);
            background: var(--bg-purple-light);
            transform: translateY(-2px);
        }
        
        .template-option.active {
            border-color: var(--accent-color);
            background: var(--bg-purple-light);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }
        
        .template-name {
            font-weight: 600;
            color: var(--primary-color);
            margin: 0 0 0.5rem 0;
        }
        
        .template-description {
            font-size: 0.9rem;
            color: var(--text-medium);
            margin: 0;
        }
        
        .preview-container {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            border: 1px solid rgba(0, 0, 0, 0.04);
        }
        
        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }
        
        .preview-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }
        
        .download-btn {
            padding: 0.875rem 1.5rem;
            border-radius: 12px;
            background: var(--gradient-purple);
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.15);
        }
        
        .download-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(124, 58, 237, 0.25);
        }
        
        .resume-preview {
            /* Container removed - direct resume display */
        }
        
        /* Include resume template styles directly to avoid path issues */
        /* Classic Resume Template */
        .classic {
            font-family: 'Georgia', 'Times New Roman', serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            line-height: 1.6;
            color: #2c3e50;
            background: white;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border-radius: 12px;
        }

        .classic .header {
            text-align: center;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            margin: -40px -40px 30px -40px;
            padding: 40px 40px 20px 40px;
        }

        .classic .name {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #2c3e50;
            letter-spacing: 1px;
        }

        .classic .contact {
            font-size: 16px;
            color: #5a6c7d;
            font-weight: 500;
            line-height: 1.4;
        }

        .classic .section {
            margin-bottom: 30px;
            border-left: 4px solid #3498db;
            padding-left: 20px;
        }

        .classic .section-title {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
        }

        .classic .section-title::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 50px;
            height: 2px;
            background: #3498db;
        }

        .classic .content {
            font-size: 15px;
            line-height: 1.7;
            color: #34495e;
        }
        .modern {
            font-family: 'Segoe UI', 'Arial', sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 0;
            line-height: 1.6;
            color: #2c3e50;
            background: white;
            box-shadow: 0 0 30px rgba(0,0,0,0.15);
            border-radius: 12px;
            overflow: hidden;
        }

        .modern .header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }

        .modern .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .modern .name {
            font-size: 36px;
            font-weight: 300;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .modern .contact {
            font-size: 16px;
            opacity: 0.95;
            font-weight: 400;
            position: relative;
            z-index: 1;
        }

        .modern .section {
            margin: 0;
            padding: 30px 40px;
            border-bottom: 1px solid #ecf0f1;
        }

        .modern .section:last-child {
            border-bottom: none;
        }

        .modern .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            position: relative;
            padding-left: 25px;
        }

        .modern .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 15px;
            height: 15px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 50%;
        }

        .modern .content {
            font-size: 15px;
            line-height: 1.8;
            color: #34495e;
            padding-left: 25px;
        }

        /* Minimal Resume Template */
        .minimal {
            font-family: 'Helvetica Neue', 'Helvetica', sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 60px;
            line-height: 1.8;
            color: #2c3e50;
            background: white;
            box-shadow: 0 0 40px rgba(0,0,0,0.08);
            border-radius: 4px;
        }

        .minimal .header {
            margin-bottom: 50px;
            text-align: center;
            padding-bottom: 30px;
            border-bottom: 1px solid #ecf0f1;
        }

        .minimal .name {
            font-size: 42px;
            font-weight: 100;
            margin-bottom: 12px;
            letter-spacing: 4px;
            color: #2c3e50;
            text-transform: uppercase;
        }

        .minimal .contact {
            font-size: 14px;
            color: #7f8c8d;
            font-weight: 300;
            letter-spacing: 1px;
            line-height: 1.6;
        }

        .minimal .section {
            margin-bottom: 45px;
        }

        .minimal .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 4px;
            position: relative;
            text-align: center;
        }

        .minimal .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 1px;
            background: #bdc3c7;
        }

        .minimal .content {
            font-weight: 300;
            font-size: 15px;
            line-height: 1.9;
            color: #34495e;
            text-align: left;
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
                <div class="page-title">Template Selection</div>
            </div>
            <div class="nav-actions">
                <a href="dashboard.php" class="btn-nav btn-secondary">← Dashboard</a>
                <a href="resume_form.php" class="btn-nav btn-secondary">Edit Data</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title-main">Choose Your Template</h1>
            <p class="page-subtitle">Select a professional template that best represents your style and industry</p>
        </div>
        
        <!-- Template Selection -->
        <div class="template-selector">
            <h3 class="selector-title">Available Templates</h3>
            
            <div class="template-options">
                <!-- Classic Template -->
                <a href="?template=classic" class="template-option <?php echo $selected_template === 'classic' ? 'active' : ''; ?>">
                    <h4 class="template-name">Classic</h4>
                    <p class="template-description">Traditional and professional with clean typography</p>
                </a>
                
                <!-- Modern Template -->
                <a href="?template=modern" class="template-option <?php echo $selected_template === 'modern' ? 'active' : ''; ?>">
                    <h4 class="template-name">Modern</h4>
                    <p class="template-description">Contemporary design with color accents</p>
                </a>
                
                <!-- Minimal Template -->
                <a href="?template=minimal" class="template-option <?php echo $selected_template === 'minimal' ? 'active' : ''; ?>">
                    <h4 class="template-name">Minimal</h4>
                    <p class="template-description">Clean and simple with maximum readability</p>
                </a>
            </div>
        </div>
        
        <!-- Preview and Download -->
        <div class="preview-container">
            <div class="preview-header">
                <h3 class="preview-title"><?php echo ucfirst($selected_template); ?> Template Preview</h3>
                <a href="generate_pdf.php?template=<?php echo $selected_template; ?>" class="download-btn">
                    📄 Download PDF
                </a>
            </div>
            
            <div class="resume-preview">
                <?php 
                switch ($selected_template) {
                    case 'modern':
                        echo generateModernTemplate($resume_data);
                        break;
                    case 'minimal':
                        echo generateMinimalTemplate($resume_data);
                        break;
                    default:
                        echo generateClassicTemplate($resume_data);
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>