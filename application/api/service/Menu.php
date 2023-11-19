<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/2/19
 * Time: 11:22
 */

namespace app\api\service;

use app\api\model\Menu as MenuModel;
use app\api\model\SysUser as SysUserModel;
use app\api\service\token\LoginToken;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

class Menu
{

    private static $instance;
    private $loginTokenService;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->loginTokenService = LoginToken::getInstance();
    }

    public static function getInstance(): Menu
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function selectMenuList($userId)
    {
        $sysUser = SysUserModel::get($userId);
        $roleId = $sysUser['role_id'];
        if($roleId == 1){
            $menuList = MenuModel::where('status','=',0)->order(['parent_id'=>'asc','order_num'=>'asc'])->select();
            return $menuList;
        }else{
            $menuList = MenuModel::query('
                select distinct m.menu_id, m.parent_id, m.menu_name, m.path, m.component, ifnull(m.perms,"") as perms, m.menu_type, m.icon, m.order_num
                from sys_menu m
                left join sys_role_menu rm on m.menu_id = rm.menu_id
                left join sys_user ur on rm.role_id = ur.role_id
                where m.status = 0 and ur.id='.$userId);
            return $menuList;
        }

    }

    public function selectAllMenu(){
        $menuList = MenuModel::query('
        select distinct m.menu_id, m.parent_id, m.name,m.alwaysShow, m.path, m.component, ifnull(m.perms,"") as perms, m.menu_type, m.icon, m.order_num
		from sys_menu m
		left join sys_role_menu rm on m.menu_id = rm.menu_id
		where m.status = 0
		order by m.parent_id, m.order_num
	');
        return $menuList;
    }
    /**
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    public function selectMenuTreeByUserId($uid) {
        $sysUser = SysUserModel::get($uid);
        $menuList = MenuModel::query('
        select distinct m.menu_id, m.parent_id, m.name,m.alwaysShow, m.path, m.component, ifnull(m.perms,"") as perms, m.menu_type, m.icon, m.order_num
		from sys_menu m
		left join sys_role_menu rm on m.menu_id = rm.menu_id
		where m.status = 0 and rm.role_id = '.$sysUser['role_id'].' and m.menu_type in ("M", "C")
		order by m.parent_id, m.order_num');

        $returnList = [];
        foreach ($menuList as $menu) {
            if($menu['alwaysShow'] == 1) {
                $menu['alwaysShow'] = true;
            }else{
                $menu['alwaysShow'] = false;
            }
            $menu['hidden'] = false;
            $meta = array('title' => $menu['name'], 'icon' => $menu['icon'], 'noCache' => false, 'link' => null);
            $menu['meta'] = $meta;
            array_push($returnList,$menu);
        }
        return $this->getChildPerms($returnList,0);
    }

    private function getChildPerms($menuList, $parentId) {
        $returnList = [];
        foreach ($menuList as $menu) {
            if ($menu['parent_id'] == $parentId) {
                $returnMenu = $this->recursionFn($menuList,$menu);
                array_push($returnList,$returnMenu);
            }
        }
        return $returnList;
    }

    private function recursionFn($menuList, $menu) {
        $childList = $this->getChildList($menuList,$menu);
        $menu['children'] = $childList;
        foreach ($childList as $tChild) {
            if ($this->hasChild($menuList,$tChild)) {
                $this->recursionFn($menuList,$tChild);
            }
        }
        return $menu;
    }

    private function getChildList($menuList, $menu) {
        $tList = [];
        foreach ($menuList as $item) {
            if ($item['parent_id'] == $menu['menu_id']) {
                array_push($tList,$item);
            }
        }
        return $tList;
    }

    private function hasChild($menuList,$menu) {
        $childList = $this->getChildList($menuList,$menu);
        return sizeof($childList) > 0;
    }

    public function selectMenuListByRoleId($role_id)
    {
        return MenuModel::query('
        select m.menu_id
		from sys_menu m
            left join sys_role_menu rm on m.menu_id = rm.menu_id
        where m.status = 0 and rm.role_id='.$role_id.' order by m.parent_id, m.order_num');
    }

    public function buildMenuTree($menus)
    {
        $returnList = [];
        $tempList = array_map(function ($menu) {
            return $menu['menu_id'];
        }, $menus);
        foreach ($menus as $menu){
            if (!in_array($menu['parent_id'], $tempList)) {
                $returnMenu = $this->recursionFn($menus,$menu);
                array_push($returnList,$returnMenu);
            }
        }

        if(sizeof($returnList) == 0){
            $returnList = $menus;
        }
        return $returnList;
    }
}