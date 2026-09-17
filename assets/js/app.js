const PAGE_BASE = window.location.pathname.replace(/\/index\.php$/, '').replace(/\/$/, '') || '';
const API_BASE = `${PAGE_BASE}/tasks`;

const state = {
  currentStatus: 'All',
  currentPriority: 'All',
  search: '',
  editingTaskId: null,
};

const taskTableBody = document.getElementById('taskTableBody');
const summaryCards = {
  total: document.getElementById('totalTasksValue'),
  pending: document.getElementById('pendingTasksValue'),
  completed: document.getElementById('completedTasksValue'),
  high: document.getElementById('highPriorityTasksValue'),
};

const taskModal = document.getElementById('taskModal');
const taskForm = document.getElementById('taskForm');
const modalTitle = document.getElementById('modalTitle');
const taskIdInput = document.getElementById('taskId');
const titleInput = document.getElementById('title');
const descriptionInput = document.getElementById('description');
const statusInput = document.getElementById('status');
const priorityInput = document.getElementById('priority');
const submitButton = document.getElementById('submitTaskBtn');
const toastContainer = document.getElementById('toastContainer');
const loadingIndicator = document.getElementById('loadingIndicator');
const emptyState = document.getElementById('emptyState');
const statusFilterButtons = document.querySelectorAll('[data-status-filter]');
const priorityFilter = document.getElementById('priorityFilter');
const searchInput = document.getElementById('taskSearch');
const clearFiltersButton = document.getElementById('clearFiltersBtn');

function escapeHtml(value = '') {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function formatDate(value) {
  if (!value) return 'N/A';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return 'N/A';

  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(date);
}

function showLoading(show = true) {
  loadingIndicator.hidden = !show;
}

function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.textContent = message;
  toastContainer.appendChild(toast);

  setTimeout(() => {
    toast.classList.add('is-visible');
  }, 10);

  setTimeout(() => {
    toast.classList.remove('is-visible');
    setTimeout(() => toast.remove(), 320);
  }, 2500);
}

function setButtonState() {
  statusFilterButtons.forEach((button) => {
    const isActive = button.dataset.statusFilter === state.currentStatus;
    button.classList.toggle('is-active', isActive);
  });
}

function renderStatistics(tasks) {
  const total = tasks.length;
  const pending = tasks.filter((task) => task.status === 'Pending').length;
  const completed = tasks.filter((task) => task.status === 'Completed').length;
  const highPriority = tasks.filter((task) => task.priority === 'High').length;

  summaryCards.total.textContent = total;
  summaryCards.pending.textContent = pending;
  summaryCards.completed.textContent = completed;
  summaryCards.high.textContent = highPriority;
}

function renderTasks(tasks) {
  taskTableBody.innerHTML = '';

  if (!tasks.length) {
    emptyState.hidden = false;
    return;
  }

  emptyState.hidden = true;

  tasks.forEach((task) => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td data-label="Title">
        <div class="task-title">${escapeHtml(task.title)}</div>
      </td>
      <td data-label="Description">
        <div class="task-description">${escapeHtml(task.description || 'No description provided.')}</div>
      </td>
      <td data-label="Status">
        <span class="status-badge ${task.status === 'Completed' ? 'success' : 'pending'}">${escapeHtml(task.status)}</span>
      </td>
      <td data-label="Priority">
        <span class="priority-badge ${task.priority.toLowerCase()}">${escapeHtml(task.priority)}</span>
      </td>
      <td data-label="Created">
        <span class="date-text">${formatDate(task.createdAt)}</span>
      </td>
      <td data-label="Actions">
        <div class="table-actions">
          <button type="button" class="btn btn-secondary" data-action="toggle-status" data-id="${task.id}">
            ${task.status === 'Completed' ? 'Mark Pending' : 'Mark Completed'}
          </button>
          <button type="button" class="btn btn-outline" data-action="edit" data-id="${task.id}">Edit</button>
          <button type="button" class="btn btn-danger" data-action="delete" data-id="${task.id}">Delete</button>
        </div>
      </td>
    `;
    taskTableBody.appendChild(row);
  });
}

function buildQueryString() {
  const params = new URLSearchParams();

  if (state.currentStatus !== 'All') params.set('status', state.currentStatus);
  if (state.currentPriority !== 'All') params.set('priority', state.currentPriority);
  if (state.search.trim()) params.set('search', state.search.trim());

  const query = params.toString();
  return query ? `?${query}` : '';
}

async function fetchTasks() {
  showLoading(true);

  try {
    const response = await fetch(`${API_BASE}${buildQueryString()}`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
      throw new Error(result.message || 'Unable to load tasks.');
    }

    const tasks = Array.isArray(result.data) ? result.data : [];
    renderTasks(tasks);
    renderStatistics(tasks);
    return tasks;
  } catch (error) {
    taskTableBody.innerHTML = '';
    emptyState.hidden = false;
    emptyState.querySelector('h3').textContent = 'Unable to load tasks';
    emptyState.querySelector('p').textContent = error.message;
    showToast(error.message, 'error');
    return [];
  } finally {
    showLoading(false);
  }
}

function openModal(mode = 'create') {
  taskModal.classList.add('is-visible');

  if (mode === 'create') {
    modalTitle.textContent = 'Create Task';
    taskForm.reset();
    state.editingTaskId = null;
    taskIdInput.value = '';
    statusInput.value = 'Pending';
    priorityInput.value = 'Medium';
    titleInput.focus();
    return;
  }

  modalTitle.textContent = 'Edit Task';
}

function closeModal() {
  taskModal.classList.remove('is-visible');
  taskForm.reset();
  state.editingTaskId = null;
  taskIdInput.value = '';
}

async function getTaskById(taskId) {
  const response = await fetch(`${API_BASE}/${taskId}`, {
    method: 'GET',
    headers: { 'Content-Type': 'application/json' },
  });

  const result = await response.json();
  if (!response.ok || !result.success) {
    throw new Error(result.message || 'Unable to load task details.');
  }

  return result.data;
}

async function editTask(taskId) {
  try {
    const task = await getTaskById(taskId);
    state.editingTaskId = task.id;
    modalTitle.textContent = 'Edit Task';
    taskIdInput.value = task.id;
    titleInput.value = task.title || '';
    descriptionInput.value = task.description || '';
    statusInput.value = task.status || 'Pending';
    priorityInput.value = task.priority || 'Medium';
    openModal('edit');
  } catch (error) {
    showToast(error.message, 'error');
  }
}

async function deleteTask(taskId) {
  const confirmModal = document.getElementById('confirmDeleteModal');
  confirmModal.classList.add('is-visible');
  const confirmDeleteButton = document.getElementById('confirmDeleteBtn');
  confirmDeleteButton.dataset.taskId = String(taskId);
}

async function toggleTaskStatus(taskId) {
  try {
    const task = await getTaskById(taskId);
    const nextStatus = task.status === 'Completed' ? 'Pending' : 'Completed';

    const response = await fetch(`${API_BASE}/${taskId}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: nextStatus }),
    });

    const result = await response.json();
    if (!response.ok || !result.success) {
      throw new Error(result.message || 'Unable to update task status.');
    }

    showToast('Task status updated successfully', 'success');
    await fetchTasks();
  } catch (error) {
    showToast(error.message, 'error');
  }
}

function validateTaskInput(payload) {
  const errors = {};

  if (!payload.title || !payload.title.trim()) {
    errors.title = 'Title is required.';
  } else if (payload.title.trim().length > 255) {
    errors.title = 'Title must be 255 characters or less.';
  }

  if (payload.description && payload.description.trim().length > 1000) {
    errors.description = 'Description must be 1000 characters or less.';
  }

  if (payload.status && !['Pending', 'Completed'].includes(payload.status)) {
    errors.status = 'Status must be Pending or Completed.';
  }

  if (payload.priority && !['Low', 'Medium', 'High'].includes(payload.priority)) {
    errors.priority = 'Priority must be Low, Medium, or High.';
  }

  return errors;
}

async function submitTask(event) {
  event.preventDefault();

  const payload = {
    title: titleInput.value,
    description: descriptionInput.value,
    status: statusInput.value,
    priority: priorityInput.value,
  };

  const errors = validateTaskInput(payload);
  if (Object.keys(errors).length > 0) {
    const firstError = Object.values(errors)[0];
    showToast(firstError, 'error');
    return;
  }

  submitButton.disabled = true;
  submitButton.textContent = state.editingTaskId ? 'Updating...' : 'Saving...';

  try {
    const method = state.editingTaskId ? 'PUT' : 'POST';
    const url = state.editingTaskId ? `${API_BASE}/${state.editingTaskId}` : API_BASE;

    const response = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });

    const result = await response.json();
    if (!response.ok || !result.success) {
      throw new Error(result.message || 'Unable to save task.');
    }

    showToast(state.editingTaskId ? 'Task updated successfully' : 'Task created successfully', 'success');
    closeModal();
    await fetchTasks();
  } catch (error) {
    showToast(error.message, 'error');
  } finally {
    submitButton.disabled = false;
    submitButton.textContent = state.editingTaskId ? 'Update Task' : 'Save Task';
  }
}

function handleFilterChange() {
  state.currentStatus = document.querySelector('[data-status-filter].is-active')?.dataset.statusFilter || 'All';
  state.currentPriority = priorityFilter.value;
  state.search = searchInput.value.trim();
  fetchTasks();
}

statusFilterButtons.forEach((button) => {
  button.addEventListener('click', () => {
    state.currentStatus = button.dataset.statusFilter;
    setButtonState();
    handleFilterChange();
  });
});

priorityFilter.addEventListener('change', handleFilterChange);
searchInput.addEventListener('input', () => {
  state.search = searchInput.value.trim();
  handleFilterChange();
});

clearFiltersButton.addEventListener('click', () => {
  state.currentStatus = 'All';
  state.currentPriority = 'All';
  state.search = '';
  searchInput.value = '';
  priorityFilter.value = 'All';
  setButtonState();
  fetchTasks();
});

document.getElementById('openCreateTaskBtn').addEventListener('click', () => openModal('create'));
document.getElementById('closeModalBtn').addEventListener('click', closeModal);
document.getElementById('cancelTaskBtn').addEventListener('click', closeModal);
document.getElementById('closeDeleteModalBtn').addEventListener('click', () => {
  document.getElementById('confirmDeleteModal').classList.remove('is-visible');
});

document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
  const taskId = document.getElementById('confirmDeleteBtn').dataset.taskId;
  const confirmModal = document.getElementById('confirmDeleteModal');
  confirmModal.classList.remove('is-visible');

  try {
    const response = await fetch(`${API_BASE}/${taskId}`, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
    });

    const result = await response.json();
    if (!response.ok || !result.success) {
      throw new Error(result.message || 'Unable to delete task.');
    }

    showToast('Task deleted successfully', 'success');
    await fetchTasks();
  } catch (error) {
    showToast(error.message, 'error');
  }
});

taskForm.addEventListener('submit', submitTask);

taskTableBody.addEventListener('click', async (event) => {
  const button = event.target.closest('button');
  if (!button) return;

  const { action, id } = button.dataset;
  if (!action || !id) return;

  if (action === 'toggle-status') {
    await toggleTaskStatus(id);
  }

  if (action === 'edit') {
    await editTask(id);
  }

  if (action === 'delete') {
    await deleteTask(id);
  }
});

window.addEventListener('click', (event) => {
  if (event.target === taskModal) closeModal();
  if (event.target === document.getElementById('confirmDeleteModal')) {
    document.getElementById('confirmDeleteModal').classList.remove('is-visible');
  }
});

setButtonState();
fetchTasks();
