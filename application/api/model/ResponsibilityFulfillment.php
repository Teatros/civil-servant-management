<?php

namespace app\api\model;

use think\Model;
use app\api\model\User;

class ResponsibilityFulfillment extends Model
{
    protected $table='responsibility_fulfillment_record';

    public $hidden = ['create_user_id','create_date_time', 'update_user_id', 'update_date_time','is_delete'];

    public function user()
    {
        return $this->hasOne(\app\api\model\User::class,'id','user_id');
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