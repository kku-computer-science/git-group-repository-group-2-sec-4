<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOfResearchGroup extends Model
{
    use HasFactory;

    protected $table = 'work_of_research_groups'; // กำหนดชื่อตาราง

    protected $fillable = ['role', 'user_id', 'research_group_id'];

    // เชื่อมกับตาราง research_groups
    public function researchGroup()
    {
        return $this->belongsTo(ResearchGroup::class, 'research_group_id');
    }

    // เชื่อมกับตาราง users
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
