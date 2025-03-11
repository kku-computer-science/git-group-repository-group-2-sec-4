<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Helpers\LogHelper;
use Throwable;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    // ✅ เพิ่มฟังก์ชันตรวจสอบ static assets
    private function isStaticAsset($request)
    {
        $path = $request->path();
        return preg_match('/\.(css|js|jpg|jpeg|png|gif|svg|woff|woff2|ttf|eot|ico)$/i', $path);
    }


    /**
     * Handle and log errors before rendering.
     */
    public function render($request, Throwable $exception)
    {
        \Log::debug("Handler.php - Checking if session is started: " . (session()->isStarted() ? 'YES' : 'NO'));
        \Log::debug("Handler.php - Checking Auth::check(): " . (Auth::check() ? 'Authenticated' : 'Not Authenticated'));

        $user = Auth::user();

        if (!$user) {
            \Log::debug("Handler.php - No authenticated user found.");
        } else {
            \Log::debug("Handler.php - Authenticated user: " . json_encode([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]));
        }

        $userId = $user?->id ?? null;
        $userName = $user?->name ?? 'Unknown';
        $userEmail = $user?->email ?? 'Unknown';

        // ✅ เช็ค URL และไม่บันทึก log สำหรับ favicon.ico และไฟล์ .map
        $excludedPaths = [
            'favicon.ico',
            'images/icons/favicon.ico',
            'vendors/mdi/css/materialdesignicons.css.map',
            'maps/vertical-layout-light/style.css.map',
            'vendors/js/perfect-scrollbar.min.js.map',
            'vendors/progressbar.js/progressbar.min.js.map',
        ];
        foreach ($excludedPaths as $excludedPath) {
            if (str_contains($request->path(), $excludedPath)) {
                return parent::render($request, $exception);
            }
        }
        
        // เช็คว่ามันเป็น 404 และเป็นไฟล์ .map หรือไม่
        if ($exception instanceof HttpException && $exception->getStatusCode() == 404) {
            $requestUrl = $request->fullUrl();

            // ไม่บันทึก 404 ถ้า URL ลงท้ายด้วย .map
            if (preg_match('/\.map$/', $requestUrl)) {
                \Log::debug("Ignored 404 for .map file: " . $requestUrl);
                return response()->noContent(404);
            }
        }
        // ตรวจจับ HTTP 4xx Error ทุกประเภท
        if ($exception instanceof HttpException) {
            $status = $exception->getStatusCode();

            // ตรวจสอบว่าเป็น 4xx หรือไม่
            if ($status >= 400 && $status < 500) {
                LogHelper::logError($status, json_encode([
                    'user_id' => $userId, // ดึง User ID
                    'user_name' => $userName, // ดึงชื่อ
                    'user_email' => $userEmail, // ดึงอีเมล
                    'message' => $exception->getMessage(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'input' => $request->except(['password', 'password_confirmation'])
                ]));
            }
        }


        // ตรวจจับ Validation Error (422)
        if ($exception instanceof ValidationException) {
            LogHelper::logError(422, json_encode([
                'user_id' => $userId, // ดึง User ID
                'user_name' => $userName, // ดึงชื่อ
                'user_email' => $userEmail, // ดึงอีเมล
                'message' => 'Validation failed',
                'errors' => $exception->errors(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'input' => $request->except(['password', 'password_confirmation'])
            ]));
        }

        // ตรวจจับ Error 419 (CSRF Token Expired)
        if ($exception instanceof TokenMismatchException) {
            LogHelper::logError(419, json_encode([
                'user_id' => $userId, // ดึง User ID
                'user_name' => $userName, // ดึงชื่อ
                'user_email' => $userEmail, // ดึงอีเมล
                'message' => 'CSRF Token Mismatch',
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'input' => $request->except(['password', 'password_confirmation'])
            ]));
        }

        return parent::render($request, $exception);
    }
}
