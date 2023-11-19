<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class Role extends Model
{

    protected $table = 'sys_role';
    protected $pk = 'id';
}