<?php

namespace App\Http\Controllers;

use App\Models\Educaton;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Log;
use App\Helpers\LogHelper;
use Illuminate\Support\Carbon;
class ProfileuserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');

        // ดึงจำนวน Logs ทั้งหมด
        $logsCount = Log::count();

        // ดึงจำนวน Logs แยกประเภท (Error, Warning, Info)
        $errorLogsCount = Log::where('log_level', 'ERROR')->count();
        $warningLogsCount = Log::where('log_level', 'WARNING')->count();
        $infoLogsCount = Log::where('log_level', 'INFO')->count();

        // รับค่าช่วงเวลาจาก Request (ค่าเริ่มต้น: "now")
        $timeRange = $request->input('time_range', 'now');

        // แปลงช่วงเวลาเป็น timestamp
        switch ($timeRange) {
            case '2h':
                $startTime = Carbon::now()->subHours(2);
                break;
            case '24h':
                $startTime = Carbon::now()->subHours(24);
                break;
            case '7d':
                $startTime = Carbon::now()->subDays(7);
                break;
            case '30d':
                $startTime = Carbon::now()->subDays(30);
                break;
            default:
                $startTime = Carbon::now()->subHours(1); // Default: 1 ชั่วโมงล่าสุด
        }

        // ดึง Top 5 Logs ที่เกิดซ้ำมากที่สุดในช่วงเวลาที่เลือก
        $topLogs = Log::where('created_at', '>=', $startTime)
            ->selectRaw('action, log_level, COUNT(*) as count, MAX(created_at) as last_occurrence')
            ->groupBy('action', 'log_level')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return view('dashboards.users.index', compact(
            'logsCount',
            'isAdmin',
            'errorLogsCount',
            'warningLogsCount',
            'infoLogsCount',
            'topLogs',
            'timeRange'
        ));
    }

    function profile()
    {
        return view('dashboards.users.profile');
    }
    function settings()
    {
        return view('dashboards.users.settings');
    }

    function updateInfo(Request $request)
    {

        $user = Auth::user();
        $oldData = $user->toArray(); // เก็บข้อมูลเก่าก่อนอัปเดต
        $user->update($request->all());
        // บันทึก Log การอัปเดตโปรไฟล์
        LogHelper::log(
            'User Updated Profile',
            'INFO',
            'User ' . $user->email . ' updated their profile.',
            'users',
            $user->id
        );

        $validator = Validator::make($request->all(), [
            'fname_en' => 'required',
            'lname_en' => 'required',
            'fname_th' => 'required',
            'lname_th' => 'required',
            'email' => 'required|email|unique:users,email,' . Auth::user()->id,

        ]);

        if (!$validator->passes()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        } else {
            $id = Auth::user()->id;

            if ($request->title_name_en == "Mr.") {
                $title_name_th = 'นาย';
            }
            if ($request->title_name_en == "Miss") {
                $title_name_th = 'นางสาว';
            }
            if ($request->title_name_en == "Mrs.") {
                $title_name_th = 'นาง';
            }
            // $pos_en='';
            // $pos_th='';
            // $doctoral = '';
            $pos_eng = '';
            $pos_thai = '';
            if (Auth::user()->hasRole('admin') or Auth::user()->hasRole('student')) {
                $request->academic_ranks_en = null;
                $request->academic_ranks_th = null;
                $pos_eng = null;
                $pos_thai = null;
                $doctoral = null;
            } else {
                if ($request->academic_ranks_en == "Professor") {
                    $pos_en = 'Prof.';
                    $pos_th = 'ศ.';
                }
                if ($request->academic_ranks_en == "Associate Professo") {
                    $pos_en = 'Assoc. Prof.';
                    $pos_th = 'รศ.';
                }
                if ($request->academic_ranks_en == "Assistant Professor") {
                    $pos_en = 'Asst. Prof.';
                    $pos_th = 'ผศ.';
                }
                if ($request->academic_ranks_en == "Lecturer") {
                    $pos_en = 'Lecturer';
                    $pos_th = 'อ.';
                }
                if ($request->has('pos')) {
                    $pos_eng = $pos_en;
                    $pos_thai = $pos_th;
                    //$doctoral = null ;
                } else {
                    if ($pos_en == "Lecturer") {
                        $pos_eng = $pos_en;
                        $pos_thai = $pos_th . 'ดร.';
                        $doctoral = 'Ph.D.';
                    } else {
                        $pos_eng = $pos_en . ' Dr.';
                        $pos_thai = $pos_th . 'ดร.';
                        $doctoral = 'Ph.D.';
                    }
                }
            }
            $query = User::find($id)->update([
                'fname_en' => $request->fname_en,
                'lname_en' => $request->lname_en,
                'fname_th' => $request->fname_th,
                'lname_th' => $request->lname_th,
                'email' => $request->email,
                'academic_ranks_en' => $request->academic_ranks_en,
                'academic_ranks_th' => $request->academic_ranks_th,
                'position_en' => $pos_eng,
                'position_th' => $pos_thai,
                'title_name_en' => $request->title_name_en,
                'title_name_th' => $title_name_th,
                'doctoral_degree' => $doctoral,

            ]);

            if (!$query) {
                return response()->json(['status' => 0, 'msg' => 'Something went wrong.']);
            } else {
                return response()->json(['status' => 1, 'msg' => 'success']);
            }
        }
    }

    public function updatePicture(Request $request)
    {
        $path = 'images/imag_user/';
        $file = $request->file('admin_image');
        $new_name = 'UIMG_' . date('Ymd') . uniqid() . '.jpg';

        // อัปโหลดรูปภาพใหม่
        $upload = $file->move(public_path($path), $new_name);

        if (!$upload) {
            return response()->json(['status' => 0, 'msg' => 'Something went wrong, upload new picture failed.']);
        } else {
            // ดึงรูปเดิมของผู้ใช้
            $user = User::find(Auth::user()->id);
            $oldPicture = $user->picture;

            // ลบรูปเดิมถ้ามีอยู่
            if ($oldPicture && \File::exists(public_path($path . $oldPicture))) {
                \File::delete(public_path($path . $oldPicture));
            }

            // อัปเดตรูปภาพใหม่ในฐานข้อมูล
            $update = $user->update(['picture' => $new_name]);

            if (!$update) {
                return response()->json(['status' => 0, 'msg' => 'Something went wrong, updating picture in db failed.']);
            } else {
                // ✅ บันทึก Log ว่าผู้ใช้เปลี่ยนรูปโปรไฟล์
                LogHelper::log(
                    'User Updated Profile Picture',
                    'INFO',
                    'User ' . $user->email . ' updated their profile picture.',
                    'users',
                    $user->id
                );

                return response()->json(['status' => 1, 'msg' => 'Your profile picture has been updated successfully']);
            }
        }
    }


    function changePassword(Request $request)
    {

        $user = Auth::user();
        $user->password = bcrypt($request->new_password);
        $user->save();

        // บันทึก Log การเปลี่ยนรหัสผ่าน
        LogHelper::log(
            'User Changed Password',
            'WARNING',
            'User ' . $user->email . ' changed their password.',
            'users',
            $user->id
        );
        //Validate form
        $validator = \Validator::make($request->all(), [
            'oldpassword' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!\Hash::check($value, Auth::user()->password)) {
                        return $fail(__('The current password is incorrect'));
                    }
                },
                'min:8',
                'max:30'
            ],
            'newpassword' => 'required|min:8|max:30',
            'cnewpassword' => 'required|same:newpassword'
        ], [
            'oldpassword.required' => 'Enter your current password',
            'oldpassword.min' => 'Old password must have atleast 8 characters',
            'oldpassword.max' => 'Old password must not be greater than 30 characters',
            'newpassword.required' => 'Enter new password',
            'newpassword.min' => 'New password must have atleast 8 characters',
            'newpassword.max' => 'New password must not be greater than 30 characters',
            'cnewpassword.required' => 'ReEnter your new password',
            'cnewpassword.same' => 'New password and Confirm new password must match'
        ]);

        if (!$validator->passes()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        } else {

            $update = User::find(Auth::user()->id)->update(['password' => \Hash::make($request->newpassword)]);

            if (!$update) {
                return response()->json(['status' => 0, 'msg' => 'Something went wrong, Failed to update password in db']);
            } else {
                return response()->json(['status' => 1, 'msg' => 'Your password has been changed successfully']);
            }
        }
    }
}
