<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Services\Api\AuthService;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function login(Request $request)
    {
        // Validate email and password
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Call service
        $data = $this->authService->login($request->email, $request->password);

        return response()->json(
            generate_response($data, 1, false, 'Login successful')
        );
    }
}
