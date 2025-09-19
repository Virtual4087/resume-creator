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

$template = $_GET['template'] ?? 'classic';

// Save generated resume to database
$stmt = $db->prepare('INSERT INTO generated_resumes (user_id, template, full_name, created_at) VALUES (?, ?, ?, datetime("now"))');
$stmt->execute([$_SESSION['user_id'], $template, $resume_data['full_name']]);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Resume - <?php echo htmlspecialchars($resume_data['full_name']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            background: #fafbfc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            letter-spacing: -0.01em;
            margin: 0;
            padding: 0;
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
        
        .success-banner {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            border: 1px solid rgba(0, 0, 0, 0.04);
        }
        
        .success-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .success-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .success-subtitle {
            font-size: 1rem;
            color: var(--text-medium);
            margin-bottom: 2rem;
            line-height: 1.5;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 0.875rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-download {
            background: var(--gradient-purple);
            color: white;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.15);
        }
        
        .btn-download:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(124, 58, 237, 0.25);
        }
        
        .btn-template {
            background: rgba(124, 58, 237, 0.1);
            color: var(--accent-color);
            border: 1.5px solid rgba(124, 58, 237, 0.2);
        }
        
        .btn-template:hover {
            background: var(--bg-purple-light);
            border-color: var(--accent-color);
        }
        
        .btn-dashboard {
            background: white;
            color: var(--text-medium);
            border: 1.5px solid rgba(0, 0, 0, 0.1);
        }
        
        .btn-dashboard:hover {
            background: #f8f9fa;
            color: var(--primary-color);
        }
        
        .resume-preview {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            border: 1px solid rgba(0, 0, 0, 0.04);
            margin-bottom: 2rem;
        }
        
        .preview-header {
            background: var(--bg-purple-light);
            padding: 1.5rem 2rem;
            border-bottom: 1px solid rgba(124, 58, 237, 0.1);
        }
        
        .preview-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }
        
        .template-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 0.5rem;
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
        
        .resume-content {
            padding: 2rem;
        }
        
        /* Include resume template styles directly */
        .classic {
            font-family: 'Georgia', 'Times New Roman', serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
            color: #2c3e50;
            background: white;
        }

        .classic .header {
            text-align: center;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            margin: -20px -20px 30px -20px;
            padding: 30px 20px 20px 20px;
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
            padding: 30px;
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
            padding: 25px 30px;
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

        .minimal {
            font-family: 'Helvetica Neue', 'Helvetica', sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
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

        /* Creative Resume Template */
        .creative {
            font-family: 'Segoe UI', 'Arial', sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 0;
            line-height: 1.6;
            color: #2c3e50;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .creative .header {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
        }

        .creative .name {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .creative .contact {
            font-size: 1.1rem;
            position: relative;
            z-index: 1;
            opacity: 0.95;
        }

        .creative .summary-section {
            background: white;
            padding: 30px 40px;
            border-bottom: 3px solid #ff6b6b;
        }

        .creative .two-column {
            display: flex;
            background: white;
        }

        .creative .main-column {
            flex: 2;
            padding: 30px 40px;
        }

        .creative .side-column {
            flex: 1;
            background: #f8f9fa;
            padding: 30px 25px;
            border-left: 3px solid #ff6b6b;
        }

        .creative .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #ff6b6b;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .creative .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, #ff6b6b, #ee5a24);
            border-radius: 2px;
        }

        .creative .content {
            font-size: 15px;
            line-height: 1.7;
            color: #34495e;
        }

        /* Corporate Resume Template */
        .corporate {
            font-family: 'Times New Roman', serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 50px;
            line-height: 1.7;
            color: #2c3e50;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
        }

        .corporate .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 25px;
            border-bottom: 2px solid #2c3e50;
        }

        .corporate .name {
            font-size: 2.2rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .corporate .contact {
            font-size: 1rem;
            color: #5a6c7d;
            font-weight: 500;
        }

        .corporate .executive-summary {
            background: #f8f9fa;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 4px solid #2c3e50;
            border-radius: 0 8px 8px 0;
        }

        .corporate .section {
            margin-bottom: 35px;
        }

        .corporate .section-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 8px;
        }

        .corporate .content {
            font-size: 15px;
            line-height: 1.8;
            color: #34495e;
        }

        /* Tech Resume Template */
        .tech {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            max-width: 800px;
            margin: 0 auto;
            padding: 0;
            line-height: 1.6;
            color: #2c3e50;
            background: #1e1e1e;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .tech .header {
            background: #2d2d2d;
            padding: 0;
        }

        .tech .terminal-header {
            background: #3c3c3c;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #555;
        }

        .tech .terminal-buttons {
            display: flex;
            gap: 8px;
        }

        .tech .terminal-buttons .btn {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }

        .tech .close { background: #ff5f57; }
        .tech .minimize { background: #ffbd2e; }
        .tech .maximize { background: #28ca42; }

        .tech .terminal-title {
            color: #fff;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .tech .name-block {
            padding: 30px 40px;
            background: #2d2d2d;
        }

        .tech .name {
            font-size: 2rem;
            font-weight: bold;
            color: #00ff41;
            margin-bottom: 15px;
            text-shadow: 0 0 10px rgba(0, 255, 65, 0.3);
        }

        .tech .contact {
            color: #ffffff;
            font-size: 0.95rem;
            line-height: 1.8;
        }

        .tech .property {
            color: #79b6f2;
        }

        .tech .string {
            color: #98c379;
        }

        .tech .section {
            background: #1e1e1e;
            margin: 0;
            padding: 25px 40px;
            border-bottom: 1px solid #333;
        }

        .tech .section:last-child {
            border-bottom: none;
        }

        .tech .section-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #61dafb;
            margin-bottom: 20px;
            font-family: 'Monaco', monospace;
        }

        .tech .bracket {
            color: #ff79c6;
            font-weight: bold;
        }

        .tech .content {
            color: #f8f8f2;
            font-size: 14px;
            line-height: 1.7;
        }

        .tech .code-block {
            background: #282828;
            padding: 15px;
            border-radius: 5px;
            border-left: 3px solid #61dafb;
            font-family: 'Monaco', monospace;
        }

        .tech .tech-skills {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        @media print {
            @page {
                margin: 0;
                size: A4;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            body { 
                margin: 0; 
                background: white !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            .no-print { display: none !important; }
            .container {
                padding: 0;
                max-width: none;
                margin: 0;
            }
            .resume-preview {
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                border: none !important;
            }
            .preview-header {
                display: none !important;
            }
            .resume-content {
                padding: 0 !important;
            }
            
            /* Ensure resume templates print correctly */
            .classic, .modern, .minimal {
                margin: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
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
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-action {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
            
            .classic,
            .modern,
            .minimal {
                font-size: 14px;
            }
            
            .classic {
                padding: 15px;
            }
            
            .modern .header {
                padding: 20px;
            }
            
            .modern .section {
                padding: 20px;
            }
            
            .minimal {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Modern Navigation -->
    <nav class="top-nav no-print">
        <div class="nav-content">
            <div class="nav-left">
                <a href="dashboard.php" class="logo">ResumeBuilder</a>
                <div class="nav-separator"></div>
                <div class="page-title">Resume Preview</div>
            </div>
            <div class="nav-actions">
                <a href="dashboard.php" class="btn-nav btn-secondary">← Dashboard</a>
                <a href="my_resumes.php" class="btn-nav btn-primary">Choose Template</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <!-- Success Banner -->
        <div class="success-banner no-print">
            <div class="success-icon">🎉</div>
            <h1 class="success-title">Your Professional Resume is Ready!</h1>
            <p class="success-subtitle">
                Your beautifully formatted <?php echo ucfirst($template); ?> template resume is ready for download. 
                Click "Download PDF" to save your resume or try a different template design.
            </p>
            
            <div class="action-buttons">
                <button onclick="printResume()" class="btn-action btn-download">
                    📄 Download PDF
                </button>
                <a href="my_resumes.php" class="btn-action btn-template">
                    🎨 Try Different Template
                </a>
                <a href="dashboard.php" class="btn-action btn-dashboard">
                    🏠 Back to Dashboard
                </a>
            </div>
            
            <!-- Print Instructions -->
            <div style="margin-top: 1.5rem; padding: 1rem; background: rgba(124, 58, 237, 0.05); border-radius: 12px; border: 1px solid rgba(124, 58, 237, 0.1);">
                <p style="margin: 0; font-size: 0.9rem; color: var(--text-medium); text-align: left;">
                    <strong>💡 For best PDF results:</strong> In the print dialog, ensure "Headers and footers" is unchecked/disabled to remove URLs and page info from your resume.
                </p>
            </div>
        </div>
        
        <!-- Resume Preview -->
        <div class="resume-preview">
            <div class="preview-header no-print">
                <h3 class="preview-title">
                    Resume Preview - <?php echo htmlspecialchars($resume_data['full_name']); ?>
                </h3>
                <span class="template-badge template-<?php echo strtolower($template); ?>">
                    <?php echo ucfirst($template); ?> Template
                </span>
            </div>
            
            <div class="resume-content">
                <?php
                switch ($template) {
                    case 'modern':
                        echo generateModernTemplate($resume_data);
                        break;
                    case 'minimal':
                        echo generateMinimalTemplate($resume_data);
                        break;
                    case 'creative':
                        echo generateCreativeTemplate($resume_data);
                        break;
                    case 'corporate':
                        echo generateCorporateTemplate($resume_data);
                        break;
                    case 'tech':
                        echo generateTechTemplate($resume_data);
                        break;
                    default:
                        echo generateClassicTemplate($resume_data);
                }
                ?>
            </div>
        </div>
    </div>
    
    <script>
        function printResume() {
            window.print();
        }

        // Auto-hide print actions after printing
        window.addEventListener('afterprint', function() {
            console.log('Print dialog closed');
        });
        
        // Optimize print settings when page loads
        window.addEventListener('load', function() {
            // Add print-specific styling
            const printStyle = document.createElement('style');
            printStyle.type = 'text/css';
            printStyle.innerHTML = `
                @media print {
                    html, body {
                        height: 100%;
                        margin: 0 !important;
                        padding: 0 !important;
                        overflow: hidden;
                    }
                }
            `;
            document.head.appendChild(printStyle);
        });
    </script>
</body>
</html>