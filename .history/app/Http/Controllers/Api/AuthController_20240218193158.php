<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\VerificationCode;
use App\Traits\GeneralTrait;
use Mail;
use Validator;
use JWTAuth;

class AuthController extends Controller
{
    use GeneralTrait;


    public function __construct()
    {
    }
    public function VerificationCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100|unique:users'
        ]);

        if ($validator->fails()) {
            return $this->apiResponse($data = [], $message = "", 400, $validator->errors());
        }

        $api = new ShopifyAPI();
        $customers = $api->__call('get', [
            'customers/search.json',
            ["query" => "email:" . $request->email]
        ]);
        $customer = json_decode($customers);

        if (!empty($customer->customers)) {
            return $this->apiResponse($data = [], $message = "", 400, ['email' => ['The email has already been taken']]);
        }

        $otp = rand(123456, 999999);
        VerificationCode::create([
            'email' => $request->email,
            'otp' => $otp,
            'expire_at' => Carbon::now()->addMinutes(10)
        ]);

        Mail::send('email.VerificationCode', ['otp' => $otp], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Verification Code');
        });
        return $this->apiResponse($data = [], $message = "Send Verification Code", 201, []);
    }

    // public function login(Request $request){
    // 	$validator = Validator::make($request->all(), [
    //         'phone' => 'required|min:11|max:14'
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }


    //     $client = Client::where('phone', $request->phone)->first();
    //     if($client){
    //         $verificationCode = VerificationCode::where('client_id', $client->id)->latest()->first();

    //         $now = Carbon::now();

    //         if($verificationCode && $now->isBefore($verificationCode->expire_at)){

    //             return $this->returnData('client',$client,'please check verification Code expire_at');
    //         }else{
    //             $otp=rand(123456, 999999);
    //         VerificationCode::create([
    //                 'client_id' => $client->id,
    //                 'otp' =>$otp ,
    //                 'expire_at' => Carbon::now()->addMinutes(10)
    //             ]);
    //             $data['recipient'] = "+20$request->phone";
    //             $data['message'] =  "verification Code Shifi $otp";
    //             $data['sender_id'] =  "WhySMS";
    //             $data['type'] =  "plain";
    //               //   $this->sms($data);

    //             return $this->returnData('client',$client,'send sms verification Code',);
    //         }

    //     }else{


    //         $client=  Client::create([
    //             'phone' => $request->phone
    //         ]);
    //         $otp=rand(123456, 999999);
    //         VerificationCode::create([
    //                 'client_id' => $client->id,
    //                 'otp' =>$otp ,
    //                 'expire_at' => Carbon::now()->addMinutes(10)
    //             ]);
    //             $data['recipient'] = "+20$request->phone";
    //             $data['message'] =  "verification Code Shifi $otp";
    //             $data['sender_id'] =  "WhySMS";
    //             $data['type'] =  "plain";
    //          // $this->sms($data);
    //           return $this->returnData('send sms verification Code',$client);



    //     }





    // }



    public function registerVervication(Request $request)
    {
        if ($request->force_update != 1) {
            return $this->apiResponse($data = [], $message = "", 400, ['error' => ['need update app']]);
        }
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100',
            'otp' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->apiResponse($data = [], $message = "", 400, $validator->errors());
        }



        $verificationCode   = VerificationCode::where('email', $request->email)->where('otp', $request->otp)->latest()->first();

        $now = Carbon::now();
        if (!$verificationCode) {
            return $this->apiResponse($data = [], $message = "", 400, ['activation_code' => ['Your Otp is not correct']]);
        } elseif ($verificationCode && $now->isAfter($verificationCode->expire_at)) {
            return $this->apiResponse($data = [], $message = "", 400, ['activation_code' => ['Your OTP has been expired']]);
        }


        return $this->apiResponse($data = [], $message = "User successfully otp", 201, []);
    }


    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */



    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {

        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */


    public function refresh()
    {
        return $this->createNewToken(auth('api')->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        return response()->json(auth('api')->user());
    }

    public function loginDefault(Request $request)
    {

        if ($request->force_update != 1) {
            return $this->apiResponse($data = [], $message = "", 400, ['error' => ['need update app']]);
        }


        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse($data = [], $message = "", 400, $validator->errors());
        }





        if (!$token = JWTAuth::attempt($validator->validated())) {
            $api = new ShopifyAPI();
            $customers = $api->__call('get', [
                'customers/search.json',
                ["query" => "email:" . $request->email]
            ]);
            $customer = json_decode($customers);

            if (!empty($customer->customers)) {
                return $this->apiResponse($data = [], $message = "", 400, ['email' => ['The email has already but error in password']]);
            }
            return $this->apiResponse($data = [], $message = "", 400, ['error' => [trans('main.unauthorized')]]);
        }


        return $this->createNewToken($token);
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
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 6000,
            'user' => auth()->user()
        ]);
    }



    public function country_phone_code()
    {
        $countries = Country::all();

        return $this->returnData('countries', $countries);
    }


    public function submitForgetPasswordForm(Request $request)
    {

        $api = new ShopifyAPI();
        $customers = $api->__call('get', [
            'customers/search.json',
            ["query" => "email:" . $request->email]
        ]);
        $customer = json_decode($customers);

        if (!empty($customer->customers[0]->email)) {
            $usernot = User::where('email', $customer->customers[0]->email)->first();
            if (empty($usernot)) {
                $user = new User();
                $user->email = $customer->customers[0]->email;
                $user->name = $customer->customers[0]->first_name . ' ' . $customer->customers[0]->last_name;
                $user->shopify_customer_id = $customer->customers[0]->id;

                $user->save();
            }
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse($data = [], $message = "", 400, $validator->errors());
        }

        $otp = rand(123456, 999999);
        VerificationCode::create([
            'email' => $request->email,
            'otp' => $otp,
            'expire_at' => Carbon::now()->addMinutes(10)
        ]);

        Mail::send('email.VerificationCode', ['otp' => $otp], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Verification Code');
        });

        return $this->apiResponse($data = [], $message = "Send Verification Code", 201, []);
    }


    public function deleteCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = User::findOrFail($request->id);



        $api = new ShopifyAPI();

        $cst = $api->__call('delete', ['customers/' . $user->shopify_customer_id . '.json']);
        $cst = json_decode($cst);
        if (empty($cst->errors)) {
            $user = User::destroy($user->id);
            return response()->json([
                'message' => 'User successfully deleted',
                'user' => $cst
            ], 201);
        } else {
            return $this->apiResponse($data = [], $message = "", 400, $cst);
        }
    }

    public function termsSale(Request $request)
    {
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100|unique:users',
            'name' => 'required|string|between:2,200',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = new User();
        $user->email = $request->email;


        if (!empty($request->name)) {
            $user->name = $request->name;
        }

        if ($request->has('password') && !empty($request->password)) {
            $user->password = bcrypt($request->password);
        }
        if ($request->hasFile('image')) {
            $user->image = $this->saveImage($request->image, "/user");
        }

        $user->save();
        $api = new ShopifyAPI();
        $names = explode(" ", $request->name);
        $customer = $api->__call(
            'post',
            [
                'customers.json',  [
                    'customer' => [
                        'first_name' => @$names[0],
                        'last_name' => @$names[1],
                        'email' => $user->email,
                        'phone' => '',
                        'verified_email' => true,
                        'password' => $request->password,
                        'password_confirmation' => $request->password,
                        'send_email_welcome' => false
                    ]
                ]
            ]
        );
        if ($customer) {
            $customer = json_decode($customer);
            $user->shopify_customer_id = $customer->customer->id;
        }

        $user->save();

        return response()->json([
            'message' => 'User successfully register',
            'client' => $user
        ], 201);
    }


    public static function saveImage($file, $folder = '/')
    {
        $extension = $file->getClientOriginalExtension(); // getting image extension
        $fileName = time() . '' . rand(11111, 99999) . '.' . $extension; // renameing image
        $dest = public_path('/uploads' . $folder);
        $file->move($dest, $fileName);
        return '/uploads' . $folder . '/' . $fileName;
    }


    public function submitResetPasswordForm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->apiResponse($data = [], $message = "", 400, $validator->errors());
        }


        $user = User::where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);
        $user = User::where('email', $request->email)
            ->first();
        //   dd($user);
        $api = new ShopifyAPI();
        $customer = $api->__call(
            'post',
            [
                'customers/' . $user->shopify_customer_id . '.json',  [
                    'customer' => [
                        'password' => $request->password,
                        'password_confirmation' => $request->password
                    ]
                ]
            ]
        );

        return $this->apiResponse($data = [], $message = "Your password has been changed!", 201, []);
    }
}
