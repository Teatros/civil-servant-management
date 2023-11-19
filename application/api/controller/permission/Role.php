<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/4/20
 * Time: 19:57
 */

namespace app\api\controller\permission;

use app\api\model\Role as RoleModel;
use app\api\model\RoleMenu as RoleMenuModel;
use app\api\model\SysUser as SysUserModel;
use app\api\service\token\LoginToken;
use app\lib\util\UUIDUtil;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Request;
use think\facade\Hook;

class Role
{

    private $loginTokenService;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->loginTokenService = LoginToken::getInstance();
    }

    /**
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     * @throws \Exception
     */
    public function listRoles(Request $request)
    {
        $params = $request->post();
        $pageNum = $params['pageNum'];
        $pageSize = $params['pageSize'];

        $list = RoleModel::where('is_delete','=',0)
        ->paginate($pageSize,false,['page'=>$pageNum]);

        return writeJson(200, $list, '获取成功');
    }

    public function listAllRoles(Request $request)
    {
        $list = RoleModel::where('is_delete','=','0')->select();

        return writeJson(200, $list, '获取成功');
    }

    public function addRole(Request $request)
    {
        $uid = $this->loginTokenService->getCurrentUid();
        $sysUser = SysUserModel::get($uid);
        $params = $request->post();
        RoleModel::create($params);
        Hook::listen('logger', $sysUser['name'] . '创建了角色【'.$params['name'].'】');
        return writeJson(200, '', '新建角色成功');
    }

    public function getRole(Request $request)
    {
        $params = $request->get();

        $role = RoleModel::get($params['id']);
        return writeJson(200, $role, '获取成功');
    }

    public function updateRole(Request $request)
    {
        $params = $request->post();

        $roleModel = new RoleModel();
        $roleModel->save($params, ['id' => $params['id']]);

        $uid = $this->loginTokenService->getCurrentUid();
        $sysUser = SysUserModel::get($uid);

        $menu_ids = $params['menu_ids'];
        RoleMenuModel::where('role_id','=',$params['id'])->delete();
        $role_menus = [];
        foreach ($menu_ids as $menu_id){
            array_push($role_menus,['role_id'=>$params['id'],'menu_id'=>$menu_id,'is_delete'=>0]);
        }
        $role_menu_model = new RoleMenuModel();
        $role_menu_model->saveAll($role_menus);
        Hook::listen('logger', $sysUser['name'] . '更新了角色【'.$params['name'].'】');
        return writeJson(200, '', '更新角色成功');
    }

    public function deleteRole(Request $request)
    {
        $params = $request->get();
        $role = RoleModel::get($params['id']);

        $updateBody = array('is_delete'=>1);
        $roleModel = new RoleModel();
        $roleModel->save($updateBody, ['id' => $params['id']]);
        $uid = $this->loginTokenService->getCurrentUid();
        $sysUser = SysUserModel::get($uid);
        Hook::listen('logger', $sysUser['name'] . '删除了角色【'.$role['name'].'】');
        return writeJson(200, '', '删除角色成功');
    }
}