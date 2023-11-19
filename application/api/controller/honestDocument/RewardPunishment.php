<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/4/20
 * Time: 19:57
 */

namespace app\api\controller\honestDocument;

use app\api\model\honestDocument\RewardPunishment as RewardPunishmentModel;
use app\api\model\SysUser as SysUserModel;
use app\api\model\User as UserModel;
use app\api\service\token\LoginToken;
use app\lib\util\UUIDUtil;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Request;
use think\facade\Hook;

class RewardPunishment
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
        $user_id = $params['user_id'];
        $pageNum = $params['pageNum'];
        $pageSize = $params['pageSize'];

        $sysUser = SysUserModel::get($this->loginTokenService->getCurrentUid());
        $dept_id = $sysUser['dept_id'];
        $where=[
            ['dept_id','=',$dept_id]
        ];
        $rewardPunishmentModelWhere=[
            ['reward_punishment.is_delete', '=', 0],
        ];
        if($user_id != ''){
            $rewardPunishmentModelWhere[] = ['reward_punishment.user_id','=',$user_id];
        }

        $list = RewardPunishmentModel::hasWhere('user',function($query) use ($where) {
            $query->where($where);
        })
            ->where($rewardPunishmentModelWhere)
            ->with([
                'user'=>function ($user) {
                    $user->field('id,name');
                },
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
        RewardPunishmentModel::create($params);
        return writeJson(200, '', '新建成功');
    }

    public function updateData(Request $request)
    {
        $params = $request->put();
        $user = UserModel::get($params['user_id']);

        $dishonestyModel = new RewardPunishmentModel();
        $dishonestyModel->save($params, ['id' => $params['id']]);

        $uid = $this->loginTokenService->getCurrentUid();
        $sysUser = SysUserModel::get($uid);
        Hook::listen('logger', $sysUser['name'] . '更新了' . $user['name'].'的奖惩记录，记录id:['.$params['id'].']');
        return writeJson(200, '', '更新成功');
    }

    public function deleteData(Request $request)
    {
        $params = $request->get();
        $dishonesty = RewardPunishmentModel::get($params['id']);
        $user = UserModel::get($dishonesty['user_id']);

        $dishonestyModel = new RewardPunishmentModel();
        $deleteBody = ['is_delete'=>1];
        $dishonestyModel->save($deleteBody, ['id' => $params['id']]);

        $uid = $this->loginTokenService->getCurrentUid();
        $sysUser = SysUserModel::get($uid);
        Hook::listen('logger', $sysUser['name'] . '删除了' . $user['name'].'的奖惩记录，记录id:['.$params['id'].']');
        return writeJson(200, '', '删除成功');
    }
}