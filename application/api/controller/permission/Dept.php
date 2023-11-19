<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/4/20
 * Time: 19:57
 */

namespace app\api\controller\permission;

use app\api\model\Dept as DeptModel;
use app\api\service\Dept as DeptService;
use app\api\service\token\LoginToken;
use app\lib\util\UUIDUtil;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Request;
use think\facade\Hook;

class Dept
{

    private $loginTokenService;
    private $deptService;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->loginTokenService = LoginToken::getInstance();
        $this->deptService = DeptService::getInstance();
    }

    /**
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     * @throws \Exception
     */
    public function listDeptTree(Request $request)
    {
        $params = $request->post();

        $list = DeptModel::select();
        $returnList = $this->deptService->getDeptTree();
        return writeJson(200, $returnList, '获取成功');
    }

    public function getDeptList(Request $request)
    {
        $params = $request->post();

        $list = DeptModel::query('
            select d.dept_id, d.parent_id, d.ancestors, d.dept_name, d.order_num, d.leader, d.status, d.del_flag, d.create_by, d.create_time 
            from sys_dept d
            where d.del_flag = 0
            order by d.parent_id, d.order_num
        ');
        $returnList = [];
        foreach ($list as $dept){
            $dept['children'] = [];
            array_push($returnList,$dept);
        }
        return writeJson(200, $returnList, '获取成功');
    }

    public function listDeptExclude(Request $request)
    {
        $params = $request->get();
        $dept_id = $params['dept_id'];
        $list = DeptModel::query('
            select d.dept_id, d.parent_id, d.ancestors, d.dept_name, d.order_num, d.leader, d.status, d.del_flag, d.create_by, d.create_time 
            from sys_dept d
            where d.del_flag = 0
            order by d.parent_id, d.order_num
        ');
        $depts = array_filter($list, function ($d) use ($dept_id) {
            return $d->getDeptId() == $dept_id || in_array($dept_id, explode(',', $d->getAncestors()));
        });
        return writeJson(200, $depts, '获取成功');
    }

    public function getDept(Request $request){
        $params = $request->get();
        $list  = DeptModel::query(
            '
            select d.dept_id, d.parent_id, d.ancestors, d.dept_name, d.order_num, d.leader, d.status,
			(select dept_name from sys_dept where dept_id = d.parent_id) parent_name
		from sys_dept d
		where d.dept_id ='.$params['dept_id']
        );
        return writeJson(200, $list, '获取成功');
    }
    public function addDept(Request $request)
    {
        $params = $request->post();
        $dbDept = DeptModel::where('dept_name','like','%'.$params['dept_name'].'%')->select();
        if(!isset($dbDept)){
            return writeJson(201, null, '名称已存在');
        }
        $uid = $this->loginTokenService->getCurrentUid();
        $params['create_by'] = $uid;
        $params['create_time'] = date('Y-m-d H:i:s');
        $params['update_by'] = $uid;
        $params['update_time'] = date('Y-m-d H:i:s');
        $params['status'] = 0;
        $params['del_flag'] = 0;
        $parent_id = $params['parent_id'];
        $parent_dept = DeptModel::get($parent_id);
        if(is_null($parent_dept)){
            $params['parent_id'] = 0;
            $params['ancestors'] = '0';
        }else{
            $params['ancestors'] = $parent_dept['ancestors'].','.$params['parent_id'];
        }
        DeptModel::create($params);
        return writeJson(200, $params, '创建成功');
    }

    public function updateDept(Request $request)
    {
        $params = $request->post();
        $uid = $this->loginTokenService->getCurrentUid();
        $params['update_by'] = $uid;
        $params['update_time'] = date('Y-m-d H:i:s');
        $dataModel = new DeptModel();
        $dataModel->save($params, ['dept_id' => $params['dept_id']]);
        return writeJson(200, null, '更新成功');
    }

    public function deleteDept(Request $request)
    {
        $params = $request->get();
        $list = DeptModel::where('parent_id','=',$params['dept_id'])->select();
        if(sizeof($list) > 0){
            return writeJson(500, null, '还有子节点，无法删除此节点');
        }

        $params['del_flag'] = 1;
        $uid = $this->loginTokenService->getCurrentUid();
        $params['update_by'] = $uid;
        $params['update_time'] = date('Y-m-d H:i:s');
        $dataModel = new DeptModel();
        $dataModel->save($params, ['dept_id' => $params['dept_id']]);
        return writeJson(200, null, '删除成功');
    }

}