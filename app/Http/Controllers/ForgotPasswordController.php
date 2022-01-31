<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Http\Requests\SendEmailRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ForgotPasswordController extends Controller
{
    //
    /**
     * This API Takes the request which is the email id and validates it and check where that email id 
     * is present in DB or not if it is not,it returns failure with the appropriate response code and 
     * checks for password reset model once the email is valid and by creating an object of the 
     * sendEmail function which is there in App\Http\Requests\SendEmailRequest and calling the function
     * by passing args and successfully sending the password reset link to the specified email id.
     * 
     * @return success reponse about reset link.
     */
    /**
     * @OA\Post(
     *   path="/api/auth/forgotpassword",
     *   summary="forgotpassword",
     *   description="Send Mail to the respectice mail id for forget password link",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email"},
     *               @OA\Property(property="email", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=200, description="we have mailed your password reset link to respective E-mail"),
     *   @OA\Response(response=404, description="we can not find a user with that email address"),
     * )
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100|unique:users',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'we can not find a user with that email address'], 404);
        }
        $token = Auth::fromUser($user);

        if ($user) {
            $sendEmail = new SendEmailRequest();
            $sendEmail->sendEmail($user->email, $token);
        }

        Log::info('Forgot PassWord Link : ' . 'Email Id :' . $request->email);
        return response()->json(['message' => 'password reset link genereted in mail'], 200);
    }

    /**
     * This API Takes the request which has new password and confirm password and validates both of them
     * if validation fails returns failure resonse and if it passes it checks with DB whether the token 
     * is there or not if not returns a failure response and checks the user email also if everything is 
     * good resets the password successfully.
     * 
     * 
     */
    /**
     * @OA\Post(
     *   path="/api/auth/resetpassword",
     *   summary="resetpassword",
     *   description="reset your password",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={ "new_password", "confirm_password"},
     *               @OA\Property(property="new_password", type="password"),
     *               @OA\Property(property="confirm_password", type="password"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=200, description="we have mailed your password reset link to respective E-mail"),
     *   @OA\Response(response=404, description="we can not find a user with that email address"),
     *   security = {
     * {
     * "Bearer" : {}}}
     * )
     */
    public function resetPassword(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'new_password' => 'min:6|required|',
            'confirm_password' => 'required|same:new_password'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => "Password doesn't match"
            ], 400);
        }
        $user = Auth::user();

        $user = User::where('email', $user->email)->first();

        if (!$user) {
            Log::error('Email not found.', ['id' => $request->email]);
            return response()->json([
                'message' => "we can't find the user with that e-mail address"
            ], 400);
        } else {
            $user->password = bcrypt($request->new_password);
            $user->save();
            Log::info('Reset Successful : ' . 'Email Id :' . $request->email);

            return response()->json([
                'status' => 201,
                'message' => 'Password reset successfull!'
            ], 201);
        }
    }
}
