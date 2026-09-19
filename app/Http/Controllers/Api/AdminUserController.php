<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function __construct(
        private UserRepository $users,
    ) {}

    public function index(): JsonResponse
    {
        $users = $this->users->listAll();

        return response()->json(
            array_map(fn ($u) => $u->toArray(), $users),
        );
    }

    public function show(string $id): JsonResponse
    {
        $user = $this->users->findById($id);

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        return response()->json($user->toArray());
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'fullName'      => 'sometimes|string|max:255',
            'role'          => 'sometimes|in:USER,ADMIN,COACH',
            'status'        => 'sometimes|in:ACTIVE,INACTIVE,SUSPENDED',
            'studentNumber' => 'sometimes|string|max:20',
        ]);

        $this->users->updateUser($id, $validated);

        $user = $this->users->findById($id);

        return response()->json($user?->toArray());
    }

    public function destroy(string $id): JsonResponse
    {
        $user = $this->users->findById($id);

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $this->users->deleteUser($id);

        return response()->json(['message' => 'User deleted successfully.']);
    }
}