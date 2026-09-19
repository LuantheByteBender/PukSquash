<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Services\FirebaseAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Kreait\Firebase\Exception\Auth\UserNotFound;

class AdminUserController extends Controller
{
    public function __construct(
        private UserRepository $users,
        private FirebaseAuthService $firebase,
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
            'role'          => 'sometimes|in:USER,ADMIN,COACH',
            'status'        => 'sometimes|in:ACTIVE,INACTIVE,SUSPENDED',
        ]);

        if (! array_key_exists('role', $validated) && ! array_key_exists('status', $validated)) {
            return response()->json(['message' => 'No changes provided.'], 422);
        }

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

        try {
            $this->firebase->deleteUser($user->firebaseUid);
        } catch (UserNotFound $exception) {
            // Already removed from Firebase Authentication.
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => 'Unable to delete user from Firebase Authentication.',
            ], 500);
        }

        $this->users->deleteUser($id);

        return response()->json(['message' => 'User deleted successfully.']);
    }
}