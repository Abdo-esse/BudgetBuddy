<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Endpoints pour l'authentification"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Créer un nouvel utilisateur",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully."),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string", example="token_value_here")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     * )
     */
    public function register(AuthRequest $request): JsonResponse
    {
         $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Connexion utilisateur",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login successful."),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string", example="token_value_here")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Identifiants invalides"),
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Déconnexion de l'utilisateur",
     *     tags={"Auth"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully logged out.")
     *         )
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out.'
        ], 200);
    }

   /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Récupérer les informations de l'utilisateur connecté",
     *     tags={"Auth"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function user()
    {
        return Auth::user();
    }
}
