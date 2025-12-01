# Job Application Manager 📊

A comprehensive full-stack web application designed to streamline the job search process by tracking applications, managing interviews, organizing documents, and maintaining professional networking contacts.

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![PHP Version](https://img.shields.io/badge/PHP-8.3-blue.svg)
![Python Version](https://img.shields.io/badge/Python-3.11-blue.svg)
![Docker](https://img.shields.io/badge/Docker-Ready-blue.svg)

## 🌟 Key Features

### Application Tracking
- **Status Management**: Track applications through multiple stages (Pending, Applied, Interview, Offer, Rejected)
- **Company Details**: Store company names, job titles, application dates, and descriptions
- **Bulk Management**: View and manage all applications in a centralized dashboard

### 📅 Calendar & Interview Management
- **Interactive Calendar**: FullCalendar integration for visual scheduling
- **Interview Scheduling**: Schedule interviews, deadlines, and follow-up reminders
- **Event Types**: Categorize events (Interview, Deadline, Follow-up, Other)
- **Status Tracking**: Monitor interview outcomes and update statuses
- **Reminder System**: Set reminders for upcoming events

### 📄 Document Management
- **File Upload**: Store resumes, cover letters, offer letters, and rejection letters
- **Company Association**: Link documents to specific companies
- **Document Types**: Organize by categories (Resume, Cover Letter, Offer, Rejection, Other)
- **Easy Access**: Quick download and view capabilities
- **Search**: Filter documents by name, type, or company

### 📝 Interview Preparation
- **Detailed Notes**: Create comprehensive notes for each interview
- **Company Research**: Document company information and position details
- **Question Preparation**: Store answers to common interview questions
- **Resource Links**: Quick access to interview preparation resources
- **History Tracking**: Maintain historical records of all interviews

### 📧 Email Templates
- **40+ Templates**: Pre-written professional email templates
- **Categories**: Organized by purpose (Outreach, Follow-up, Thank You, Negotiation)
- **Personalization**: Easy variable replacement (Name, Company, Position)
- **Copy to Clipboard**: Quick copy functionality
- **Search & Filter**: Find the right template quickly

### 🤝 Professional Networking
- **Contact Management**: Store details of professional contacts
- **Interaction History**: Track all communications and meetings
- **Follow-up Reminders**: Get notified when contacts need follow-up
- **Relationship Tracking**: Categorize contacts by relationship type
- **LinkedIn Integration**: Store LinkedIn profile URLs

### 📈 Analytics Dashboard
- **Visual Statistics**: Charts showing application distribution
- **Success Metrics**: Track interview-to-offer conversion rates
- **Status Overview**: See application status breakdown
- **Calendar Analytics**: Monitor upcoming vs. completed events

### 🔗 Quick Access Features
- **Job Portal Links**: Direct links to 10+ popular job search websites
- **Industry-Specific Portals**: Access to specialized job boards
- **Resource Hub**: Curated links to career resources

## 🛠️ Tech Stack

### Backend
- **PHP 8.3**: Primary backend with Apache server
- **Flask 3.0.0**: Alternative Python implementation
- **RESTful API**: JSON-based API endpoints

### Frontend
- **Bootstrap 4**: Responsive UI framework
- **JavaScript/jQuery**: Interactive functionality
- **FullCalendar.js 5.10.1**: Calendar integration
- **Chart.js**: Analytics visualization

### Database
- **MySQL 8.0**: Primary database (Docker)
- **SQLite**: Alternative database (Flask)
- **phpMyAdmin**: Database management interface

### DevOps
- **Docker & Docker Compose**: Containerized deployment
- **Apache**: Web server
- **Git**: Version control

## 📋 Prerequisites

- Docker Desktop (Windows/Mac/Linux)
- Docker Compose
- Git
- Web Browser (Chrome, Firefox, Safari, Edge)

### Optional (for Python version)
- Python 3.11+
- pip (Python package manager)

## 🚀 Quick Start

### Using Docker (Recommended)

1. **Clone the repository**
```bash
git clone https://github.com/KritiChandra11/job-application-manager.git
cd job-application-manager
```

2. **Start Docker containers**
```bash
docker-compose up -d
```

3. **Access the application**
- Main Application: http://localhost:8080
- phpMyAdmin: http://localhost:8081
  - Username: `root`
  - Password: `root`

4. **Create your account**
- Navigate to http://localhost:8080
- Click "Register" and create your account
- Start tracking your job applications!

### Using Flask/Python (Alternative)

1. **Create virtual environment**
```bash
python -m venv venv
```

2. **Activate virtual environment**

**Windows:**
```bash
.\venv\Scripts\activate
```

**Mac/Linux:**
```bash
source venv/bin/activate
```

3. **Install dependencies**
```bash
pip install -r requirements.txt
```

4. **Run the application**
```bash
python app.py
```

5. **Access at** http://localhost:5000

### Docker Installation

1. Make sure Docker and Docker Compose are installed on your system.
2. Use the provided Docker configuration:
   ```bash
   docker-compose up -d
   ```
   For MariaDB installation:
   ```bash
   docker-compose -f docker-compose-mariadb.yml up -d
   ```
3. Access the application:
   ```
   http://localhost:8080/
   ```

## Default Admin Credentials

- Email: admin@example.com
- Password: admin123

## Usage

### Authentication

- Register a new account with a username and password
- Login with your credentials
- Logout when you're done

### Dashboard

- View all your job applications in a table format
- Add new job applications with details like company, position, status, etc.
- Edit or delete existing applications
- Track application status (Applied, Interviewed, Offer, Rejected, etc.)

### Calendar

- View upcoming interviews and important dates
- Set reminders for follow-ups
- Sync with your application statuses

### Documents

- Upload and store resumes, cover letters, and portfolios
- Organize documents by job application
- Track document versions

### Analytics

- View statistics about your job applications
- Track success rates and response times
- Identify patterns in your job search

### Job Portals

- Access popular job search websites directly from the application
- Save time by having all your job search resources in one place

### Networking

- Store contact information for recruiters and hiring managers
- Log networking activities and follow-ups
- Set reminders for networking opportunities

## File Structure

```
job_application_manager/
├── api/
│   ├── calendar_events.php
│   └── events.php
├── models/
│   ├── Event.php
│   └── User.php
├── static/
│   └── css/
│       └── style.css
├── templates/
│   ├── analytics.php
│   ├── calendar.php
│   ├── dashboard.html
│   ├── dashboard.php
│   ├── documents.php
│   ├── footer.php
│   ├── header.php
│   ├── home.php
│   ├── index.html
│   ├── interview_notes.php
│   ├── login.html
│   ├── login.php
│   ├── network.php
│   ├── portals.html
│   ├── portals.php
│   ├── register.html
│   └── register.php
├── app.py
├── config.php
├── config-mariadb.php
├── docker-compose.yml
├── docker-compose-mariadb.yml
├── Dockerfile
├── DOCKER-SETUP.md
├── index.php
├── init_db.php
├── init_db.py
├── migrate_calendar_events.php
├── models.py
├── update_calendar_status.php
└── README.md
```

## Python API Integration

The application includes Python-based components (`app.py`, `models.py`, `init_db.py`) for extended functionality:

- REST API for calendar events
- Data migration tools
- Advanced analytics processing

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Created By

Kriti Chandra
