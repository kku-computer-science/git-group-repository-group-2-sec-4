<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class CpanelController extends Controller
{
    public function getCpanelStats()
    {
        // 1) กำหนดข้อมูลสำหรับเรียก API
        $cpanelUser  = 'api_statistics';
        $cpanelToken = 'MHTMBKRTJ7HP8S2OA0IXQ3VPNUMZVN2O'; 
        $cpanelHost  = 'cs040268.cpkkuhost.com/'; 
        // หรืออาจเป็นโดเมน/hostname ของเซิร์ฟเวอร์ เช่น 'server123.hosting.com'
        
        // 2) สร้าง Guzzle Client ชี้ไปที่พอร์ต cPanel (2083) (หรือ 2087 ถ้าเป็น WHM)
        $client = new Client([
            'base_uri' => "https://{$cpanelHost}:2083/",
            'verify'   => false,  // ปิด SSL verify ถ้าใบ cert ไม่ตรง (ควรเปิดใน production ถ้า cert ถูกต้อง)
        ]);

        try {
            // 3) เรียก API /execute/StatsBar/getstatsbar 
            //    ดูเอกสาร https://api.docs.cpanel.net/openapi/cpanel/operation/StatsBar_getstatsbar/
            $response = $client->get('execute/StatsBar/getstatsbar', [
                'headers' => [
                    // ใช้รูปแบบ Authorization: cpanel [user]:[token]
                    'Authorization' => "cpanel {$cpanelUser}:{$cpanelToken}"
                ]
            ]);

            // 4) แปลงผลลัพธ์เป็น Array
            $data = json_decode($response->getBody(), true);

            // 5) ดึงค่าต่าง ๆ จาก $data 
            //    (โครงสร้าง response อาจปรับเปลี่ยนตามเวอร์ชัน cPanel)
            //    ตัวอย่าง:
            $diskUsed     = $data['data']['newdiskuse']  ?? null;
            $diskLimit    = $data['data']['newdisklimit'] ?? null;
            $bwUsed       = $data['data']['newbwuse']     ?? null;
            $bwLimit      = $data['data']['newbwlimit']   ?? null;
            $fileUsage    = $data['data']['newfileuse']   ?? null; // บางเวอร์ชันจะมี 
            $fileLimit    = $data['data']['newfilelimit'] ?? null;

            // 6) ส่งข้อมูลไปแสดงใน Blade (ตัวอย่าง cpanel_stats.blade.php)
            return view('cpanel_stats', compact(
                'diskUsed', 'diskLimit',
                'bwUsed', 'bwLimit',
                'fileUsage', 'fileLimit'
            ));

        } catch (\Exception $e) {
            return "Error calling cPanel API: " . $e->getMessage();
        }
    }
}
