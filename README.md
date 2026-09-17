# Task Management System

## Project Description

This project is a full-stack task management application built for the kLab Tech Upskill Program 2026 Full-Stack Coding Challenge. It demonstrates frontend development, backend APIs, MySQL database integration, and CRUD functionality using pure HTML, CSS, JavaScript, PHP, and PDO.

## Features

- Create, read, update, and delete tasks
- Filter by status and priority
- Search tasks by title or description
- View dynamic task statistics
- Toggle task status between Pending and Completed
- Responsive dashboard for desktop, tablet, and mobile
- Modal-based create/edit workflow
- Toast notifications and validation feedback
- REST API built with PHP and PDO
- MySQL-backed storage

## Technologies

- HTML5
- CSS3
- Vanilla JavaScript
- PHP 8+
- MySQL
- PDO
- REST API
- Apache/XAMPP

## Requirements

- XAMPP with Apache and MySQL running
- PHP 8+
- MySQL 8+
- Modern browser

## Installation

1. Place the project folder inside your XAMPP htdocs directory.
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin.
4. Import the `database.sql` file.
5. Confirm database credentials in `config/database.php`.
6. Open the app in your browser.

Example local URL:

http://localhost/task-management/

If your folder name differs, use the correct folder path instead.

## API Documentation

### GET /tasks

Purpose: Retrieve all tasks.

Query parameters:
- `status=Pending`
- `status=Completed`
- `priority=Low`
- `priority=Medium`
- `priority=High`
- `search=project`

Example response:

```json
{
  "success": true,
  "message": "Tasks retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "Complete project documentation",
      "description": "Finish the documentation for the project.",
      "status": "Pending",
      "priority": "High",
      "createdAt": "2026-09-17 10:30:00"
    }
  ]
}
```

### GET /tasks/:id

Purpose: Retrieve one task by ID.

Possible status codes:
- 200 OK
- 404 Not Found
- 400 Bad Request

### POST /tasks

Purpose: Create a task.

Request body:

```json
{
  "title": "Complete API implementation",
  "description": "Implement the REST API for the task system.",
  "status": "Pending",
  "priority": "High"
}
```

Possible status codes:
- 201 Created
- 400 Bad Request

### PUT /tasks/:id

Purpose: Update an existing task.

Request body:

```json
{
  "title": "Updated task",
  "description": "Updated description",
  "status": "Completed",
  "priority": "Medium"
}
```

Possible status codes:
- 200 OK
- 400 Bad Request
- 404 Not Found

### DELETE /tasks/:id

Purpose: Delete a task.

Possible status codes:
- 200 OK
- 404 Not Found
- 400 Bad Request

## Database

The application uses a MySQL database named `task_management`.

The `tasks` table contains:

- id
- title
- description
- status
- priority
- createdAt
- updatedAt

## Project Structure

- `index.php` - Main dashboard interface
- `.htaccess` - URL rewriting for clean API routes
- `database.sql` - Database schema and sample data
- `config/database.php` - PDO connection configuration
- `api/index.php` - API entry point
- `routes/api.php` - Request routing and API logic
- `controllers/TaskController.php` - Business logic for tasks
- `models/Task.php` - Validation and model rules
- `assets/css/style.css` - Styles for the app
- `assets/js/app.js` - Frontend API communication and UI logic
- `README.md` - Project documentation

## Testing

You can test the application in the browser or using tools like Postman, Thunder Client, or curl.

Examples:

```bash
curl http://localhost/task-management/tasks
curl http://localhost/task-management/tasks/1
curl -X POST http://localhost/task-management/tasks -H "Content-Type: application/json" -d '{"title":"Review API implementation","description":"Validate the API contract","status":"Pending","priority":"High"}'
```

## Future Improvements

- User authentication
- Task assignment
- Due dates
- Categories
- Reminders
- Pagination
- Sorting by due date or priority

## Notes

This challenge focuses on the core task management functionality and keeps the architecture clean, maintainable, and easy to test locally.
