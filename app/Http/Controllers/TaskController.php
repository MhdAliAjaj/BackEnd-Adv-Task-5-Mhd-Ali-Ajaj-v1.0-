<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = Task::query();

        // فلترة حسب الأولوية والحالة
        if ($request->has('priority')) {
            $tasks->priority($request->priority);
        }

        if ($request->has('status')) {
            $tasks->status($request->status);
        }

        return response()->json($tasks->get());
    }

    public function store(Request $request)
    {
        // تحقق من دور المستخدم
        $this->authorize('create', Task::class);

        $task = Task::create($request->all());

        return response()->json($task, 201);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        // تحقق من أن المستخدم المعيّن إله هو اللي بيقدر يعدل
        if (auth()->user()->id !== $task->assigned_to) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $task->update($request->all());

        return response()->json($task);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete(); // Soft Delete

        return response()->json(['message' => 'Task deleted successfully']);
    }

    public function assign(Request $request, $taskId)
{
    // تحقق من أن المستخدم له دور المدير (Manager)
    $this->authorize('assign', Task::class);

    // العثور على المهمة
    $task = Task::findOrFail($taskId);

    // تعيين المهمة لمستخدم
    $task->assigned_to = $request->assigned_to;
    $task->save();

    return response()->json(['message' => 'Task assigned successfully']);
}

    public function getUserTasks($userId)
{
    // استرجاع المستخدم مع مهامه
    $user = User::with('tasks')->findOrFail($userId);
    return response()->json($user->tasks);
}
}

