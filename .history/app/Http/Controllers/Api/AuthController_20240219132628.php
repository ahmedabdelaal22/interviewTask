<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Traits\GeneralTrait;
use App\Http\Resources\UserResource;
use App\Service\UserService;
class AuthController extends Controller
{
    use GeneralTrait;
    private $service;

    public function __construct(UserService $Service)
    {
        $this->service = $Service;
    }
    public function register(RegisterRequest $request)
    {
        $data = $request->only('name', 'email', 'password');
        $data = $this->service->store($request);
        if ($data) {
            return $this->createResponse($data,'User created successfully' );
        }
        return $this->unKnowError();
    }
    public function authenticate(LoginRequest $request)
    {

        $credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return $this->unauthorizedResponse('Login credentials are invalid.');
            }
        } catch (JWTException $e) {
            return $this->unauthorizedResponse('Could not create token.');
        }
        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        try {
            JWTAuth::invalidate($request->token);

            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_user(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        $user = JWTAuth::authenticate($request->token);
        return $this->apiResponse(new UserResource($user));
    }
}
