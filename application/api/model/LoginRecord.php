<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class LoginRecord extends Model
{
    protected $table = 'login_record';

    public function sysUser(){
        return $this->hasOne(\app\api\model\SysUser::class,'id','user_id');
    }
}