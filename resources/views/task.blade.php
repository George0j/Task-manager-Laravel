<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Task Manager</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    background: linear-gradient(135deg, #4facfe, #00f2fe);
}

.hidden { display: none; }

#landing {
    height: 100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    flex-direction:column;
    color:white;
}

.btn {
    padding: 12px 25px;
    border-radius: 30px;
    border: none;
    cursor: pointer;
    background: orange;
    color:white;
}

#app { padding:20px; color:white; }

.card {
    background:white;
    color:black;
    padding:15px;
    border-radius:10px;
    margin-bottom:10px;
}

.priority-high {color:red;}
.priority-medium {color:orange;}
.priority-low {color:green;}

.status {padding:5px 10px; border-radius:20px; color:black;}
.pending {background:gray;}
.in_progress {background:orange;}
.done {background:green;}
</style>
</head>

<body>

<div id="landing">
    <h1>Task Manager </h1>
    <button class="btn" id="startBtn">Start</button>
</div>

<div id="app" class="hidden">

<h2>Dashboard</h2>

<form id="taskForm">
    <input type="text" id="title" placeholder="Title" required>
    <input type="date" id="due_date" required>
    <select id="priority">
        <option value="low">Low</option>
        <option value="medium">Medium</option>
        <option value="high">High</option>
    </select>
    <button>Add Task</button>
</form>

<br>

<select id="filterStatus">
    <option value="">All</option>
    <option value="pending">Pending</option>
    <option value="in_progress">In Progress</option>
    <option value="done">Done</option>
</select>
<button id="filterBtn">Filter</button>

<div id="taskList"></div>

<br>

<input type="date" id="reportDate">
<button id="reportBtn">Generate Report</button>
<pre id="reportOutput"></pre>

</div>

<script>
const API = "http://127.0.0.1:8000/api/tasks";

document.getElementById('startBtn').onclick = () => {
    document.getElementById('landing').style.display='none';
    document.getElementById('app').classList.remove('hidden');
};

document.getElementById('taskForm').onsubmit = async (e)=>{
    e.preventDefault();

    const data = {
        title: title.value,
        due_date: due_date.value,
        priority: priority.value
    };

    await fetch(API,{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify(data)
    });

    e.target.reset();
    fetchTasks();
};

async function fetchTasks(){
    let url = API;
    const status = filterStatus.value;
    if(status) url += `?status=${status}`;

    const res = await fetch(url);
    const data = await res.json();

    taskList.innerHTML = "";

    data.forEach(task=>{
        taskList.innerHTML += `
        <div class="card">
            <h3>${task.title}</h3>
            <p>${task.due_date}</p>
            <p class="priority-${task.priority}">${task.priority}</p>
            <span class="status ${task.status}">${task.status}</span>
            <br><br>
            <button onclick="updateStatus(${task.id})">Next</button>
            <button onclick="deleteTask(${task.id})">Delete</button>
        </div>
        `;
    });
}

async function updateStatus(id){
    await fetch(`${API}/${id}/status`, {method:'PATCH'});
    fetchTasks();
}

async function deleteTask(id){
    await fetch(`${API}/${id}`, {method:'DELETE'});
    fetchTasks();
}

document.getElementById('filterBtn').onclick = fetchTasks;

document.getElementById('reportBtn').onclick = async ()=>{
    const date = reportDate.value;
    const res = await fetch(`${API}/report?date=${date}`);
    const data = await res.json();
    reportOutput.textContent = JSON.stringify(data,null,2);
};

fetchTasks();
</script>

</body>
</html>