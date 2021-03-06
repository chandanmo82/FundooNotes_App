<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Exceptions\FundooNoteException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/**
 * @since 2-jan-2022
 * 
 * This is the main controller that is responsible for user registration,login,user-profile 
 * refresh and logout API's.
 */
class UserController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'addProfileImage']]);
    }
    /**
     * @OA\Post(
     *   path="/api/auth/register",
     *   summary="register",
     *   description="register the user for login",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"firstname","lastname","email", "password", "confirm_password"},
     *               @OA\Property(property="firstname", type="string"),
     *               @OA\Property(property="lastname", type="string"),
     *               @OA\Property(property="email", type="string"),
     *               @OA\Property(property="password", type="password"),
     *               @OA\Property(property="confirm_password", type="password")
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="User successfully registered"),
     *   @OA\Response(response=401, description="The email has already been taken"),
     * )


     * It takes a POST request and requires fields for the user to register
     * and validates them if it is validated,creates those fields including
     * values in DB and returns success response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|between:2,20',
            'lastname' => 'required|string|between:2,20',
            'email' => 'required|string|email|max:100',
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        try {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                throw new FundooNoteException("The email has already been taken", 401);
            }

            $user = User::create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);
            $value = Cache::remember('users', 3600, function () {
                return DB::table('users')->get();
            });
        } catch (FundooNoteException $e) {

            return response()->json(['message' => $e->message(), 'status' => $e->statusCode()]);
        }

        return response()->json([
            'message' => 'User successfully registered',
        ], 201);
    }
    /**
     * @OA\Post(
     *   path="/api/auth/login",
     *   summary="login",
     *   description=" login ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email", "password"},
     *               @OA\Property(property="email", type="string"),
     *               @OA\Property(property="password", type="password"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=200, description="Login successfull"),
     *   @OA\Response(response=401, description="we can not find the user with that e-mail address You need to register first"),

     * )
     * Takes the POST request and user credentials checks if it correct,
     * if so, returns JWT access token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $value = Cache::remember('users', 3600, function () {
            return DB::table('users')->get();
        });

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            Log::error('User failed to login.', ['id' => $request->email]);
            return response()->json([
                'message' => 'we can not find the user with that e-mail address You need to register first'
            ], 401);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        Log::info('Login Success : ' . 'Email Id :' . $request->email);
        return response()->json([
            'access_token' => $token,
            'message' => 'Login successfull'
        ], 200);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Post(
     *   path="/api/auth/logout",
     *   summary="logout",
     *   description=" logout ",
     *  @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"token",},
     *               @OA\Property(property="token", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="User successfully signed out"),
     * )
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'User successfully signed out'], 201);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
        ]);
    }
    /**
     * This function will take image
     * as input and save in AWS S3 
     * and will save link in database
     * @return \Illuminate\Http\JsonResponse
     */

    public function addProfileImage(Request $request)
    {
        $request->validate([

            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

        ]);
        $user = Auth::user();

        $user = User::where('email', $user->email)->first();
        if ($user) {
            $imageName = time() . '.' . $request->image->extension();

            $path = Storage::disk('s3')->put('images', $request->image);
            $url = env('AWS_URL') . $path;
            $temp = User::where('email', $user->email)
                ->update(['profilepic' => $url]);
            return response()->json(['message' => 'Profilepic Successsfully Added', 'URL' => $url], 201);
            
        } else {
            return response()->json(['message' => 'We cannot find a user'], 400);
        }
    }
}
