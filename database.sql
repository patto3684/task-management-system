CREATE DATABASE IF NOT EXISTS task_management;
USE task_management;

CREATE TABLE IF NOT EXISTS tasks (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    status ENUM('Pending', 'Completed') NOT NULL DEFAULT 'Pending',
    priority ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Medium',
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_status (status),
    KEY idx_priority (priority),
    KEY idx_created_at (createdAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO tasks (title, description, status, priority) VALUES
('Complete project documentation', 'Finish the documentation for the kLab challenge and share with the team.', 'Pending', 'High'),
('Review API implementation', 'Test the REST endpoints and validate response payloads against the requirements.', 'Completed', 'Medium'),
('Prepare final presentation', 'Create slides summarizing the architecture, features, and demo flow.', 'Pending', 'High'),
('Update onboarding checklist', 'Revise internal setup guide and local development instructions for the team.', 'Completed', 'Low');
