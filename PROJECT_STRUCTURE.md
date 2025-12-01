# Project Structure

This document explains the organization of the Job Application Manager codebase.

```
job-application-manager/
│
├── 📁 api/                          # RESTful API Endpoints
│   ├── events.php                   # Job applications CRUD
│   └── calendar_events.php          # Calendar events CRUD
│
├── 📁 config/                       # Alternative Configurations
│   ├── config-mariadb.php          # MariaDB configuration (alternative)
│   ├── docker-compose-mariadb.yml  # MariaDB Docker setup
│   └── pyrightconfig.json          # Python type checking config
│
├── 📁 data/                         # Static Data Files
│   └── email_templates.json        # 40 email templates (JSON)
│
├── 📁 docs/                         # Documentation
│   ├── DOCKER-SETUP.md             # Docker deployment guide
│   └── PRESENTATION-GUIDE.md       # Project presentation guide
│
├── 📁 models/                       # Business Logic Layer
│   ├── Event.php                   # Job application model
│   └── User.php                    # User authentication model
│
├── 📁 scripts/                      # Utility & Test Scripts
│   ├── create_db.php               # Database initialization
│   ├── test_calendar.php           # Calendar debugging
│   ├── test_dashboard.php          # Dashboard testing
│   ├── test_db.php                 # Database connection test
│   ├── session_debug.php           # Session debugging
│   ├── migrate_calendar_events.php # Data migration script
│   └── update_calendar_status.php  # Status update utility
│
├── 📁 static/                       # Frontend Assets
│   └── css/
│       └── style.css               # Custom CSS styles
│
├── 📁 templates/                    # PHP View Templates
│   ├── header.php                  # Common header
│   ├── footer.php                  # Common footer
│   ├── home.php                    # Landing page
│   ├── login.php                   # Login form
│   ├── register.php                # Registration form
│   ├── dashboard.php               # Main dashboard
│   ├── calendar.php                # Calendar view
│   ├── analytics.php               # Statistics/analytics
│   ├── documents.php               # Document manager
│   ├── interview_notes.php         # Interview notes
│   ├── email_templates.php         # Email template browser
│   ├── network.php                 # Network contacts
│   └── portals.php                 # Job portal links
│
├── 📁 uploads/                      # User Uploaded Files
│   ├── .gitkeep                    # Keep folder in Git
│   └── {user_id}/                  # User-specific folders
│
├── 📄 index.php                     # Main Controller/Router
├── 📄 config.php                    # Database & Session Config
├── 📄 docker-compose.yml            # Docker orchestration
├── 📄 Dockerfile                    # PHP-Apache image config
├── 📄 .gitignore                    # Git ignore rules
├── 📄 README.md                     # Project documentation
└── 📄 organize_project.ps1          # Project organization script
```

## 📂 Folder Descriptions

### **Core Application Files (Root)**
- `index.php` - Main entry point, handles routing and authentication
- `config.php` - Database connection, session management, helper functions

### **api/** - RESTful API Layer
Contains PHP files that handle AJAX requests and return JSON responses.
- Follows REST principles (GET, POST, PUT, DELETE)
- Session-based authentication
- JSON request/response format

### **models/** - Data Models
PHP classes representing database entities with CRUD operations.
- `User.php` - User authentication, password hashing
- `Event.php` - Job application business logic

### **templates/** - View Layer
PHP files containing HTML/JavaScript for each page.
- Uses Bootstrap 4 for styling
- jQuery for AJAX interactions
- FullCalendar.js for calendar view

### **static/** - Static Assets
CSS, JavaScript, and image files.
- Custom styles extending Bootstrap
- Client-side JavaScript libraries

### **data/** - JSON Data Storage
Static data files read by the application.
- Email templates with placeholders
- Future: Job portal configurations

### **uploads/** - User Files
Dynamically created folders for each user's uploaded documents.
- Structure: `uploads/{user_id}/{timestamp}_{filename}`
- Supported: PDF, DOC, DOCX

### **scripts/** - Utility Scripts
Helper scripts for setup, testing, and maintenance.
- Database initialization
- Debugging tools
- Migration scripts

### **docs/** - Documentation
Project documentation for developers and users.
- Setup guides
- Deployment instructions
- Presentation materials

### **config/** - Alternative Configurations
Alternative configuration files for different setups.
- MariaDB instead of MySQL
- Development vs Production configs

## 🔧 Key Files

| File | Purpose |
|------|---------|
| `index.php` | Main controller, routing, authentication checks |
| `config.php` | Database connection, session initialization |
| `docker-compose.yml` | Orchestrates 3 containers (app, mysql, phpmyadmin) |
| `Dockerfile` | Custom PHP 8.3 + Apache image |
| `.gitignore` | Excludes uploads, logs, env files from Git |
| `README.md` | Project overview and installation guide |

## 🚀 Getting Started

1. **Clone the repository**
2. **Run organization script** (optional): `.\organize_project.ps1`
3. **Start Docker**: `docker compose up -d`
4. **Access app**: http://localhost:8080

## 📝 Notes

- Python files (`app.py`, `models.py`) are legacy Flask implementation - ignore them
- The project uses PHP/MySQL stack, not Python/Flask
- `venv/` and `__pycache__/` should be deleted (Python artifacts)
- Test files in `scripts/` are for development debugging

---

**Last Updated:** December 2025
