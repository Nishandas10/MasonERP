<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::where('company_id', $request->user()->company_id)
            ->with('role')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json(['data' => $users]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)->letters()->numbers()],
            'role_id'  => 'required|exists:roles,id',
            'phone'    => 'nullable|string|max:20',
        ]);

        // Ensure the role belongs to the same company
        $role = Role::where('id', $validated['role_id'])
            ->where('company_id', $request->user()->company_id)
            ->firstOrFail();

        $user = User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'role_id'    => $role->id,
            'company_id' => $request->user()->company_id,
            'phone'      => $validated['phone'] ?? null,
            'status'     => 'active',
        ]);

        return response()->json(['message' => 'User created.', 'data' => $user->load('role')], 201);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        abort_if($user->company_id !== $request->user()->company_id, 403);

        return response()->json(['data' => $user->load('role')]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        abort_if($user->company_id !== $request->user()->company_id, 403);

        $validated = $request->validate([
            'name'    => 'sometimes|required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'role_id' => 'nullable|exists:roles,id',
            'status'  => 'nullable|in:active,inactive',
        ]);

        $user->update($validated);

        return response()->json(['message' => 'User updated.', 'data' => $user->load('role')]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        abort_if($user->company_id !== $request->user()->company_id, 403);
        abort_if($user->id === $request->user()->id, 400, 'Cannot delete your own account.');

        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }
}
