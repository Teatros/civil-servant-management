<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/2/19
 * Time: 11:22
 */

namespace app\api\service;

use app\api\model\Dept as DeptModel;
use app\api\service\token\LoginToken;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

class Dept
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

    public static function getInstance(): Dept
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
    public function getDeptTree() {
        $collection = DeptModel::where('del_flag','0')
            ->order(['parent_id'=>'asc','order_num'=>'asc'])
            ->select();
        return $this->buildDeptTree($collection->toArray(),0);
    }

    private function buildDeptTree($depts, $parentId) {
        $returnList = [];
        foreach ($depts as $dept) {
            if ($dept['parent_id'] == $parentId) {
                $returnDept = $this->recursionFn($depts,$dept);
                array_push($returnList,$returnDept);
            }
        }
        return $returnList;
    }

    private function recursionFn($list, $t) {
        $childList = $this->getChildList($list,$t);
        $returnChildrenList = [];
        foreach ($childList as $tChild) {
            if ($this->hasChild($list,$tChild)) {
                $child = $this->recursionFn($list,$tChild);
                array_push($returnChildrenList,$child);
            }else{
                $returnChildrenList = $childList;
            }
        }
        $t['children'] = $returnChildrenList;
        return $t;
    }

    /**
     * 得到子节点列表
     */
    private function getChildList($list, $t)
    {
        $tList = [];
        foreach ($list as $item) {
            if ($item['parent_id'] == $t['dept_id']) {
                array_push($tList,$item);
            }
        }
        return $tList;
    }

    /**
     * 判断是否有子节点
     */
    private function hasChild($list, $t)
    {
        $childList = $this->getChildList($list,$t);
        return sizeof($childList) > 0;
    }

}