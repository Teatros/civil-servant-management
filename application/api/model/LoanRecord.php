<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class LoanRecord extends Model
{

    protected $table = 'loan_record';
    protected $pk = 'id';
    public $hidden = ['is_delete'];

    public function creator()
    {
        return $this->hasOne(\app\api\model\SysUser::class,'id','create_user_id');
    }

    public function updator()
    {
        return $this->hasOne(\app\api\model\SysUser::class,'id','update_user_id');
    }
}