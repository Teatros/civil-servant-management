<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/4/20
 * Time: 19:57
 */

namespace app\api\controller\user;

use app\api\model\User as UserModel;
use app\api\model\SysUser as SysUserModel;
use app\api\model\User as UserService;
use app\api\service\token\LoginToken;
use app\lib\util\UUIDUtil;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Request;
use think\facade\Hook;

class User
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
    public function listUsers(Request $request)
    {
        $params = $request->post();
        $name = $params['name'];
        $id_card_no = $params['id_card_no'];
        $pageNum = $params['pageNum'];
        $pageSize = $params['pageSize'];

        $dept_id = $params['dept_id'] ?? null;
        $where=[
            ['name','like','%'.$name.'%'],
            ['id_card_no', 'like', '%'.$id_card_no.'%'],
        ];
        if (!is_null($dept_id)) {
            $where[] = ['dept_id', '=', $dept_id];
        }

        $list = UserService::where($where)
            ->with([
                'dept'=>function ($dept) {
                    $dept->field('dept_id,dept_name');
                },
            ])
        ->paginate($pageSize,false,['page'=>$pageNum]);

        return writeJson(200, $list, '获取成功');
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
        UserService::create($params);
        return writeJson(200, '', '新建用户成功');
    }

    public function getUser(Request $request)
    {
        $uid = $this->loginTokenService->getCurrentUid();
        return writeJson(200, SysUserModel::get($uid), '获取用户成功');
    }

    public function updateUser(Request $request)
    {
        $params = $request->put();
        $user = UserModel::get($params['id']);

        $userModel = new UserModel();
        $userModel->save($params, ['id' => $params['id']]);

        $uid = $this->loginTokenService->getCurrentUid();
        $sysUser = SysUserModel::get($uid);
        Hook::listen('logger', $sysUser['name'] . '删除了id为' . $user['name']);
        return writeJson(200, '', '更新用户成功');
    }

    public function deleteUser(Request $request)
    {
        $params = $request->get();
        $user = UserModel::get($params['id']);
        UserService::destroy($params['id']);
        $uid = $this->loginTokenService->getCurrentUid();
        $sysUser = SysUserModel::get($uid);
        Hook::listen('logger', $sysUser['name'] . '删除了id为' . $user['name']);
        return writeJson(200, '', '删除用户成功');
    }

    public function getUserByDeptId(Request $request)
    {
        $params = $request->get();

        $dept_id = $params['dept_id'];
        $list = UserModel::where([
            ['dept_id','=',$dept_id],
            ['is_delete','=',0],
        ])
        ->select()
        ->order('create_date_time','desc');
        return writeJson(200, $list, '获取成功');
    }
}