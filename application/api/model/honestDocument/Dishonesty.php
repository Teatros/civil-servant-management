<?php

namespace app\api\model\honestDocument;

use think\Model;
use think\model\concern\SoftDelete;

class Dishonesty extends Model
{

    protected $table = 'responsibility_fulfillment_record';
    protected $pk = 'id';
    public $hidden = ['is_delete'];

    public function user()
    {
        return $this->hasOne(\app\api\model\User::class,'id','user_id');
    }

    public function creator()
    {
        return $this->hasOne(\app\api\model\SysUser::class,'id','create_user_id');
    }

    public function updator()
    {
        return $this->hasOne(\app\api\model\SysUser::class,'id','update_user_id');
    }
}