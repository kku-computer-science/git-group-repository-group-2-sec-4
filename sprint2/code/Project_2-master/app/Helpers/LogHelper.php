<?php
namespace App\Helpers;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class LogHelper
{
    public static function log($action, $logLevel, $message = null, $relatedTable = null, $relatedId = null)
    {
        try {
            Log::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'log_level' => $logLevel,
                'message' => $message,
                'ip_address' => Request::ip(),
                'related_table' => $relatedTable,
                'related_id' => $relatedId
            ]);

            \Log::info('Log entry created for user ID: ' . Auth::id());
        } catch (\Exception $e) {
            \Log::error("Failed to insert log: " . $e->getMessage());
        }
    }
    //ฟังก์ชันใหม่สำหรับบันทึก Error Log
    public static function logError($statusCode, $message = null)
    {
        // ดึง Request URI ที่เรียกเข้ามา
        $requestUri = \Illuminate\Support\Facades\Request::getRequestUri();
        // ตรวจสอบว่าการร้องขอนั้นเป็นสำหรับ favicon หรือไม่
        if (\Illuminate\Support\Facades\Request::is('images/icons/favicon.ico') || \Illuminate\Support\Facades\Request::is('favicon.ico')) {
            return;
        }


        // ถ้า URL มีการเรียกไฟล์ .map (เช่น materialdesignicons.css.map, progressbar.min.js.map, perfect-scrollbar.min.js.map) ให้ข้ามการบันทึก log
        if (preg_match('/\.map$/', $requestUri)) {
            return;
        }
        try {
            $user = Auth::user();
            \Log::debug("LogHelper.php - Checking Auth::user(): " . json_encode($user));

            Log::create([
                'user_id' => $user?->id ?? null,
                'user_name' => $user?->name ?? 'Unknown',
                'user_email' => $user?->email ?? 'Unknown',
                'action' => "HTTP $statusCode ",
                'log_level' => 'ERROR',
                'message' => "HTTP $statusCode: " . ($message ?? 'Unknown error'),
                'ip_address' => Request::ip(),
                'related_table' => null,
                'related_id' => null
            ]);

            \Log::error("Error logged: HTTP $statusCode - " . ($message ?? 'Unknown error'));
        } catch (\Exception $e) {
            \Log::error("Failed to insert error log: " . $e->getMessage());
        }
    }
}

