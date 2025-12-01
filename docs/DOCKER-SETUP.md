# Docker Setup Instructions

This file contains instructions for running the Job Application Manager using Docker.

## Prerequisites

- Docker Desktop installed and running
- Docker Compose installed (comes with Docker Desktop for Windows)

## Running the Application

1. Open a terminal/PowerShell in this directory
2. Run the following command to start all services:

```
docker-compose up -d
```

3. Wait for the containers to start (this may take a minute or two)
4. Access the application at:
   - Main application: http://localhost:8080
   - phpMyAdmin (database admin): http://localhost:8081 (login with root/root)

5. Initialize the database by visiting:
   http://localhost:8080/init_db.php

6. You can now use the application by going to:
   http://localhost:8080

## Default Admin Login

- Email: admin@example.com
- Password: admin123

## Stopping the Application

To stop all containers:

```
docker-compose down
```

To stop and remove all data (including the database):

```
docker-compose down -v
```

## Troubleshooting

If you encounter database connection issues, make sure:
1. All containers are running: `docker-compose ps`
2. Check container logs: `docker-compose logs mysql`
3. You've initialized the database by visiting init_db.php

For PHP errors, check the app container logs:
```
docker-compose logs app
```
