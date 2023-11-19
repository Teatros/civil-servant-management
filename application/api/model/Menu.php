<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class Menu extends Model
{
    protected $table = 'sys_menu';
    protected $pk = 'menu_id';
}