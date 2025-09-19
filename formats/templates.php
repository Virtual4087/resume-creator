<?php
function parseExperience($experience) {
    $formatted = '';
    $lines = explode("\n", $experience);
    $currentJob = '';
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Check if it's a job title line (contains dash and parentheses)
        if (preg_match('/^(.+?)\s*-\s*(.+?)\s*\((.+?)\)(?:\s*-\s*(.+?))?$/', $line, $matches)) {
            if ($currentJob) $formatted .= '</div>'; // Close previous job
            $jobTitle = trim($matches[1]);
            $company = trim($matches[2]);
            $dates = trim($matches[3]);
            $location = isset($matches[4]) ? trim($matches[4]) : '';
            
            $formatted .= '<div class="job-entry">';
            $formatted .= '<div class="job-header">';
            $formatted .= '<div class="job-title-company">';
            $formatted .= '<strong class="job-title">' . htmlspecialchars($jobTitle) . '</strong>';
            $formatted .= '<span class="company"> at ' . htmlspecialchars($company) . '</span>';
            $formatted .= '</div>';
            $formatted .= '<div class="job-meta">';
            $formatted .= '<span class="dates">' . htmlspecialchars($dates) . '</span>';
            if ($location) $formatted .= '<span class="location"> • ' . htmlspecialchars($location) . '</span>';
            $formatted .= '</div>';
            $formatted .= '</div>';
            $currentJob = true;
        } else {
            // It's a description line
            if ($currentJob) {
                $formatted .= '<div class="job-description">' . htmlspecialchars($line) . '</div>';
            }
        }
    }
    
    if ($currentJob) $formatted .= '</div>'; // Close last job
    return $formatted;
}

function parseEducation($education) {
    $formatted = '';
    $lines = explode("\n", $education);
    $currentEdu = '';
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Check if it's a degree line
        if (preg_match('/^(.+?)\s*-\s*(.+?)\s*\((.+?)\)(?:\s*-\s*GPA:\s*(.+?))?$/', $line, $matches)) {
            if ($currentEdu) $formatted .= '</div>'; // Close previous education
            $degree = trim($matches[1]);
            $school = trim($matches[2]);
            $dates = trim($matches[3]);
            $gpa = isset($matches[4]) ? trim($matches[4]) : '';
            
            $formatted .= '<div class="edu-entry">';
            $formatted .= '<div class="edu-header">';
            $formatted .= '<div class="degree-school">';
            $formatted .= '<strong class="degree">' . htmlspecialchars($degree) . '</strong>';
            $formatted .= '<span class="school"> - ' . htmlspecialchars($school) . '</span>';
            $formatted .= '</div>';
            $formatted .= '<div class="edu-meta">';
            $formatted .= '<span class="dates">' . htmlspecialchars($dates) . '</span>';
            if ($gpa) $formatted .= '<span class="gpa"> • GPA: ' . htmlspecialchars($gpa) . '</span>';
            $formatted .= '</div>';
            $formatted .= '</div>';
            $currentEdu = true;
        } else {
            // It's a description line
            if ($currentEdu) {
                $formatted .= '<div class="edu-description">' . htmlspecialchars($line) . '</div>';
            }
        }
    }
    
    if ($currentEdu) $formatted .= '</div>'; // Close last education
    return $formatted;
}

function parseSkills($skills) {
    $formatted = '';
    $lines = explode("\n", $skills);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Check if line contains a skill category (has colon)
        if (preg_match('/^(.+?):\s*(.+)$/', $line, $matches)) {
            $category = trim($matches[1]);
            $items = trim($matches[2]);
            
            $formatted .= '<div class="skill-category">';
            $formatted .= '<strong class="skill-title">' . htmlspecialchars($category) . ':</strong> ';
            $formatted .= '<span class="skill-items">' . htmlspecialchars($items) . '</span>';
            $formatted .= '</div>';
        }
    }
    
    return $formatted;
}

function generateClassicTemplate($data) {
    return '
    <div class="classic">
        <div class="header">
            <div class="name">' . htmlspecialchars($data['full_name']) . '</div>
            <div class="contact">
                ' . htmlspecialchars($data['email']) . ' | ' . htmlspecialchars($data['phone']) . '<br>
                ' . htmlspecialchars($data['address']) . '
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">PROFESSIONAL SUMMARY</div>
            <div class="content">' . nl2br(htmlspecialchars($data['summary'])) . '</div>
        </div>
        
        <div class="section">
            <div class="section-title">WORK EXPERIENCE</div>
            <div class="content">' . parseExperience($data['experience']) . '</div>
        </div>
        
        <div class="section">
            <div class="section-title">EDUCATION</div>
            <div class="content">' . parseEducation($data['education']) . '</div>
        </div>
        
        <div class="section">
            <div class="section-title">SKILLS</div>
            <div class="content">' . parseSkills($data['skills']) . '</div>
        </div>
    </div>';
}

function generateModernTemplate($data) {
    return '
    <div class="modern">
        <div class="header">
            <div class="name">' . htmlspecialchars($data['full_name']) . '</div>
            <div class="contact">
                ' . htmlspecialchars($data['email']) . ' | ' . htmlspecialchars($data['phone']) . '<br>
                ' . htmlspecialchars($data['address']) . '
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Professional Summary</div>
            <div class="content">' . nl2br(htmlspecialchars($data['summary'])) . '</div>
        </div>
        
        <div class="section">
            <div class="section-title">Work Experience</div>
            <div class="content">' . parseExperience($data['experience']) . '</div>
        </div>
        
        <div class="section">
            <div class="section-title">Education</div>
            <div class="content">' . parseEducation($data['education']) . '</div>
        </div>
        
        <div class="section">
            <div class="section-title">Skills</div>
            <div class="content">' . parseSkills($data['skills']) . '</div>
        </div>
    </div>';
}

function generateMinimalTemplate($data) {
    return '
    <div class="minimal">
        <div class="header">
            <div class="name">' . htmlspecialchars($data['full_name']) . '</div>
            <div class="contact">
                ' . htmlspecialchars($data['email']) . ' | ' . htmlspecialchars($data['phone']) . '<br>
                ' . htmlspecialchars($data['address']) . '
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Summary</div>
            <div class="content">' . nl2br(htmlspecialchars($data['summary'])) . '</div>
        </div>
        
        <div class="section">
            <div class="section-title">Experience</div>
            <div class="content">' . parseExperience($data['experience']) . '</div>
        </div>
        
        <div class="section">
            <div class="section-title">Education</div>
            <div class="content">' . parseEducation($data['education']) . '</div>
        </div>
        
        <div class="section">
            <div class="section-title">Skills</div>
            <div class="content">' . parseSkills($data['skills']) . '</div>
        </div>
    </div>';
}
?>