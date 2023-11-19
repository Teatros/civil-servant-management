<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class User extends Model
{

    protected $table = 'user';
    protected $pk = 'id';
    public $hidden = ['create_user_id','create_date_time', 'update_user_id', 'update_date_time','is_delete'];

    public function dept()
    {
        return $this->hasOne(\app\api\model\Dept::class,'dept_id','dept_id');
    }
}