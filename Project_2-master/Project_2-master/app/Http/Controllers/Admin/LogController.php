<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = Log::with('user')->orderBy('created_at', 'desc');

        // ค้นหาตาม User ID
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // ค้นหาตาม User Name
        if ($request->filled('user_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('fname_en', 'LIKE', '%' . $request->user_name . '%')
                    ->orWhere('lname_en', 'LIKE', '%' . $request->user_name . '%');
            });
        }

        // ค้นหาตาม User Email
        if ($request->filled('user_email')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('email', 'LIKE', '%' . $request->user_email . '%');
            });
        }

        // ค้นหาตาม Action
        if ($request->filled('action')) {
            $query->where('action', 'LIKE', '%' . $request->action . '%');
        }

        // ค้นหาตาม Log Level
        if ($request->filled('log_level')) {
            $query->where('log_level', $request->log_level);
        }

        // ค้นหาตาม IP Address
        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'LIKE', '%' . $request->ip_address . '%');
        }

        // ค้นหาตามช่วงวันที่ (แปลงเป็น YYYY-MM-DD)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            try {
                $start_date = \Carbon\Carbon::createFromFormat('d/m/Y', $request->start_date)->format('Y-m-d');
                $end_date = \Carbon\Carbon::createFromFormat('d/m/Y', $request->end_date)->format('Y-m-d');

                $query->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            } catch (\Exception $e) {
                return back()->with('error', 'Invalid date format. Please use DD/MM/YYYY.');
            }
        }

        $logs = $query->paginate(20);

        return view('admin.logs.index', compact('logs'));
    }

    // ฟังก์ชัน Export Log เป็น CSV
    public function exportCsv()
    {
        $logs = Log::orderBy('created_at', 'desc')->get();

        $csvFileName = "logs_export_" . date('Y-m-d_His') . ".csv";
        $headers = ["Content-Type" => "text/csv", "Content-Disposition" => "attachment; filename=$csvFileName"];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ["Log ID", "User ID", "Action", "Log Level", "Message", "IP Address", "Related Table", "Related ID", "Created At"]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->log_id,
                    $log->user_id ?? 'Guest',
                    $log->action,
                    $log->log_level,
                    $log->message,
                    $log->ip_address,
                    $log->related_table,
                    $log->related_id,
                    $log->created_at
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
