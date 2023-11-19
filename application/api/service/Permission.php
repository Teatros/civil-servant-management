<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/2/19
 * Time: 11:22
 */

namespace app\api\service;

use app\api\model\Menu as MenuModel;
use app\api\service\Permission as PermissionService;
use app\api\service\token\LoginToken;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

class Permission
{

    private static $instance;

    /**
     * User constructor.
     */
    public function __construct()
    {

    }

    public static function getInstance(): Permission
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    public function selectMenuPermsByRoleId($roleId) {
        $perms = MenuModel::query('
        select distinct m.perms
		from sys_menu m
			 left join sys_role_menu rm on m.menu_id = rm.menu_id
		where m.perms != "" and rm.role_id='.$roleId);
        return $perms;
    }
}