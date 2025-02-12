<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $table = 'logs';

    // protected $fillable = [
    //     'user_id',
    //     'action',
    //     'log_level',
    //     'message',
    //     'ip_address',
    //     'related_table',
    //     'related_id'
    // ];
    protected $fillable = [
        'log_id',
        'user_id',
        'action',
        'log_level',
        'message',
        'ip_address',
        'related_table',
        'related_id',
        'created_at'
    ];

    public $timestamps = false;

    // ใช้ Relationship ดึงชื่อจาก users
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
