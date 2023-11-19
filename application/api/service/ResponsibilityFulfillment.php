<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/10/3
 * Time: 6:09 下午
 */
namespace app\api\service;
use app\lib\exception\NotFoundException;
use app\lib\exception\OperationException;
use app\lib\exception\RepeatException;
use app\lib\exception\token\ForbiddenException;
use phpDocumentor\Reflection\Types\Boolean;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

class ResponsibilityFulfillment
{
    /**
     * @param array $params
     * @return Boolean
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ForbiddenException
     * @throws ModelNotFoundException
     * @throws NotFoundException
     * @throws OperationException
     * @throws RepeatException
     */
    public static function create(array $params): Boolean
    {

    }
}