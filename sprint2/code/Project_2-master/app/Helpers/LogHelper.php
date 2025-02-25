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
        try {
            Log::create([
                'user_id' => Auth::id(),
                'action' => 'error',
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

