<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();
        if (! $user || ! $user->isActive() || ! Hash::check($data['password'], $user->password)) {
            return ApiResponse::fail('Invalid credentials.', [], 401);
        }

        $token = $user->createToken($data['device_name'] ?? 'api')->plainTextToken;

        return ApiResponse::ok([
            'token' => $token,
            'user' => $this->profile($user),
        ]);
    }

    public function me(Request $request)
    {
        return ApiResponse::ok($this->profile($request->user()));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return ApiResponse::ok(null, 'Operation successful');
    }

    private function profile(User $user): array
    {
        $user->loadMissing('roles');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'locale' => $user->locale,
            'school_id' => $user->school_id,
            'roles' => $user->roles->pluck('slug'),
        ];
    }
}
