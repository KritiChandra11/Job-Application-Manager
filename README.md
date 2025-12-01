# Job Application Manager

A web-based application built with PHP and MySQL to help users track job applications, interviews, and related events.

## Features

- **User Authentication**: Register, login, and manage your profile
- **Dashboard**: View and manage all your job applications in one place
- **Job Portals**: Quick access to popular job search websites
- **Calendar**: Track interview dates and important events
- **Document Management**: Store resumes, cover letters, and other job-related documents
- **Analytics**: Gain insights into your job application process
- **Networking Contacts**: Keep track of professional connections
- **Responsive Design**: Works on desktop and mobile devices

## Tech Stack

- **Backend**: PHP 8.3
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript
- **UI Framework**: Bootstrap 4
- **Icons**: Bootstrap Icons

## Installation

### Standard Installation

1. Clone the repository to your web server directory:
   ```bash
   git clone https://github.com/username/job-application-manager.git
   ```
2. Create a MySQL database:
   ```sql
   CREATE DATABASE job_tracker;
   ```
3. Update the database configuration in `config.php`:
   ```php
   $db_host = 'localhost';  // Your database host
   $db_name = 'job_tracker'; // Your database name
   $db_user = 'root';       // Your database username
   $db_pass = '';           // Your database password
   ```
4. Run the database initialization script:
   ```bash
   php init_db.php
   ```
5. Access the application in your web browser:
   ```
   http://localhost/job-application-manager/
   ```

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
