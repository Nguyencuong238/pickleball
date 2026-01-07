<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name'      => 'required',
            'email'     => 'required|email|unique:users,email',
            'phone'     => 'nullable|regex:/^\d{10,11}$/',
            'password'  => 'required|min:6|confirmed',
            'role_type' => 'required|in:user,court_owner',
        ], [
            'name.required' => 'Vui lòng nhập tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.unique' => 'Email này đã được sử dụng. Vui lòng chọn email khác.',
            'phone.regex' => 'Số điện thoại phải gồm 10 hoặc 11 chữ số.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Mật khẩu không khớp.',
            'role_type.required' => 'Vui lòng chọn loại tài khoản.',
            'terms.required' => 'Bạn phải chấp nhận Điều khoản dịch vụ.'
        ]);

        if($validation->fails())
        {
            return response()->json([
                'success' => false,
                'message' => $validation->errors()->first()
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role_type' => $request->role_type,
            'status' => 'approved',
            'elo_rating' => 0,
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký thành công!',
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email'     => 'required',
            'password'  => 'required',
        ],[
            'email.required' => 'Vui lòng nhập email.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        if($validation->fails())
        {
            return response()->json([
                'success' => false,
                'message' => $validation->errors()->first()
            ]);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không chính xác.'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công!',
            'user' => auth('api')->user(),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ], 200);
    }

    /**
     * Refresh token
     */
    public function refreshToken(Request $request)
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Làm mới token thành công!',
                'access_token' => $newToken,
                'token_type' => 'Bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token không hợp lệ hoặc đã hết hạn.',
            ], 401);
        }
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => new UserResource(auth('api')->user()),
        ], 200);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        auth('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful',
        ], 200);
    }
}
