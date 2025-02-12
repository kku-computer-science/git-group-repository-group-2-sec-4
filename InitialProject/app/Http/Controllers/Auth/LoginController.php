<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;
use App\Helpers\LogHelper;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;
    use ThrottlesLogins;
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;
    protected $maxAttempts = 10; // Default is 5
    protected $decayMinutes = 5; // Default is 1  //define 5 minute

    /**
     * Create a new controller instance.
     *
     * @return void
     */


    public function __construct()
    {

        $this->middleware(['guest'])->except('logout');
    }

    public function username()
    {
        return 'email';
    }

    public function logout(Request $request)
    {
        try {
            $user = Auth::user();
            //บันทึก log
            LogHelper::log(
                'User Logout',
                'INFO',
                'User ' . $user->email . ' logged out successfully.',
                'users',
                $user->id
            );

            $request->session()->flush();
            $request->session()->regenerate();
            Auth::logout();
            return redirect('/login');

        } catch (\Exception $e) {
            LogHelper::log(
                'Logout Error',
                'ERROR',
                'An error occurred during logout: ' . $e->getMessage(),
                'users'
            );

            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred while logging out.']);
        }



    }

    protected function redirectTo()
    {
        if (Auth::user()->hasRole('admin')) {
            return route('dashboard');
        } elseif (Auth::user()->hasRole('staff')) {
            return route('dashboard');
        } elseif (Auth::user()->hasRole('teacher')) {
            return route('dashboard');
        } elseif (Auth::user()->hasRole('student')) {
            return route('dashboard');
            //return view('home');
        }
    }

    public function login(Request $request)
    {
        try {
            // ✅ เช็คว่า request ส่งค่ามาจริงไหม
            LogHelper::log(
                'Login Attempt',
                'INFO',
                'Login attempt by: ' . $request->username,
                'users'
            );

            // ✅ ตรวจสอบ Validation
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                LogHelper::log(
                    'Login Validation Failed',
                    'ERROR',
                    'User attempted to login but failed validation. Errors: ' . json_encode($validator->errors()),
                    'users'
                );

                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            // ✅ ตรวจสอบว่า Username เป็น Email หรือไม่
            $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            // ✅ ตรวจสอบ ReCAPTCHA (ถ้ามี)
            $response = request('recaptcha');
            if (!empty($response) && !$this->checkValidGoogleRecaptchaV3($response)) {
                LogHelper::log(
                    'Login Failed - ReCAPTCHA',
                    'WARNING',
                    'User ' . $request->username . ' failed login due to invalid ReCAPTCHA.',
                    'users'
                );

                return redirect()->back()->withErrors(['error' => 'Invalid ReCAPTCHA verification.'])->withInput();
            }

            // ✅ ตรวจสอบการล็อกจากการพยายามเข้าสู่ระบบผิดพลาด
            if ($this->hasTooManyLoginAttempts($request)) {
                $this->fireLockoutEvent($request);

                LogHelper::log(
                    'User Locked Out',
                    'WARNING',
                    'User ' . $request->username . ' is temporarily locked out due to too many failed login attempts.',
                    'users'
                );

                return $this->sendLockoutResponse($request);
            }

            // ✅ ลองเข้าสู่ระบบ
            if (Auth::attempt([$fieldType => $request->username, 'password' => $request->password])) {
                $user = Auth::user();

                LogHelper::log(
                    'User Logged In',
                    'INFO',
                    'User ' . $user->email . ' logged in successfully.',
                    'users',
                    $user->id
                );

                return redirect()->route('dashboard');
            } else {
                $this->incrementLoginAttempts($request);

                LogHelper::log(
                    'Failed Login Attempt',
                    'WARNING',
                    'Failed login attempt for username: ' . $request->username,
                    'users'
                );

                return redirect()->back()
                    ->withErrors(['error' => 'Login Failed: Your username or password is incorrect.'])
                    ->withInput();
            }
        } catch (\Exception $e) {
            LogHelper::log(
                'Login Error',
                'ERROR',
                'An error occurred during login: ' . $e->getMessage(),
                'users'
            );

            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred while logging in.']);
        }
    }
    public function checkValidGoogleRecaptchaV3($response)
    {
        $url = "https://www.google.com/recaptcha/api/siteverify";

        $data = [
            'secret' => "6Ldpye4ZAAAAAKwmjpgup8vWWRwzL9Sgx8mE782u",
            'response' => $response
        ];

        $options = [
            'http' => [
                'header' => 'Content-Type: application/x-www-form-urlencoded\r\n',
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];


        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $resultJson = json_decode($result);

        return $resultJson->success;
    }
    protected function authenticated(Request $request, $user)
    {
        LogHelper::log(
            'User Login',
            'INFO',
            'User ' . $user->email . ' logged in successfully.',
            'users',
            $user->id
        );
    }
}
