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

        // ✅ ค้นหาตาม User ID
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // ✅ ค้นหาตาม User Name
        if ($request->filled('user_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('fname_en', 'LIKE', '%' . $request->user_name . '%')
                    ->orWhere('lname_en', 'LIKE', '%' . $request->user_name . '%');
            });
        }

        // ✅ ค้นหาตาม User Email
        if ($request->filled('user_email')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('email', 'LIKE', '%' . $request->user_email . '%');
            });
        }

        // ✅ ค้นหาตาม Action
        if ($request->filled('action')) {
            $query->where('action', 'LIKE', '%' . $request->action . '%');
        }

        // ✅ ค้นหาตาม Log Level
        if ($request->filled('log_level')) {
            $query->where('log_level', strtoupper($request->log_level));
        }

        // ✅ ค้นหาตาม IP Address
        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'LIKE', '%' . $request->ip_address . '%');
        }

        // ✅ ค้นหาตาม Related Table
        if ($request->filled('related_table')) {
            $query->where('related_table', 'LIKE', '%' . $request->related_table . '%');
        }

        // ✅ ค้นหาตาม Related ID
        if ($request->filled('related_id')) {
            $query->where('related_id', $request->related_id);
        }

        // ✅ ค้นหาตามช่วงวันที่ (แปลงเป็น YYYY-MM-DD)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            try {
                $start_date = Carbon::createFromFormat('d/m/Y', $request->start_date)->startOfDay();
                $end_date = Carbon::createFromFormat('d/m/Y', $request->end_date)->endOfDay();
                $query->whereBetween('created_at', [$start_date, $end_date]);
            } catch (\Exception $e) {
                return back()->withErrors(['error' => 'Invalid date format. Please use DD/MM/YYYY.']);
            }
        }

        $logs = $query->paginate(20);

        return view('admin.logs.index', compact('logs'));
    }

    // ฟังก์ชัน Export Log เป็น CSV
    public function exportCsv()
    {
        $logs = Log::select([
            'log_id',
            'user_id',
            'action',
            'log_level',
            'message',
            'ip_address',
            'related_table',
            'related_id',
            'created_at'
        ])->orderBy('created_at', 'desc')->get();

        $csvFileName = "logs_export_" . date('Y-m-d_His') . ".csv";
        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName"
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // ✅ กำหนด Header ของ CSV ให้ตรงกับฟิลด์ในฐานข้อมูล
            fputcsv($file, ["Log ID", "User ID", "User Name", "User Email", "Action", "Log Level", "Message", "IP Address", "Related Table", "Related ID", "Created At"]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->log_id ?? '-',
                    $log->user_id ?? 'Guest',
                    $log->user ? $log->user->fname_en . ' ' . $log->user->lname_en : 'Unknown', // ✅ เพิ่มชื่อ User
                    $log->user ? $log->user->email : 'Unknown', // ✅ เพิ่ม Email ของ User
                    $log->action ?? '-',
                    $log->log_level ?? '-',
                    $log->message ?? '-',
                    $log->ip_address ?? '-',
                    $log->related_table ?? '-',
                    $log->related_id ?? '-',
                    $log->created_at ?? '-'
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

}
