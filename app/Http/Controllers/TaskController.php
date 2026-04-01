<?php
namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class TaskController extends Controller
{
    // 1. List Tasks
    public function index(Request $request)
    {
        $query = Task::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
                       ->orderBy('due_date', 'asc')
                       ->get();

        if ($tasks->isEmpty()) {
            return response()->json(['message' => 'No tasks found.'], 200);
        }

        return response()->json($tasks);
    }

    // 2. Create Task
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => [
                'required',
                Rule::unique('tasks')->where(fn($query) => $query->where('due_date', $request->due_date))
            ],
            'due_date' => 'required|date|after_or_equal:today',
            'priority' => ['required', Rule::in(['low','medium','high'])],
        ]);

        $task = Task::create($validated);

        return response()->json($task, 201);
    }

    // 3. Update Status
    public function updateStatus(Task $task, Request $request)
    {
        $statuses = ['pending', 'in_progress', 'done'];

        $currentIndex = array_search($task->status, $statuses);

        if ($currentIndex === false || $currentIndex === count($statuses) - 1) {
            return response()->json(['message' => 'Cannot progress status further.'], 403);
        }

        $task->status = $statuses[$currentIndex + 1];
        $task->save();

        return response()->json($task);
    }

    // 4. Delete Task
    public function destroy(Task $task)
    {
        if ($task->status !== 'done') {
            return response()->json(['message' => 'Only done tasks can be deleted.'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully.']);
    }

    // 5. Daily Report
    public function dailyReport(Request $request)
    {
        $date = $request->query('date', Carbon::today()->toDateString());

        $tasks = Task::whereDate('due_date', $date)->get();

        $summary = [
            'high' => ['pending'=>0,'in_progress'=>0,'done'=>0],
            'medium' => ['pending'=>0,'in_progress'=>0,'done'=>0],
            'low' => ['pending'=>0,'in_progress'=>0,'done'=>0],
        ];

        foreach ($tasks as $task) {
            $summary[$task->priority][$task->status]++;
        }

        return response()->json([
            'date' => $date,
            'summary' => $summary
        ]);
    }
    //
    public function show(Task $task)
        {
            return response()->json($task);
        }
}
