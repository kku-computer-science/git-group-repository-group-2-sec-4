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
}

