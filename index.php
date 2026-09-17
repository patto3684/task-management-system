<?php
require_once __DIR__ . '/config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Task Management System</title>
    <link rel="stylesheet" href="./assets/css/style.css" />
</head>
<body>
    <div class="page-shell">
        <header class="topbar">
            <div class="brand-wrap">
                <div class="brand-mark">T</div>
                <div>
                    <p class="eyebrow">kLab Tech</p>
                    <h1>Task Management System</h1>
                </div>
            </div>
            <button id="openCreateTaskBtn" class="btn btn-primary" type="button">+ Add Task</button>
        </header>

        <section class="summary-grid" aria-label="Task statistics">
            <article class="stat-card">
                <span class="stat-label">Total Tasks</span>
                <strong id="totalTasksValue">0</strong>
            </article>
            <article class="stat-card">
                <span class="stat-label">Pending</span>
                <strong id="pendingTasksValue">0</strong>
            </article>
            <article class="stat-card">
                <span class="stat-label">Completed</span>
                <strong id="completedTasksValue">0</strong>
            </article>
            <article class="stat-card accent">
                <span class="stat-label">High Priority</span>
                <strong id="highPriorityTasksValue">0</strong>
            </article>
        </section>

        <section class="toolbar" aria-label="Task controls">
            <div class="search-wrap">
                <label class="sr-only" for="taskSearch">Search tasks</label>
                <input id="taskSearch" type="search" placeholder="Search tasks..." />
            </div>

            <div class="filter-group" aria-label="Status filters">
                <button type="button" class="filter-button is-active" data-status-filter="All">All</button>
                <button type="button" class="filter-button" data-status-filter="Pending">Pending</button>
                <button type="button" class="filter-button" data-status-filter="Completed">Completed</button>
            </div>

            <div class="priority-wrap">
                <label class="sr-only" for="priorityFilter">Priority filter</label>
                <select id="priorityFilter">
                    <option value="All">All Priorities</option>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                </select>
            </div>

            <button id="clearFiltersBtn" type="button" class="btn btn-ghost">Clear Filters</button>
        </section>

        <main>
            <div class="table-card">
                <div id="loadingIndicator" class="loading-indicator" hidden>
                    Loading tasks...
                </div>

                <div id="emptyState" class="empty-state" hidden>
                    <h3>No tasks found</h3>
                    <p>Create your first task to get started.</p>
                    <button type="button" class="btn btn-primary" id="emptyStateAddBtn">Add Task</button>
                </div>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="taskTableBody"></tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="taskModal" class="modal" aria-hidden="true">
        <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
            <div class="modal-header">
                <div>
                    <p class="eyebrow">Task</p>
                    <h2 id="modalTitle">Create Task</h2>
                </div>
                <button type="button" id="closeModalBtn" class="close-button" aria-label="Close modal">×</button>
            </div>

            <form id="taskForm">
                <input type="hidden" id="taskId" name="id" />

                <div class="form-group">
                    <label for="title">Title</label>
                    <input id="title" name="title" type="text" maxlength="255" placeholder="Enter task title" required />
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" maxlength="1000" placeholder="Describe the task"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="Pending">Pending</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" id="cancelTaskBtn" class="btn btn-ghost">Cancel</button>
                    <button type="submit" id="submitTaskBtn" class="btn btn-primary">Save Task</button>
                </div>
            </form>
        </div>
    </div>

    <div id="confirmDeleteModal" class="modal small" aria-hidden="true">
        <div class="modal-content confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="confirmDeleteTitle">
            <div class="modal-header">
                <div>
                    <p class="eyebrow">Confirm</p>
                    <h2 id="confirmDeleteTitle">Delete Task?</h2>
                </div>
                <button type="button" id="closeDeleteModalBtn" class="close-button" aria-label="Close delete dialog">×</button>
            </div>

            <p class="confirm-text">Are you sure you want to permanently delete this task?</p>

            <div class="modal-actions">
                <button type="button" id="cancelDeleteBtn" class="btn btn-ghost">Cancel</button>
                <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Delete Task</button>
            </div>
        </div>
    </div>

    <div id="toastContainer" class="toast-container" aria-live="polite" aria-atomic="true"></div>

    <script src="./assets/js/app.js"></script>
</body>
</html>
