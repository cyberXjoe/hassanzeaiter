<?php

namespace App\Http\Services\Api;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\AuthenticationException;

class AuthService
{
    /**
     * Login user and return API token
     */
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        // Create token
        $accessToken = $user->createToken('access-token', ['access'])->plainTextToken;
        $refreshToken = $user->createToken('refresh-token', ['refresh'], now()->addDays(30))->plainTextToken;

        return [
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'   => 'Bearer',
        ];        
    }
}
