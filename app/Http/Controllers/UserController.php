<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // عرض جميع المستخدمين (للـ Admin أو Manager فقط)
    public function index()
    {
        // التحقق من الصلاحيات
        $this->authorize('viewAny', User::class);

        // عرض جميع المستخدمين مع Soft Deletes
        $users = User::withTrashed()->get();

        return response()->json($users);
    }

    // عرض مستخدم محدد
    public function show($id)
    {
        // التحقق من الصلاحيات
        $this->authorize('viewAny', User::class);

        // عرض بيانات المستخدم
        $user = User::withTrashed()->findOrFail($id);
        
        return response()->json($user);
    }

    // إنشاء مستخدم جديد (فقط Admin)
    public function store(Request $request)
    {
        // التحقق من الصلاحيات
        $this->authorize('create', User::class);

        // التحقق من صحة البيانات
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:Admin,Manager,User',
        ]);

        // إنشاء المستخدم
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }

    // تحديث مستخدم (فقط Admin)
    public function update(Request $request, $id)
    {
        // العثور على المستخدم
        $user = User::withTrashed()->findOrFail($id);

        // التحقق من الصلاحيات
        $this->authorize('update', $user);

        // التحقق من صحة البيانات
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'sometimes|required|in:Admin,Manager,User',
        ]);

        // تحديث بيانات المستخدم
        $user->update([
            'name' => $validatedData['name'] ?? $user->name,
            'email' => $validatedData['email'] ?? $user->email,
            'password' => isset($validatedData['password']) ? Hash::make($validatedData['password']) : $user->password,
            'role' => $validatedData['role'] ?? $user->role,
        ]);

        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }

    // حذف مستخدم (فقط Admin) - Soft Delete
    public function destroy($id)
    {
        // العثور على المستخدم
        $user = User::findOrFail($id);

        // التحقق من الصلاحيات
        $this->authorize('delete', $user);

        // حذف المستخدم باستخدام Soft Deletes
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    // استعادة مستخدم محذوف (فقط Admin)
    public function restore($id)
    {
        // العثور على المستخدم المحذوف
        $user = User::onlyTrashed()->findOrFail($id);

        // التحقق من الصلاحيات
        $this->authorize('restore', $user);

        // استعادة المستخدم
        $user->restore();

        return response()->json(['message' => 'User restored successfully']);
    }
}
