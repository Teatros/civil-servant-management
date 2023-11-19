<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/4/20
 * Time: 19:57
 */

namespace app\api\controller\record;

use app\api\model\LoanRecord as LoanRecordModel;
use app\api\model\SysUser as SysUserModel;
use app\api\service\token\LoginToken;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\facade\Hook;
use think\Request;

class LoanRecord
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
    public function searchData(Request $request)
    {
        $params = $request->post();
        $borrower_name = $params['borrower_name'];
        $pageNum = $params['pageNum'];
        $pageSize = $params['pageSize'];

        $sysUser = SysUserModel::get($this->loginTokenService->getCurrentUid());
        $dept_id = $sysUser['dept_id'];
        $where=[
            ['borrower_name', 'like', '%'.$borrower_name.'%'],
            ['is_delete', '=', 0],
        ];

        $list = LoanRecordModel::where($where)
            ->with([
                'creator'=>function ($user) {
                    $user->field('id,name');
                },
                'updator'=>function ($user) {
                    $user->field('id,name');
                }
            ])
        ->order('create_date_time','desc')
        ->paginate($pageSize,false,['page'=>$pageNum]);

        return writeJson(200, $list, '获取成功');
    }

    public function addData(Request $request)
    {
        $uid = $this->loginTokenService->getCurrentUid();
        $params = $request->post();
        $params['create_user_id'] = $uid;
        $params['create_date_time'] = date('Y-m-d H:i:s');
        $params['update_user_id'] = $uid;
        $params['update_date_time'] = date('Y-m-d H:i:s');
        LoanRecordModel::create($params);
        return writeJson(200, '', '新建成功');
    }

    public function updateData(Request $request)
    {
        $params = $request->put();

        $updateModel = new LoanRecordModel();
        $updateModel->save($params, ['id' => $params['id']]);

        $uid = $this->loginTokenService->getCurrentUid();
        $sysUser = SysUserModel::get($uid);
        Hook::listen('logger', $sysUser['name'] . '更新了借阅记录，记录id:['.$params['id'].']');
        return writeJson(200, '', '更新成功');
    }

    public function deleteData(Request $request)
    {
        $params = $request->get();

        $deleteModel = new LoanRecordModel();
        $deleteBody = ['is_delete'=>1];
        $deleteModel->save($deleteBody, ['id' => $params['id']]);

        $uid = $this->loginTokenService->getCurrentUid();
        $sysUser = SysUserModel::get($uid);
        Hook::listen('logger', $sysUser['name'] . '删除了借阅记录，记录id:['.$params['id'].']');
        return writeJson(200, '', '删除成功');
    }
}