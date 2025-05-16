<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffView extends Model
{
    use HasFactory;

    protected $table = 'staff_view';
    
    protected $fillable = [
        'id',
        'staff_person_id',
        'staff_sid',
        'staff_name',
        'staff_sex',
        'staff_username',
        'staff_curr_class_num',
        'staff_status',
        'staff_password',
        'isAuthed',
        'staff_title',
        'staff_kind',
        'staff_tutor',
        'update_time',
        'ssousername',
        'uuid',
        'gsuite_account',
        'moe_account',
        'last_login',
        'genabled',
    ];
}
