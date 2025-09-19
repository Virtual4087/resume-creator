# 🚀 Resume Creator - Professional Resume Builder

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-003B57?style=for-the-badge&logo=sqlite&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

A modern, responsive web application for creating professional resumes with multiple templates. Built with PHP 8.2, featuring a beautiful Purple/Gray design system and seamless user experience.

## ✨ Features

### 🎨 **Modern UI/UX Design**
- **Purple/Gray Color Scheme**: Contemporary design with professional aesthetics
- **Responsive Layout**: Optimized for desktop, tablet, and mobile devices
- **Glass Morphism Effects**: Modern backdrop blur and transparency effects
- **Smooth Animations**: Hover effects and transitions throughout the app

### 👤 **User Management**
- **Secure Authentication**: Registration and login with session management
- **User Dashboard**: Personalized dashboard with progress tracking
- **Profile Management**: User-specific resume data and history

### 📄 **Resume Creation**
- **Dynamic Form Builder**: Comprehensive resume data input with real-time validation
- **Experience Management**: Add up to 5 work experiences with detailed descriptions
- **Education Tracking**: Multiple education entries with GPA and descriptions
- **Skills Categories**: Customizable skill categories (Technical, Soft Skills, etc.)
- **Auto-Save Functionality**: Never lose your progress

### 🎯 **Professional Templates**
- **Classic Template**: Traditional serif design with professional formatting
- **Modern Template**: Contemporary design with color accents and modern layout
- **Minimal Template**: Clean, simple design with maximum readability
- **Template Preview**: Live preview of selected template before generation

### 📥 **PDF Generation**
- **High-Quality PDFs**: Professional PDF output optimized for printing
- **Clean Print Layout**: Headers and footers removed for clean documents
- **Color Preservation**: Maintains template colors and formatting in PDF
- **One-Click Download**: Simple PDF generation and download process

### 📊 **Resume Management**
- **Resume History**: View all previously generated resumes
- **Template Switching**: Easy switching between different template designs
- **Data Persistence**: Resume data saved and accessible across sessions
- **Progress Tracking**: Visual indicators of completion status

## 🛠️ Technology Stack

- **Backend**: PHP 8.2 with PDO for database operations
- **Database**: SQLite for lightweight, file-based data storage
- **Frontend**: Vanilla HTML5, CSS3, and JavaScript
- **Containerization**: Docker for easy deployment and development
- **Styling**: Custom CSS with CSS Variables and modern design patterns

## 🚀 Quick Start

### Prerequisites
- Docker and Docker Compose installed on your system
- Git for cloning the repository

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd resume-creator-php
   ```

2. **Start the application with Docker**
   ```bash
   docker-compose up -d
   ```

3. **Access the application**
   Open your browser and navigate to: `http://localhost:8000`

4. **Create your first resume**
   - Register a new account or login
   - Fill out the resume form with your information
   - Choose from available templates
   - Download your professional PDF resume

### Development Setup

For development with hot-reload:
```bash
# Build and start the container
docker-compose up --build

# View logs
docker-compose logs -f

# Stop the application
docker-compose down
```

## 📁 Project Structure

```
resume-creator-php/
├── 📂 config/                 # Configuration files
│   └── db.php                # Database connection and setup
├── 📂 db/                    # SQLite database storage
│   └── database.sqlite       # Main application database
├── 📂 formats/               # Resume templates and styling
│   ├── styles.css           # Template-specific styles
│   └── templates.php        # Resume template generators
├── 📂 public/                # Web-accessible files
│   ├── 📂 assets/           # CSS, JS, and static assets
│   ├── dashboard.php        # User dashboard
│   ├── login.php           # User authentication
│   ├── register.php        # User registration
│   ├── resume_form.php     # Resume data input form
│   ├── my_resumes.php      # Template selection
│   ├── resume_history.php  # Resume history viewer
│   ├── generate_pdf.php    # PDF generation and preview
│   └── index.php           # Application entry point
├── 📂 src/                   # PHP business logic (currently minimal)
├── 📂 templates/             # Additional template files
├── 📂 tmp/                   # Session storage and temporary files
├── 🐳 Dockerfile            # Container configuration
├── 🐳 docker-compose.yml    # Multi-container orchestration
└── 📋 README.md             # This file
```

## 🎨 Design System

### Color Palette
- **Primary**: Deep slate (#2c3e50)
- **Secondary**: Purple gradient (#7c3aed to #a855f7)
- **Accent**: Bright purple (#7c3aed)
- **Background**: Light gray (#fafbfc)
- **Text**: Dark slate (#2c3e50) with medium gray (#7f8c8d) for secondary text

### Typography
- **Primary Font**: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto'
- **Headings**: 600-700 weight with proper hierarchy
- **Body Text**: 400-500 weight with optimal line height (1.6)

### Components
- **Glass Cards**: backdrop-blur with subtle transparency
- **Gradient Buttons**: Purple gradients with hover animations
- **Modern Forms**: Clean inputs with focus states
- **Responsive Grid**: Flexbox-based layouts

## 🔧 Configuration

### Database Schema
The application uses SQLite with the following tables:
- `users` - User authentication and profile data
- `resume_data` - User resume information
- `generated_resumes` - History of generated PDFs

### Environment Variables
- **PHP_SESSION_PATH**: `/app/tmp` (configured in Dockerfile)
- **DATABASE_PATH**: `../db/database.sqlite`

### Docker Configuration
- **Port**: 8000 (mapped to host port 8000)
- **PHP Version**: 8.2-cli
- **Volume Mounting**: Source code mounted for development
- **Session Storage**: Persistent session files in `/app/tmp`

## 📱 Responsive Design

The application is fully responsive with breakpoints at:
- **Mobile**: < 480px
- **Tablet**: 481px - 768px
- **Desktop**: > 768px

Key responsive features:
- Collapsible navigation on mobile
- Stacked form layouts on smaller screens
- Touch-friendly button sizes
- Optimized typography scaling

## 🔒 Security Features

- **Session Management**: Secure PHP sessions with custom storage
- **SQL Injection Prevention**: PDO prepared statements
- **XSS Protection**: Input sanitization with `htmlspecialchars()`
- **Authentication Guards**: Route protection for authenticated users
- **Data Validation**: Client and server-side form validation

## 🚀 Deployment

### Production Deployment
1. **Clone the repository** on your production server
2. **Configure environment** variables if needed
3. **Run with Docker Compose**:
   ```bash
   docker-compose up -d --build
   ```
4. **Set up reverse proxy** (Nginx/Apache) if needed
5. **Configure SSL** for HTTPS

### Scaling Considerations
- **Database**: Consider PostgreSQL/MySQL for multi-user production
- **File Storage**: Implement cloud storage for generated PDFs
- **Caching**: Add Redis for session storage and caching
- **Load Balancing**: Use multiple container instances

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. **Fork the repository**
2. **Create a feature branch**: `git checkout -b feature/amazing-feature`
3. **Commit your changes**: `git commit -m 'Add amazing feature'`
4. **Push to the branch**: `git push origin feature/amazing-feature`
5. **Open a Pull Request**

### Development Guidelines
- Follow PSR-12 PHP coding standards
- Write clear, documented code
- Test thoroughly before submitting
- Maintain responsive design principles
- Follow the established color scheme and design patterns

## 🐛 Troubleshooting

### Common Issues

**Application won't start:**
```bash
# Check if port 8000 is available
lsof -i :8000

# Rebuild containers
docker-compose down && docker-compose up --build
```

**Database connection errors:**
```bash
# Check database file permissions
ls -la db/database.sqlite

# Restart containers
docker-compose restart
```

**Session issues:**
```bash
# Clear session files
rm -rf tmp/sess_*

# Restart application
docker-compose restart
```

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

For support and questions:
- Create an issue in the repository
- Check the troubleshooting section above
- Review the code documentation

## 🎯 Future Enhancements

- [ ] Additional resume templates
- [ ] Cover letter generation
- [ ] LinkedIn integration
- [ ] Resume analytics
- [ ] Team collaboration features
- [ ] API for third-party integrations
- [ ] Advanced formatting options
- [ ] Multi-language support

---

**Built with ❤️ using modern web technologies**

*Create professional resumes that get you noticed. Modern tools for modern careers.*
