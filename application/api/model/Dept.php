<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class Dept extends Model
{

    protected $table = 'sys_dept';
    protected $pk = 'dept_id';
}