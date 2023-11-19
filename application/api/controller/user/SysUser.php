<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/4/20
 * Time: 19:57
 */

namespace app\api\controller\user;

use app\api\service\token\LoginToken;
use app\api\model\LoginRecord as LoginRecordModel;
use app\api\service\Permission;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\facade\Hook;
use think\Request;
use app\api\service\SysUser as SysUserService;
use app\api\model\SysUser as SysUserModel;

use app\lib\util\UUIDUtil;

class SysUser
{

    private $loginTokenService;
    private $permissionService;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->loginTokenService = LoginToken::getInstance();
        $this->permissionService = Permission::getInstance();
    }
    public function create(Request $request)
    {
        $params = $request->post();
        return writeJson(200, '123', '添加用户成功');
    }

    public function listUsers(Request $request)
    {
        $params = $request->post();
        $dept_id = $params['dept_id'];
        $name = $params['name'];

        $pageNum = $params['pageNum'];
        $pageSize = $params['pageSize'];

        $where=[
            ['name','like','%'.$name.'%'],
            ['is_delete','=',0],
        ];
        if (isset($dept_id) && $dept_id!='') {
            $where[] = ['dept_id', '=', $dept_id];
        }

        $list = SysUserModel::where($where)
            ->with([
                'dept'=>function ($dept) {
                    $dept->field('dept_id,dept_name');
                },
                'role'=>function ($role) {
                    $role->field('id,name');
                },
            ])
            ->paginate($pageSize,false,['page'=>$pageNum]);

        return writeJson(200, $list, '获取成功');

    }

    public function getUser(Request $request)
    {
        $params = $request->get();
        $user_id = $params['user_id'];
        $sys_user = SysUserModel::get($user_id)
            ->with([
            'dept'=>function ($dept) {
                $dept->field('dept_id,dept_name');
            },
            'role'=>function ($role) {
                $role->field('id,name');
            }
        ]);
        return writeJson(200, $sys_user, '获取成功');
    }

    public function addUser(Request $request)
    {
        $uid = $this->loginTokenService->getCurrentUid();

        $params = $request->post();
        $params['id'] = UUIDUtil::getUUID();
        $params['create_user_id'] = $uid;
        $params['create_date_time'] = date('Y-m-d H:i:s');
        $params['update_user_id'] = $uid;
        $params['update_date_time'] = date('Y-m-d H:i:s');
        SysUserModel::create($params);
        return writeJson(200, null, '创建用户成功');
    }

    public function updateUser(Request $request)
    {
        $params = $request->put();
        $user = SysUserModel::get($params['id']);

        $userModel = new SysUserModel();
        $userModel->save($params, ['id' => $params['id']]);
        return writeJson(200, '', '更新用户成功');
    }

    public function changeStatus(Request $request)
    {
        $params = $request->put();
        $user_id = $params['user_id'];
        $status = $params['status'];

        $putObj = ['status'=>$status];
        $userModel = new SysUserModel();
        $userModel->save($putObj, ['id' => $user_id]);
        return writeJson(200, '', '修改状态成功');
    }

    public function deleteUser(Request $request)
    {
        $params = $request->get();

        $deleteObj = ['is_delete'=>1];
        $userModel = new SysUserModel();
        $userModel->save($deleteObj, ['id' => $params['id']]);
        return writeJson(200, '', '删除用户成功');
    }

    /**
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     * @throws \Exception
     */
    public function login(Request $request)
    {
        $username = $request->post('username');
        $password = $request->post('password');
        $user = SysUserService::getUserByUserName($username);

        $data = null;
        if(isset($user)){
            $dbPassword = $user->password;
            if($password == $dbPassword){
                $token = $this->loginTokenService->getToken($this->getTokenExtend($user));
                $data = $token['accessToken'];
            }else{
                return writeJson(1000, $data, '用户名密码不正确');
            }
        }else{
            return writeJson(1000, $data, '用户名密码不正确');
        }
        LoginRecordModel::create(['sys_user_id'=>$user['id'],'login_date_time'=>date('Y-m-d H:m:s')]);
        return writeJson(200, $data, '登录成功');
    }

    public function getUserInfo(Request $request)
    {
        $uid = $this->loginTokenService->getCurrentUid();
        $user = SysUserModel::get($uid);
        $permissions = [];
        if($user['role_id'] == 1){
            array_push($permissions,'*:*:*');
            $user['permissions'] = $permissions;
        }else{
            $perms = $this->permissionService->selectMenuPermsByRoleId($user['role_id']);
            foreach ($perms as $perm){
                array_push($permissions,$perm['perms']);
            }
            $user['permissions'] = $permissions;
        }
        return writeJson(200, $user, '获取成功');
    }

    public function logout(Request $request)
    {
        return writeJson(200, '', '退出登录');
    }

    private function getTokenExtend($user) {
        return [
            'id' => $user->getAttr('id'),
            'username' => $user->getAttr('name'),
            'password' => $user->getAttr('password'),
            'role_id' => $user->getAttr('role_id'),
        ];
    }
}