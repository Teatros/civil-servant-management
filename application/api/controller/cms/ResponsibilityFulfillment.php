<?php

namespace app\api\controller\cms;

use app\api\model\ResponsibilityFulfillment as FulfillmentService;
use app\api\service\token\LoginToken;
use app\lib\exception\NotFoundException;
use app\lib\exception\OperationException;
use app\lib\exception\RepeatException;
use app\lib\exception\token\ForbiddenException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\facade\Hook;
use think\Request;
use think\response\Json;

class ResponsibilityFulfillment
{

    /**
     * @var LoginToken
     */
    private $loginTokenService;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->loginTokenService = LoginToken::getInstance();
    }


    /**
     * @param Request $request
     * @return Json
     * @throws NotFoundException
     * @throws OperationException
     * @throws RepeatException
     * @throws ForbiddenException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function create(Request $request)
    {
        $uid = $this->loginTokenService->getCurrentUid();
        $params = $request->post();
        $params['create_user_id'] = $uid;
        $params['create_date_time'] = date('Y-m-d H:m:s');
        $params['update_user_id'] = $uid;
        $params['update_date_time'] = date('Y-m-d H:m:s');
        FulfillmentService::create($params);

        Hook::listen('logger', "创建了履行职责失信记录");
        return writeJson(200, true, '创建成功');
    }

    /**
     * @param Request $request
     * @return Json
     * @throws NotFoundException
     * @throws OperationException
     * @throws RepeatException
     * @throws ForbiddenException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function update(Request $request)
    {
        $uid = $this->loginTokenService->getCurrentUid();
        $params = $request->post();
        $params['create_user_id'] = $uid;
        $params['create_date_time'] = date('Y-m-d H:m:s');
        $params['update_user_id'] = $uid;
        $params['update_date_time'] = date('Y-m-d H:m:s');

        $fulfillment = new FulfillmentService();
        $fulfillment->save([
            'type'  => $params['type'],
            'detail_context' => $params['detail_context'],
            'additional_instructions' => $params['additional_instructions'],
            'faithless_date' => $params['faithless_date'],
            'update_user_id' => $uid,
            'update_date_time' => date('Y-m-d H:m:s')
        ],['id' => $params['id']]);

        Hook::listen('logger', "更新了履行职责失信记录");
        return writeJson(200, true, '更新成功');
    }

    public function list(Request $request): Json
    {
        $params = $request->post();
        $civil_servant_name = $params['civil_servant_name'];
        $pageNum = $params['pageNum'];
        $pageSize = $params['pageSize'];

        $list = FulfillmentService::hasWhere('user',function($query) use ($civil_servant_name) {
            $query->where('name', 'like', '%'.$civil_servant_name.'%');
        })

            ->where('responsibility_fulfillment_record.is_delete',0)
            ->with([
            'user'=>function ($user) {
                $user->field('id,name');
            },
            'creator'=>function ($creator){
                $creator->field('id,name');
            },
            'updater'=>function ($updater){
                $updater->field('id,name');
            }
        ])
            ->paginate($pageSize,false,['page'=>$pageNum]);

        return writeJson(200, $list, '获取成功');
    }

    public function delete(Request $request): Json
    {
        $uid = $this->loginTokenService->getCurrentUid();
        $params = $request->param();

        $fulfillment = new FulfillmentService();
        $fulfillment->save([
            'is_delete'  => 1,
            'update_user_id' => $uid,
            'update_date_time' => date('Y-m-d H:m:s')
        ],['id' => $params['id']]);

        Hook::listen('logger', "删除了履行职责失信记录");

        return writeJson(200, true, '删除成功');
    }
}
