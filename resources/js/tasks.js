document.addEventListener('DOMContentLoaded', () => {

    const API = '/tasks';

    const landing = document.getElementById('landing');
    const app = document.getElementById('app');

    // Show Dashboard
    document.getElementById('startBtn').addEventListener('click', () => {
        landing.style.transition = 'opacity 0.5s';
        landing.style.opacity = 0;
        setTimeout(() => {
            landing.classList.add('hidden');
            app.classList.remove('hidden');
            setTimeout(() => app.classList.add('show'), 50);
        }, 500);
    });

    // Create Task
    document.getElementById('taskForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = { 
            title: document.getElementById('title').value,
            due_date: document.getElementById('due_date').value,
            priority: document.getElementById('priority').value
        };
        const res = await fetch(API, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(data) });
        if (!res.ok) {
            const err = await res.text();
            return alert('Error: ' + err);
        }
        e.target.reset();
        fetchTasks();
    });

    // Fetch Tasks
    async function fetchTasks() {
        let url = API;
        const status = document.getElementById('filterStatus').value;
        if (status) url += `?status=${status}`;
        const res = await fetch(url);
        const data = await res.json();
        const container = document.getElementById('taskList');
        container.innerHTML = '';
        if (!data.length) { container.innerHTML = '<p style="color:white;">No tasks found</p>'; return; }
        data.forEach(task => {
            const div = document.createElement('div');
            div.className = 'card';
            div.innerHTML = `
                <h3>${task.title}</h3>
                <p>📅 ${task.due_date}</p>
                <p class="priority-${task.priority}">Priority: ${task.priority}</p>
                <span class="status ${task.status}">${task.status}</span>
                <div class="flex">
                    <button class="btn-success" onclick="updateStatus(${task.id})">Next</button>
                    <button class="btn-danger" onclick="deleteTask(${task.id})">Delete</button>
                </div>
            `;
            container.appendChild(div);
        });
    }

    // Update & Delete (global functions)
    window.updateStatus = async function(id) {
        const res = await fetch(`${API}/${id}/status`, { method: 'PATCH' });
        if (!res.ok) alert('Cannot update status');
        fetchTasks();
    }

    window.deleteTask = async function(id) {
        const res = await fetch(`${API}/${id}`, { method: 'DELETE' });
        if (!res.ok) alert('Only DONE tasks can be deleted');
        fetchTasks();
    }

    // Daily Report
    document.getElementById('reportBtn').addEventListener('click', async () => {
        const date = document.getElementById('reportDate').value;
        const res = await fetch(`${API}/report?date=${date}`);
        const data = await res.json();
        document.getElementById('reportOutput').textContent = JSON.stringify(data, null, 2);
    });

    // Filter Tasks
    document.getElementById('filterBtn').addEventListener('click', fetchTasks);

    // Initial load
    fetchTasks();
});