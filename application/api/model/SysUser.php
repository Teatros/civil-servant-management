<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class SysUser extends Model
{
    protected $table = 'sys_user';

    public function dept()
    {
        return $this->hasOne(\app\api\model\Dept::class,'dept_id','dept_id');
    }

    public function role()
    {
        return $this->hasOne(\app\api\model\Role::class,'id','role_id');
    }

    public function creator()
    {
        return $this->hasOne(\app\api\model\SysUser::class,'id','create_user_id');
    }

    public function updater()
    {
        return $this->hasOne(\app\api\model\SysUser::class,'id','update_user_id');
    }
}