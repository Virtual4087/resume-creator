<?php
require_once '../config/db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get existing resume data if available
$stmt = $db->prepare('SELECT * FROM resume_data WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
$stmt->execute([$_SESSION['user_id']]);
$existing_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Parse existing structured data if available
$structured_data = [
    'full_name' => $existing_data['full_name'] ?? '',
    'email' => $existing_data['email'] ?? '',
    'phone' => $existing_data['phone'] ?? '',
    'address' => $existing_data['address'] ?? '',
    'summary' => $existing_data['summary'] ?? '',
    'job_title_1' => '',
    'company_1' => '',
    'job_dates_1' => '',
    'job_location_1' => '',
    'job_description_1' => '',
    'job_title_2' => '',
    'company_2' => '',
    'job_dates_2' => '',
    'job_location_2' => '',
    'job_description_2' => '',
    'job_title_3' => '',
    'company_3' => '',
    'job_dates_3' => '',
    'job_location_3' => '',
    'job_description_3' => '',
    'job_title_4' => '',
    'company_4' => '',
    'job_dates_4' => '',
    'job_location_4' => '',
    'job_description_4' => '',
    'job_title_5' => '',
    'company_5' => '',
    'job_dates_5' => '',
    'job_location_5' => '',
    'job_description_5' => '',
    'degree_1' => '',
    'school_1' => '',
    'edu_dates_1' => '',
    'gpa_1' => '',
    'edu_description_1' => '',
    'degree_2' => '',
    'school_2' => '',
    'edu_dates_2' => '',
    'gpa_2' => '',
    'edu_description_2' => '',
    'skill_category_1' => 'Technical Skills',
    'skill_items_1' => '',
    'skill_category_2' => 'Soft Skills', 
    'skill_items_2' => '',
    'skill_category_3' => '',
    'skill_items_3' => '',
    'skill_category_4' => '',
    'skill_items_4' => '',
    'skill_category_5' => '',
    'skill_items_5' => ''
];

// If existing data exists, try to parse structured format or use as fallback
if ($existing_data) {
    // Parse experience data
    $exp_lines = explode("\n", $existing_data['experience']);
    $job_count = 0;
    $current_description = '';
    
    for ($i = 0; $i < count($exp_lines) && $job_count < 5; $i++) {
        $line = trim($exp_lines[$i]);
        
        // Skip empty lines and bullet points
        if (empty($line) || strpos($line, '•') === 0) {
            if (!empty($line) && !empty($current_description)) {
                $current_description .= "\n" . $line;
            }
            continue;
        }
        
        // Check if it's a job title line (contains dash and parentheses)
        if (preg_match('/^(.+?)\s*-\s*(.+?)\s*\((.+?)\)(?:\s*-\s*(.+?))?$/', $line, $matches)) {
            // Save any pending description from previous job
            if ($job_count > 0 && !empty($current_description)) {
                $structured_data["job_description_$job_count"] = trim($current_description);
            }
            
            // Start new job
            $job_count++;
            $structured_data["job_title_$job_count"] = trim($matches[1]);
            $structured_data["company_$job_count"] = trim($matches[2]);
            $structured_data["job_dates_$job_count"] = trim($matches[3]);
            $structured_data["job_location_$job_count"] = isset($matches[4]) ? trim($matches[4]) : '';
            $current_description = '';
        } else {
            // It's part of job description
            if ($job_count > 0) {
                if (!empty($current_description)) {
                    $current_description .= "\n" . $line;
                } else {
                    $current_description = $line;
                }
            }
        }
    }
    
    // Save the last job's description
    if ($job_count > 0 && !empty($current_description)) {
        $structured_data["job_description_$job_count"] = trim($current_description);
    }
    
    // Parse education data
    $edu_lines = explode("\n", $existing_data['education']);
    $edu_count = 0;
    $current_edu_description = '';
    
    for ($i = 0; $i < count($edu_lines) && $edu_count < 2; $i++) {
        $line = trim($edu_lines[$i]);
        
        // Skip empty lines and bullet points
        if (empty($line) || strpos($line, '•') === 0) {
            if (!empty($line) && !empty($current_edu_description)) {
                $current_edu_description .= "\n" . $line;
            }
            continue;
        }
        
        // Check if it's a degree line (contains dash and parentheses)
        if (preg_match('/^(.+?)\s*-\s*(.+?)\s*\((.+?)\)(?:\s*-\s*GPA:\s*(.+?))?$/', $line, $matches)) {
            // Save any pending description from previous education
            if ($edu_count > 0 && !empty($current_edu_description)) {
                $structured_data["edu_description_$edu_count"] = trim($current_edu_description);
            }
            
            // Start new education entry
            $edu_count++;
            $structured_data["degree_$edu_count"] = trim($matches[1]);
            $structured_data["school_$edu_count"] = trim($matches[2]);
            $structured_data["edu_dates_$edu_count"] = trim($matches[3]);
            $structured_data["gpa_$edu_count"] = isset($matches[4]) ? trim($matches[4]) : '';
            $current_edu_description = '';
        } else {
            // It's part of education description
            if ($edu_count > 0) {
                if (!empty($current_edu_description)) {
                    $current_edu_description .= "\n" . $line;
                } else {
                    $current_edu_description = $line;
                }
            }
        }
    }
    
    // Save the last education's description
    if ($edu_count > 0 && !empty($current_edu_description)) {
        $structured_data["edu_description_$edu_count"] = trim($current_edu_description);
    }
    
    // Parse skills data
    $skills_text = $existing_data['skills'];
    $skills_lines = explode("\n", $skills_text);
    $skill_count = 0;
    
    foreach ($skills_lines as $line) {
        $line = trim($line);
        if (empty($line) || $skill_count >= 5) continue;
        
        // Check if line contains a skill category (has colon)
        if (preg_match('/^(.+?):\s*(.+)$/', $line, $matches)) {
            $skill_count++;
            $structured_data["skill_category_$skill_count"] = trim($matches[1]);
            $structured_data["skill_items_$skill_count"] = trim($matches[2]);
        }
    }
    
    // If no skills were parsed, set default values
    if ($skill_count == 0) {
        $structured_data['skill_category_1'] = 'Technical Skills';
        $structured_data['skill_category_2'] = 'Soft Skills';
    }
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $summary = $_POST['summary'] ?? '';
    
    // Build structured experience data
    $experience_parts = [];
    for ($i = 1; $i <= 5; $i++) {
        $job_title = $_POST["job_title_$i"] ?? '';
        $company = $_POST["company_$i"] ?? '';
        $job_dates = $_POST["job_dates_$i"] ?? '';
        $job_location = $_POST["job_location_$i"] ?? '';
        $job_description = $_POST["job_description_$i"] ?? '';
        
        if ($job_title && $company) {
            $title_line = "$job_title - $company ($job_dates)";
            if ($job_location) {
                $title_line .= " - $job_location";
            }
            $experience_parts[] = $title_line;
            if ($job_description) {
                $experience_parts[] = $job_description;
            }
            $experience_parts[] = ""; // Empty line between jobs
        }
    }
    $experience = implode("\n", $experience_parts);
    
    // Build structured education data
    $education_parts = [];
    for ($i = 1; $i <= 2; $i++) {
        $degree = $_POST["degree_$i"] ?? '';
        $school = $_POST["school_$i"] ?? '';
        $edu_dates = $_POST["edu_dates_$i"] ?? '';
        $gpa = $_POST["gpa_$i"] ?? '';
        $edu_description = $_POST["edu_description_$i"] ?? '';
        
        if ($degree && $school) {
            $education_line = "$degree - $school ($edu_dates)";
            if ($gpa) {
                $education_line .= " - GPA: $gpa";
            }
            $education_parts[] = $education_line;
            if ($edu_description) {
                $education_parts[] = $edu_description;
            }
            $education_parts[] = ""; // Empty line between education entries
        }
    }
    $education = implode("\n", $education_parts);
    
    // Build structured skills data
    $skills_parts = [];
    for ($i = 1; $i <= 5; $i++) {
        $category = $_POST["skill_category_$i"] ?? '';
        $items = $_POST["skill_items_$i"] ?? '';
        
        if ($category && $items) {
            $skills_parts[] = "$category: $items";
        }
    }
    $skills = implode("\n", $skills_parts);
    
    if ($full_name && $email) {
        try {
            if ($existing_data) {
                // Update existing record
                $stmt = $db->prepare('UPDATE resume_data SET full_name = ?, email = ?, phone = ?, address = ?, summary = ?, experience = ?, education = ?, skills = ? WHERE user_id = ?');
                $stmt->execute([$full_name, $email, $phone, $address, $summary, $experience, $education, $skills, $_SESSION['user_id']]);
                $message = "Resume data updated successfully! <a href='my_resumes.php'>Choose Template</a> | <a href='dashboard.php'>Back to Dashboard</a>";
            } else {
                // Insert new record
                $stmt = $db->prepare('INSERT INTO resume_data (user_id, full_name, email, phone, address, summary, experience, education, skills) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$_SESSION['user_id'], $full_name, $email, $phone, $address, $summary, $experience, $education, $skills]);
                $message = "Resume data saved successfully! <a href='my_resumes.php'>Choose Template</a> | <a href='dashboard.php'>Back to Dashboard</a>";
            }
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    } else {
        $message = "Please fill in required fields (Name and Email).";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Resume - Resume Creator</title>
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
            max-width: 1200px;
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
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .form-progress {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            border: 1px solid rgba(0, 0, 0, 0.04);
        }
        
        .progress-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .progress-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0 0 0.5rem 0;
        }
        
        .progress-subtitle {
            color: var(--text-medium);
            margin: 0;
        }
        
        .progress-bar {
            background: rgba(124, 58, 237, 0.1);
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        
        .progress-fill {
            background: var(--gradient-purple);
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .form-sections {
            display: grid;
            gap: 2rem;
        }
        
        .form-section {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            border: 1px solid rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
        }
        
        .form-section:hover {
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }
        
        .section-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--bg-purple-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-right: 1rem;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        
        .form-row.single {
            grid-template-columns: 1fr;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--primary-color);
            font-size: 0.95rem;
        }
        
        .form-input, .form-textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            box-sizing: border-box;
        }
        
        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }
        
        .form-textarea {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }
        
        .char-counter {
            font-size: 0.8rem;
            color: var(--text-medium);
            text-align: right;
            margin-top: 0.25rem;
        }
        
        .char-counter.warning {
            color: var(--warning-color);
        }
        
        .char-counter.danger {
            color: var(--danger-color);
        }
        
        .form-help {
            font-size: 0.85rem;
            color: var(--text-medium);
            margin-top: 0.5rem;
            line-height: 1.4;
        }
        
        .btn-add, .btn-remove {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-add {
            background: var(--bg-purple-light);
            color: var(--accent-color);
        }
        
        .btn-add:hover {
            background: var(--accent-color);
            color: white;
        }
        
        .btn-remove {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }
        
        .btn-remove:hover {
            background: #e74c3c;
            color: white;
        }
        
        .save-actions {
            position: sticky;
            bottom: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 1.5rem 2rem;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
            display: flex;
            gap: 1rem;
            justify-content: space-between;
            align-items: center;
            margin: 2rem -2rem -2rem -2rem;
            border-radius: 0 0 20px 20px;
        }
        
        .save-info {
            color: var(--text-medium);
            font-size: 0.9rem;
        }
        
        .save-buttons {
            display: flex;
            gap: 1rem;
        }
        
        .btn-save {
            padding: 0.875rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        /* Base button styling */
        .btn {
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            display: inline-block;
            text-align: center;
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
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .btn-secondary:hover {
            background: var(--bg-purple-light);
            border-color: var(--accent-color);
        }
        
        .btn-success {
            background: var(--gradient-purple);
            color: white;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.15);
        }
        
        .btn-success:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(124, 58, 237, 0.25);
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
            
            .form-section {
                padding: 1.5rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .save-actions {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
            }
            
            .save-buttons {
                justify-content: center;
            }
        }
        
        /* Structured form elements */
        .experience-item,
        .education-item,
        .skill-item {
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1rem;
            background: var(--bg-light);
        }
        
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .item-header h4 {
            margin: 0;
            color: var(--text-dark);
            font-size: 1.1rem;
        }
        
        .remove-btn {
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .remove-btn:hover {
            background: #dc2626;
        }
        
        .add-btn {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .add-btn:hover {
            background: var(--accent-dark);
            transform: translateY(-1px);
        }
        
        /* Skills section specific styling */
        .skill-item .form-row {
            align-items: end;
        }
        
        .skill-item .form-group:first-child {
            flex: 1;
            min-width: 200px;
        }
        
        .skill-item .form-group:last-child {
            flex: 2;
        }
        
        /* Style for mandatory skill categories */
        .skill-item:nth-child(1),
        .skill-item:nth-child(2) {
            border-left: 4px solid var(--accent-color);
        }
        
        .skill-item:nth-child(1) .item-header h4::after,
        .skill-item:nth-child(2) .item-header h4::after {
            content: " (Core)";
            font-size: 0.8rem;
            color: var(--accent-color);
            font-weight: normal;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .save-actions {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .item-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
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
                <div class="page-title">Create Resume</div>
            </div>
            <div class="nav-actions">
                <a href="dashboard.php" class="btn-secondary">← Dashboard</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <!-- Progress indicator -->
        <div class="form-progress">
            <div class="progress-header">
                <h1 class="progress-title">Build Your Professional Resume</h1>
                <p class="progress-subtitle">
                    <?php echo $existing_data ? 'Update your resume information below' : 'Fill out your information to create a professional resume'; ?>
                </p>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <small id="progressText">0% Complete</small>
        </div>
        
        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'successful') !== false ? 'alert-success' : 'alert-error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="resume_form.php" id="resumeForm">
            <div class="form-sections">
                <!-- Personal Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon">👤</div>
                        <h2 class="section-title">Personal Information</h2>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name <span class="required">*</span></label>
                            <input type="text" name="full_name" class="form-input" required placeholder="John Doe" 
                                   value="<?php echo htmlspecialchars($structured_data['full_name']); ?>" data-progress="1">
                            <div class="form-help">Your full name as it should appear on your resume</div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email Address <span class="required">*</span></label>
                            <input type="email" name="email" class="form-input" required placeholder="john@example.com" 
                                   value="<?php echo htmlspecialchars($structured_data['email']); ?>" data-progress="1">
                            <div class="form-help">Professional email address for contact</div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-input" placeholder="+1 (555) 123-4567" 
                                   value="<?php echo htmlspecialchars($structured_data['phone']); ?>" data-progress="1">
                            <div class="form-help">Your primary contact number</div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-input" placeholder="City, State, Country" 
                                   value="<?php echo htmlspecialchars($structured_data['address']); ?>" data-progress="1">
                            <div class="form-help">Location (city and country are usually sufficient)</div>
                        </div>
                    </div>
                </div>
                
                <!-- Professional Summary Section -->
                <div class="form-section">
                    <div class="section-header">
                        <span class="section-icon">📝</span>
                        <h3>Professional Summary</h3>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Professional Summary</label>
                        <textarea name="summary" class="form-textarea" rows="4" 
                                placeholder="Brief summary of your professional background, key skills, and career objectives..." 
                                data-progress="1" data-maxlength="300" oninput="updateCharCounter(this)"><?php echo htmlspecialchars($structured_data['summary']); ?></textarea>
                        <div class="char-counter" id="summary-counter">0/300 characters</div>
                        <div class="form-help">2-3 sentences highlighting your key qualifications and career goals</div>
                    </div>
                </div>
                
                <!-- Work Experience Section -->
                <div class="form-section">
                    <div class="section-header">
                        <span class="section-icon">💼</span>
                        <h3>Work Experience</h3>
                    </div>
                    
                    <div id="experience-container">
                        <?php 
                        $experience_count = max(1, count(array_filter([$structured_data['job_title_1'], $structured_data['job_title_2'], $structured_data['job_title_3'], $structured_data['job_title_4'], $structured_data['job_title_5']])));
                        for ($i = 1; $i <= max(5, $experience_count); $i++): 
                            $hasData = !empty($structured_data['job_title_' . $i]) || !empty($structured_data['company_' . $i]);
                            $style = ($i > $experience_count && !$hasData) ? 'style="display: none;"' : '';
                        ?>
                        <div class="experience-item" id="experience-<?php echo $i; ?>" <?php echo $style; ?>>
                            <div class="item-header">
                                <h4>Position #<?php echo $i; ?></h4>
                                <?php if ($i > 1): ?>
                                <button type="button" class="remove-btn" onclick="removeExperience(<?php echo $i; ?>)">Remove</button>
                                <?php endif; ?>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Job Title</label>
                                    <input type="text" name="job_title_<?php echo $i; ?>" class="form-input" 
                                           value="<?php echo htmlspecialchars($structured_data['job_title_' . $i]); ?>"
                                           placeholder="e.g., Software Engineer">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Company</label>
                                    <input type="text" name="company_<?php echo $i; ?>" class="form-input"
                                           value="<?php echo htmlspecialchars($structured_data['company_' . $i]); ?>"
                                           placeholder="e.g., Google Inc.">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Employment Dates</label>
                                    <input type="text" name="job_dates_<?php echo $i; ?>" class="form-input"
                                           value="<?php echo htmlspecialchars($structured_data['job_dates_' . $i]); ?>"
                                           placeholder="e.g., Jan 2020 - Present">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="job_location_<?php echo $i; ?>" class="form-input"
                                           value="<?php echo htmlspecialchars($structured_data['job_location_' . $i]); ?>"
                                           placeholder="e.g., San Francisco, CA">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Job Description</label>
                                <textarea name="job_description_<?php echo $i; ?>" class="form-textarea" rows="3"
                                          placeholder="Describe your key responsibilities, achievements, and technologies used..."><?php echo htmlspecialchars($structured_data['job_description_' . $i]); ?></textarea>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <button type="button" id="add-experience" class="add-btn" onclick="addExperience()">+ Add Another Position</button>
                </div>
                
                <!-- Education Section -->
                <div class="form-section">
                    <div class="section-header">
                        <span class="section-icon">🎓</span>
                        <h3>Education</h3>
                    </div>
                    
                    <div id="education-container">
                        <?php 
                        $education_count = max(1, count(array_filter([$structured_data['degree_1'], $structured_data['degree_2']])));
                        for ($i = 1; $i <= max(2, $education_count); $i++): 
                            $hasData = !empty($structured_data['degree_' . $i]) || !empty($structured_data['school_' . $i]);
                            $style = ($i > $education_count && !$hasData) ? 'style="display: none;"' : '';
                        ?>
                        <div class="education-item" id="education-<?php echo $i; ?>" <?php echo $style; ?>>
                            <div class="item-header">
                                <h4>Education #<?php echo $i; ?></h4>
                                <?php if ($i > 1): ?>
                                <button type="button" class="remove-btn" onclick="removeEducation(<?php echo $i; ?>)">Remove</button>
                                <?php endif; ?>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Degree</label>
                                    <input type="text" name="degree_<?php echo $i; ?>" class="form-input" 
                                           value="<?php echo htmlspecialchars($structured_data['degree_' . $i]); ?>"
                                           placeholder="e.g., Bachelor of Science in Computer Science">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">School/University</label>
                                    <input type="text" name="school_<?php echo $i; ?>" class="form-input"
                                           value="<?php echo htmlspecialchars($structured_data['school_' . $i]); ?>"
                                           placeholder="e.g., University of California, Berkeley">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Graduation Date</label>
                                    <input type="text" name="edu_dates_<?php echo $i; ?>" class="form-input"
                                           value="<?php echo htmlspecialchars($structured_data['edu_dates_' . $i]); ?>"
                                           placeholder="e.g., May 2020">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">GPA (Optional)</label>
                                    <input type="text" name="gpa_<?php echo $i; ?>" class="form-input"
                                           value="<?php echo htmlspecialchars($structured_data['gpa_' . $i]); ?>"
                                           placeholder="e.g., 3.8/4.0">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Additional Details (Optional)</label>
                                <textarea name="edu_description_<?php echo $i; ?>" class="form-textarea" rows="2"
                                          placeholder="Relevant coursework, honors, activities, thesis..."><?php echo htmlspecialchars($structured_data['edu_description_' . $i]); ?></textarea>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <button type="button" id="add-education" class="add-btn" onclick="addEducation()">+ Add Another Education</button>
                </div>
                
                <!-- Skills Section -->
                <div class="form-section">
                    <div class="section-header">
                        <span class="section-icon">⚡</span>
                        <h3>Skills</h3>
                    </div>
                    
                    <div id="skills-container">
                        <?php 
                        $skills_count = max(2, count(array_filter([$structured_data['skill_category_1'], $structured_data['skill_category_2'], $structured_data['skill_category_3'], $structured_data['skill_category_4'], $structured_data['skill_category_5']])));
                        for ($i = 1; $i <= max(5, $skills_count); $i++): 
                            $hasData = !empty($structured_data['skill_category_' . $i]) || !empty($structured_data['skill_items_' . $i]);
                            $style = ($i > $skills_count && !$hasData) ? 'style="display: none;"' : '';
                        ?>
                        <div class="skill-item" id="skill-<?php echo $i; ?>" <?php echo $style; ?>>
                            <div class="item-header">
                                <h4>Skill Category #<?php echo $i; ?></h4>
                                <?php if ($i > 2): ?>
                                <button type="button" class="remove-btn" onclick="removeSkill(<?php echo $i; ?>)">Remove</button>
                                <?php endif; ?>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Category Name</label>
                                    <input type="text" name="skill_category_<?php echo $i; ?>" class="form-input" 
                                           value="<?php echo htmlspecialchars($structured_data['skill_category_' . $i]); ?>"
                                           placeholder="<?php echo $i <= 2 ? ($i == 1 ? 'Technical Skills' : 'Soft Skills') : 'e.g., Languages, Certifications, Tools'; ?>">
                                </div>
                                <div class="form-group" style="flex: 2;">
                                    <label class="form-label">Skills</label>
                                    <input type="text" name="skill_items_<?php echo $i; ?>" class="form-input"
                                           value="<?php echo htmlspecialchars($structured_data['skill_items_' . $i]); ?>"
                                           placeholder="e.g., Python, JavaScript, React, SQL, AWS">
                                </div>
                            </div>
                            <div class="form-help">
                                <?php if ($i == 1): ?>
                                    Examples: Programming languages, frameworks, tools, technologies
                                <?php elseif ($i == 2): ?>
                                    Examples: Leadership, Communication, Problem-solving, Teamwork
                                <?php else: ?>
                                    Add any additional skill category relevant to your profession (e.g., Languages, Certifications, Tools)
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <button type="button" id="add-skill" class="add-btn" onclick="addSkill()">+ Add Another Skill Category</button>
                </div>
            </div>
        </form>
        
        <!-- Sticky save actions -->
        <div class="save-actions">
            <div>
                <span id="autoSaveStatus" style="color: var(--text-medium); font-size: 0.9rem;"></span>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button type="submit" form="resumeForm" class="btn btn-success">
                    <?php echo $existing_data ? '💾 Update Resume Data' : '💾 Save Resume Data'; ?>
                </button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                <?php if ($existing_data): ?>
                    <a href="my_resumes.php" class="btn btn-primary">Choose Template →</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Progress tracking
        function updateProgress() {
            const inputs = document.querySelectorAll('[data-progress]');
            let filled = 0;
            let total = inputs.length;
            
            inputs.forEach(input => {
                if (input.value.trim() !== '') {
                    filled++;
                }
            });
            
            const percentage = Math.round((filled / total) * 100);
            document.getElementById('progressFill').style.width = percentage + '%';
            document.getElementById('progressText').textContent = percentage + '% Complete';
        }
        
        // Character counter
        function updateCharCounter(textarea) {
            const maxLength = textarea.getAttribute('data-maxlength');
            const currentLength = textarea.value.length;
            const counterId = textarea.name + '-counter';
            const counter = document.getElementById(counterId);
            
            if (counter) {
                counter.textContent = currentLength + '/' + maxLength + ' characters';
                
                if (currentLength > maxLength * 0.9) {
                    counter.className = 'char-counter danger';
                } else if (currentLength > maxLength * 0.8) {
                    counter.className = 'char-counter warning';
                } else {
                    counter.className = 'char-counter';
                }
            }
        }
        
        // Add new experience entry
        function addExperience() {
            const container = document.getElementById('experience-container');
            const visibleItems = container.querySelectorAll('.experience-item:not([style*="display: none"])');
            const nextIndex = visibleItems.length + 1;
            
            if (nextIndex > 5) {
                alert('Maximum 5 experience entries allowed');
                return;
            }
            
            // Check if there's a hidden item we can show first
            const hiddenItem = container.querySelector('.experience-item[style*="display: none"]');
            if (hiddenItem) {
                hiddenItem.style.display = 'block';
                if (nextIndex >= 5) {
                    document.getElementById('add-experience').style.display = 'none';
                }
                return;
            }
            
            const experienceHTML = `
                <div class="experience-item" id="experience-${nextIndex}">
                    <div class="item-header">
                        <h4>Position #${nextIndex}</h4>
                        <button type="button" class="remove-btn" onclick="removeExperience(${nextIndex})">Remove</button>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Job Title</label>
                            <input type="text" name="job_title_${nextIndex}" class="form-input" placeholder="e.g., Software Engineer">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Company</label>
                            <input type="text" name="company_${nextIndex}" class="form-input" placeholder="e.g., Google Inc.">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Employment Dates</label>
                            <input type="text" name="job_dates_${nextIndex}" class="form-input" placeholder="e.g., Jan 2020 - Present">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Location</label>
                            <input type="text" name="job_location_${nextIndex}" class="form-input" placeholder="e.g., San Francisco, CA">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Job Description</label>
                        <textarea name="job_description_${nextIndex}" class="form-textarea" rows="3" placeholder="Describe your key responsibilities, achievements, and technologies used..."></textarea>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', experienceHTML);
            
            if (nextIndex >= 5) {
                document.getElementById('add-experience').style.display = 'none';
            }
        }
        
        // Remove experience entry
        function removeExperience(index) {
            const item = document.getElementById(`experience-${index}`);
            if (item) {
                // Clear all input values
                const inputs = item.querySelectorAll('input, textarea');
                inputs.forEach(input => input.value = '');
                
                // Hide the item instead of removing it completely
                item.style.display = 'none';
                
                // Always show the add button when an item is removed
                document.getElementById('add-experience').style.display = 'block';
            }
        }
        
        // Add new education entry
        function addEducation() {
            const container = document.getElementById('education-container');
            const visibleItems = container.querySelectorAll('.education-item:not([style*="display: none"])');
            const nextIndex = visibleItems.length + 1;
            
            if (nextIndex > 2) {
                alert('Maximum 2 education entries allowed');
                return;
            }
            
            // Check if there's a hidden item we can show first
            const hiddenItem = container.querySelector('.education-item[style*="display: none"]');
            if (hiddenItem) {
                hiddenItem.style.display = 'block';
                if (nextIndex >= 2) {
                    document.getElementById('add-education').style.display = 'none';
                }
                return;
            }
            
            const educationHTML = `
                <div class="education-item" id="education-${nextIndex}">
                    <div class="item-header">
                        <h4>Education #${nextIndex}</h4>
                        <button type="button" class="remove-btn" onclick="removeEducation(${nextIndex})">Remove</button>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Degree</label>
                            <input type="text" name="degree_${nextIndex}" class="form-input" placeholder="e.g., Bachelor of Science in Computer Science">
                        </div>
                        <div class="form-group">
                            <label class="form-label">School/University</label>
                            <input type="text" name="school_${nextIndex}" class="form-input" placeholder="e.g., University of California, Berkeley">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Graduation Date</label>
                            <input type="text" name="edu_dates_${nextIndex}" class="form-input" placeholder="e.g., May 2020">
                        </div>
                        <div class="form-group">
                            <label class="form-label">GPA (Optional)</label>
                            <input type="text" name="gpa_${nextIndex}" class="form-input" placeholder="e.g., 3.8/4.0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Additional Details (Optional)</label>
                        <textarea name="edu_description_${nextIndex}" class="form-textarea" rows="2" placeholder="Relevant coursework, honors, activities, thesis..."></textarea>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', educationHTML);
            
            if (nextIndex >= 2) {
                document.getElementById('add-education').style.display = 'none';
            }
        }
        
        // Remove education entry
        // Remove education entry
        function removeEducation(index) {
            const item = document.getElementById(`education-${index}`);
            if (item) {
                // Clear all input values
                const inputs = item.querySelectorAll('input, textarea');
                inputs.forEach(input => input.value = '');
                
                // Hide the item instead of removing it completely
                item.style.display = 'none';
                
                // Always show the add button when an item is removed
                document.getElementById('add-education').style.display = 'block';
            }
        }
        
        // Add new skill category
        function addSkill() {
            const container = document.getElementById('skills-container');
            const visibleItems = container.querySelectorAll('.skill-item:not([style*="display: none"])');
            const nextIndex = visibleItems.length + 1;
            
            if (nextIndex > 5) {
                alert('Maximum 5 skill categories allowed');
                return;
            }
            
            // Check if there's a hidden item we can show first
            const hiddenItem = container.querySelector('.skill-item[style*="display: none"]');
            if (hiddenItem) {
                hiddenItem.style.display = 'block';
                if (nextIndex >= 5) {
                    document.getElementById('add-skill').style.display = 'none';
                }
                return;
            }
            
            const skillHTML = `
                <div class="skill-item" id="skill-${nextIndex}">
                    <div class="item-header">
                        <h4>Skill Category #${nextIndex}</h4>
                        <button type="button" class="remove-btn" onclick="removeSkill(${nextIndex})">Remove</button>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="skill_category_${nextIndex}" class="form-input" placeholder="e.g., Technical Skills, Certifications, Tools">
                        </div>
                        <div class="form-group" style="flex: 2;">
                            <label class="form-label">Skills</label>
                            <input type="text" name="skill_items_${nextIndex}" class="form-input" placeholder="e.g., Skill 1, Skill 2, Skill 3">
                        </div>
                    </div>
                    <div class="form-help">Add any skill category relevant to your profession</div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', skillHTML);
            
            if (nextIndex >= 5) {
                document.getElementById('add-skill').style.display = 'none';
            }
        }
        
        // Remove skill category
        function removeSkill(index) {
            const item = document.getElementById(`skill-${index}`);
            if (item) {
                // Clear all input values
                const inputs = item.querySelectorAll('input');
                inputs.forEach(input => input.value = '');
                
                // Hide the item instead of removing it completely
                item.style.display = 'none';
                
                // Always show the add button when an item is removed
                document.getElementById('add-skill').style.display = 'block';
            }
        }
        
        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Update progress on input
            document.querySelectorAll('[data-progress]').forEach(input => {
                input.addEventListener('input', updateProgress);
            });
            
            // Initialize character counters
            document.querySelectorAll('textarea[data-maxlength]').forEach(textarea => {
                updateCharCounter(textarea);
            });
            
            // Initial progress update
            updateProgress();
        });
    </script>
</body>
</html>