<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Info(
 *     title="SafeSHIPS API",
 *     version="1.0.0",
 *     description="API for SafeSHIPS application",
 * )
 */

class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="Register a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="role", type="string", enum={"user", "frontliner", "non_user"}, example="user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Registration successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Registration successful"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validasi gagal"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:user,frontliner,non_user', // Hanya role tertentu yang bisa register
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        $token = $user->createToken('auth_token', ['*'], now()->addDays(7))->plainTextToken;

        return response()->json([
            'message' => 'Berhasil Registrasi',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/guest-login",
     *     tags={"Auth"},
     *     summary="Login as guest using FCM token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="fcm_token", type="string", example="sample_fcm_token_123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Guest login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Guest login successful"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validasi gagal"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function guestLogin(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $email = $request->fcm_token . '@mail.com';
        $user = User::where('email', $email)->first();

        if ($user) {
            // User exists, attempt login
            if (!Auth::attempt(['email' => $email, 'password' => $request->fcm_token])) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }
        } else {
            // Register new guest user
            $user = User::create([
                'name' => 'Guest_' . substr($request->fcm_token, 0, 8),
                'email' => $email,
                'password' => Hash::make($request->fcm_token),
                'role' => 'non_user',
                'fcm_token' => $request->fcm_token,
            ]);
        }

        // Update FCM token for the user
        User::where('fcm_token', $request->fcm_token)
            ->where('id', '!=', $user->id)
            ->update(['fcm_token' => null]);

        $user->update(['fcm_token' => $request->fcm_token]);

        $token = $user->createToken('auth_token', ['*'], now()->addDays(7))->plainTextToken;

        return response()->json([
            'message' => 'Guest login successful',
            'token' => $token,
            'user' => $user
        ], 200);
    }


    /**
     * @OA\Get(
     *     path="/api/user",
     *     tags={"Login"},
     *     summary="Get authenticated user by token",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pengguna berhasil diambil"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="role", type="string", example="user")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Gagal mengambil pengguna"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function getUserByToken(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated',
                ], 401);
            }

            return response()->json([
                'message' => 'Pengguna berhasil diambil',
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil pengguna',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Login a user with token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'fcm_token' => 'nullable|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        // Hapus fcm_token dari user lain yang memakai token yang sama
        if ($request->has('fcm_token') && $request->fcm_token) {
            \App\Models\User::where('fcm_token', $request->fcm_token)
                ->where('id', '!=', $user->id)
                ->update(['fcm_token' => null]);

            // Update user yang sedang login dengan fcm_token baru
            $user->update(['fcm_token' => $request->fcm_token]);
        }

        $token = $user->createToken('auth_token', ['*'], now()->addDays(7))->plainTextToken;

        return response()->json([
            'message' => 'Login Sukses',
            'token' => $token,
            'user' => $user
        ]);
    }



    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Logout a user",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        // Hapus fcm_token user saat logout
        $user->update(['fcm_token' => null]);

        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
