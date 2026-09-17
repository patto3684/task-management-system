CREATE DATABASE IF NOT EXISTS task_manager;
USE task_manager;

CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('Pending', 'Completed') NOT NULL DEFAULT 'Pending',
    priority ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Medium',
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Sample seed data
INSERT INTO tasks (title, description, status, priority)
VALUES
    ('Prepare sprint backlog', 'Review tasks for the next sprint and assign ownership.', 'Pending', 'High'),
    ('Update project documentation', 'Refresh the onboarding guide and API notes.', 'Completed', 'Medium'),
    ('Fix login issue', 'Investigate reported login failures on the admin portal.', 'Pending', 'High');
