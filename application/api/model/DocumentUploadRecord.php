<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class DocumentUploadRecord extends Model
{
    protected $table = 'document_upload_record';

    public function sysUser()
    {
        return $this->hasOne(\app\api\model\SysUser::class,'id','sys_user_id');
    }
}