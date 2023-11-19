<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/4/20
 * Time: 19:57
 */

namespace app\api\controller\permission;

use app\api\service\token\LoginToken;
use app\api\service\Menu as MenuService;
use app\api\model\Menu as MenuModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\facade\Hook;
use think\Request;
use app\api\service\SysUser as SysUserService;
use app\api\model\SysUser as SysUserModel;

class Menu
{

    private $loginTokenService;
    private $menuService;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->loginTokenService = LoginToken::getInstance();
        $this->menuService = MenuService::getInstance();
    }


    public function getRouters(Request $request)
    {
        $uid = $this->loginTokenService->getCurrentUid();
        $menuList = $this->menuService->selectMenuTreeByUserId($uid);
        return writeJson(200, $menuList, '获取成功');
    }


    public function treeSelect(Request $request)
    {
        $uid = $this->loginTokenService->getCurrentUid();
        $menuList = $this->menuService->selectMenuList($uid);
        return writeJson(200, $menuList, '获取成功');
    }

    public function roleMenuTreeSelect(Request $request)
    {
        $params = $request->get();
        $role_id = $params['role_id'];
        $uid = $this->loginTokenService->getCurrentUid();
        $menuList = $this->menuService->selectMenuList($uid);
        $checkedKeys = $this->menuService->selectMenuListByRoleId($role_id);
        $tempList = array_map(function ($menu) {
            return $menu['menu_id'];
        }, $checkedKeys);
        $returnObject = ['checkedKeys'=>$tempList, 'menus'=>$this->menuService->buildMenuTree($menuList->toArray())];
        return writeJson(200, $returnObject, '获取成功');
    }

    public function getMenus(Request $request)
    {
        $uid = $this->loginTokenService->getCurrentUid();
        $menuList = $this->menuService->selectAllMenu();
        return writeJson(200, $menuList, '获取成功');
    }

    public function getMenu(Request $request)
    {
        $params = $request->get();
        $menu_id = $params['menu_id'];
        $menu = MenuModel::get($menu_id);
        return writeJson(200, $menu, '获取成功');
    }

    public function addMenu(Request $request)
    {
        $params = $request->post();
        $uid = $this->loginTokenService->getCurrentUid();

        $menu = MenuModel::where([
            ['name','like','%'.$params['name'].'%'],
        ])
            ->where(function ($query) {
                $query->where('menu_type', '=', 'M')
                    ->whereOr('menu_type', '=', 'C');
            })
            ->select();
        if(isset($menu)){
            MenuModel::create($params);
        }else{
            return writeJson(201, "名称重复", '创建失败');
        }
        return writeJson(200, "", '创建成功');
    }

    public function updateMenu(Request $request)
    {
        $params = $request->post();
        $uid = $this->loginTokenService->getCurrentUid();
        $dataModel = new MenuModel();
        $dataModel->save($params, ['menu_id' => $params['menu_id']]);
        return writeJson(200, "", '更新成功');
    }

    public function deleteMenu(Request $request)
    {
        $params = $request->get();
        $params['status'] = 1;
        $uid = $this->loginTokenService->getCurrentUid();

        $dataModel = new MenuModel();
        $dataModel->save($params, ['menu_id' => $params['menu_id']]);
        return writeJson(200, null, '删除成功');
    }
}