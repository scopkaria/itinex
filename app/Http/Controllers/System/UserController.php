<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TenantScoped;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    use TenantScoped;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = User::with('company')->orderBy('name');

        if (!$user->isSuperAdmin()) {
            $query->where('company_id', $user->company_id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', 'in:admin,staff,hotel'],
        ];

        if ($user->isSuperAdmin()) {
            $rules['company_id'] = ['required', 'exists:companies,id'];
            $rules['role'] = ['required', 'in:super_admin,admin,staff,hotel'];
        }

        $data = $request->validate($rules);
        $data['password'] = Hash::make($data['password']);

        if (!$user->isSuperAdmin()) {
            $data['company_id'] = $user->company_id;
        }

        $company = Company::findOrFail($data['company_id']);
        if (!$company->canAddUser()) {
            return response()->json([
                'message' => 'User limit reached for this company plan.',
                'max_users' => (int) $company->max_users,
            ], 422);
        }

        $newUser = User::create($data);

        return response()->json($newUser->load('company'), 201);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        if (!$currentUser->isSuperAdmin() && $user->company_id !== $currentUser->company_id) {
            abort(403, 'Unauthorized.');
        }

        return response()->json($user->load('company'));
    }

    public function update(Request $request, User $targetUser): JsonResponse
    {
        $currentUser = $request->user();

        if (!$currentUser->isSuperAdmin() && $targetUser->company_id !== $currentUser->company_id) {
            abort(403, 'Unauthorized.');
        }

        $rules = [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $targetUser->id],
            'password' => ['sometimes', Password::defaults()],
            'is_active' => ['sometimes', 'boolean'],
        ];

        if ($currentUser->isSuperAdmin()) {
            $rules['role'] = ['sometimes', 'in:super_admin,admin,staff,hotel'];
            $rules['company_id'] = ['sometimes', 'exists:companies,id'];
        } elseif ($currentUser->isAdmin()) {
            $rules['role'] = ['sometimes', 'in:admin,staff,hotel'];
        }

        $data = $request->validate($rules);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $targetUser->update($data);

        return response()->json($targetUser->load('company'));
    }

    public function destroy(Request $request, User $targetUser): JsonResponse
    {
        $currentUser = $request->user();

        if (!$currentUser->isSuperAdmin() && $targetUser->company_id !== $currentUser->company_id) {
            abort(403, 'Unauthorized.');
        }

        if ($targetUser->id === $currentUser->id) {
            abort(400, 'Cannot delete yourself.');
        }

        $targetUser->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }
}
