<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Info(
 *     title="API Documentation",
 *     version="1.0.0",
 *     description="API documentation for user authentication and profile management",
 *
 *     @OA\Contact(
 *         email="support@example.com"
 *     ),
 *
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class ApiController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="phone_number", type="string", example="123456789")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation errors"
     *     )
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|confirmed',
        ]);
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone_number' => $request->phone_number,
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'User registered successfully',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Log in a user",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Logged in successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="token", type="string", example="your-access-token")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Invalid credentials"
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! empty($user)) {
            if (Hash::check($request->password, $user->password)) {

                $token = $user->createToken('myToken')->accessToken;

                return response()->json([
                    'status' => '200',
                    'message' => 'Logged in successfully',
                    'token' => $token,
                ]);

            } else {
                return response()->json([
                    'status' => 422,
                    'message' => 'Passwords dint match',
                ]);
            }
        } else {
            return response()->json([
                'status' => 422,
                'message' => 'User not found',
            ]);
        }

    }

    /**
     * @OA\Get(
     *     path="/api/profile",
     *     summary="Get user profile",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="User profile fetched successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com")
     *         )
     *     )
     * )
     */
    public function profile()
    {
        $user = Auth::user();

        return response()->json([
            'status' => '200',
            'message' => 'Logged in successfully',
            'user' => $user,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/refresh-token",
     *     summary="Refresh user token",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=201,
     *         description="Token refreshed successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="token", type="string", example="new-access-token")
     *         )
     *     )
     * )
     */
    public function refreshToken()
    {
        $user = request()->user();
        $token = $user->createToken('newToken');

        $refreshToken = $token->accessToken;

        return response()->json([
            'status' => 201,
            'message' => 'Refresh token',
            'token' => $refreshToken,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Log out a user",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        request()->user()->tokens()->delete();

        return response()->json([
            'status' => 200,
            'message' => 'User logged out',
        ]);
    }
}
